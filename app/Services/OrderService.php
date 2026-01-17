<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CurrencySetting;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected MailService $mailService,
        protected SmsService $smsService,
        protected CacheService $cacheService,
        protected PaymentService $paymentService
    ) {}

    public function getCacheService(): CacheService
    {
        return $this->cacheService;
    }

    public function getPaymentService(): PaymentService
    {
        return $this->paymentService;
    }

    public function getSmsService(): SmsService
    {
        return $this->smsService;
    }

    /**
     * Create a new order.
     *
     * @throws Exception
     */
    public function createOrder(array $data): array
    {
        $validationResult = $this->validateOrderData($data);
        if ($validationResult !== null) {
            return $validationResult;
        }

        $data = $this->processOrderTimes($data);

        $limitCheck = $this->checkOrderLimits($data);
        if ($limitCheck['limited']) {
            return ['success' => false, 'message' => $limitCheck['message']];
        }

        return DB::transaction(function () use ($data) {
            $car = Car::lockForUpdate()->findOrFail($data['car_id']);

            if ($car->hidden) {
                return ['success' => false, 'message' => __('messages.order_unavailable')];
            }

            $availabilityCheck = $this->checkCarAvailability($data['car_id'], $data['rental_date'], $data['return_date']);
            if (! $availabilityCheck['available']) {
                return ['success' => false, 'message' => $availabilityCheck['message']];
            }

            $order = $this->createOrderRecord($data);
            $this->hideCarAndClearCache($car);

            $verificationMethod = $data['verification_method'] ?? 'email';

            return $verificationMethod === 'email'
                ? $this->sendEmailVerification($order, $car)
                : $this->sendSmsVerification($order, $car);
        });
    }

    /**
     * Validate order data before processing.
     *
     * @return array|null Returns error array or null if valid
     */
    protected function validateOrderData(array $data): ?array
    {
        $rentalHour = (int) $data['rental_time_hour'];
        $returnHour = (int) $data['return_time_hour'];

        if ($rentalHour < 6 || $rentalHour > 20) {
            return ['success' => false, 'message' => __('messages.rental_time_must_be_between_6_20')];
        }

        if ($returnHour < 6 || $returnHour > 20) {
            return ['success' => false, 'message' => __('messages.return_time_must_be_between_6_20')];
        }

        if (! isset($data['return_date']) || $data['return_date'] <= $data['rental_date']) {
            return ['success' => false, 'message' => __('messages.return_date_must_be_after_rental_date')];
        }

        if (empty($data['acceptance_terms']) || empty($data['acceptance_privacy'])) {
            return ['success' => false, 'message' => __('messages.must_accept_terms_and_privacy')];
        }

        if ($data['delivery_option'] === 'delivery' && empty($data['delivery_address'])) {
            return ['success' => false, 'message' => __('messages.delivery_address_required')];
        }

        return null;
    }

    /**
     * Process order time fields.
     */
    protected function processOrderTimes(array $data): array
    {
        $data['rental_time'] = $data['rental_time_hour'].':'.$data['rental_time_minute'];
        $data['return_time'] = $data['return_time_hour'].':'.$data['return_time_minute'];

        unset($data['rental_time_hour'], $data['rental_time_minute'], $data['return_time_hour'], $data['return_time_minute']);

        return $data;
    }

    /**
     * Create order record with system-managed fields.
     */
    protected function createOrderRecord(array $data): Order
    {
        $order = Order::create($data);

        $order->status = 'pending';
        $order->payment_amount = Order::getStaticReservationFee();
        $order->payment_currency = CurrencySetting::getDefaultCurrency()->currency_code;
        $order->additional_insurance_cost = $data['additional_insurance'] ? Order::getStaticAdditionalInsuranceCost() : null;
        $order->save();

        return $order;
    }

    /**
     * Hide car and clear cache.
     */
    protected function hideCarAndClearCache(Car $car): void
    {
        $car->update(['hidden' => true]);
        $this->cacheService->clearCarsCache();
    }

    /**
     * Generate verification token pair.
     *
     * @return array{token: string, hashedToken: string}
     */
    public function generateVerificationToken(): array
    {
        $token = bin2hex(random_bytes(32));

        return [
            'token' => $token,
            'hashedToken' => hash('sha256', $token),
        ];
    }

    /**
     * Send email verification for order.
     */
    protected function sendEmailVerification(Order $order, Car $car): array
    {
        $tokens = $this->generateVerificationToken();

        $order->email_verification_token = $tokens['hashedToken'];
        $order->email_verification_sent_at = now();
        $order->save();

        $verificationUrl = route('orders.verify-email-payment', [
            'order' => $order->id,
            'token' => $tokens['token'],
        ]);

        $this->mailService->sendPaymentLink($order, $verificationUrl);

        return [
            'success' => true,
            'message' => __('messages.order_created_email_verification_sent'),
            'order' => $order,
            'verification_method' => 'email',
        ];
    }

    /**
     * Send SMS verification for order.
     */
    protected function sendSmsVerification(Order $order, Car $car): array
    {
        $tokens = $this->generateVerificationToken();

        $order->sms_verification_token = $tokens['hashedToken'];
        $order->sms_verification_sent_at = now();
        $order->save();

        $paymentLink = $this->paymentService->generateReservationPaymentLink($order);

        if (! $paymentLink) {
            $this->rollbackOrderCreation($order, $car);

            return ['success' => false, 'message' => __('messages.error_generating_payment_link')];
        }

        $order->payment_link_sent_at = now();
        $order->save();

        $this->smsService->sendPaymentLink($order->phone, $paymentLink);

        return [
            'success' => true,
            'message' => __('messages.order_created_sms_payment_link_sent'),
            'order' => $order,
            'verification_method' => 'sms',
        ];
    }

    /**
     * Rollback order creation on failure.
     */
    protected function rollbackOrderCreation(Order $order, Car $car): void
    {
        $order->delete();
        $car->update(['hidden' => false]);
        $this->cacheService->clearCarsCache();
    }

    /**
     * Check order limits for the current day.
     */
    protected function checkOrderLimits(array $data): array
    {
        $count = Order::where(function ($query) use ($data) {
            $query->where('email', $data['email'])
                ->orWhere('phone', $data['phone']);
        })
            ->whereDate('created_at', Carbon::today())
            ->count();

        if ($count >= 3) {
            return [
                'limited' => true,
                'message' => __('order_already'),
                'count' => $count,
            ];
        }

        return [
            'limited' => false,
            'message' => null,
            'count' => $count,
        ];
    }

    /**
     * Check if a car is available for the given dates.
     *
     * @param  int  $carId  The car ID to check
     * @param  string  $rentalDate  The rental start date
     * @param  string  $returnDate  The rental end date
     * @param  int|null  $excludeOrderId  Optional order ID to exclude (for updates)
     */
    public function checkCarAvailability(int $carId, string $rentalDate, string $returnDate, ?int $excludeOrderId = null): array
    {
        $rentalStart = Carbon::parse($rentalDate)->startOfDay();
        $returnEnd = Carbon::parse($returnDate)->endOfDay();

        // Find overlapping orders for this car
        // An order overlaps if: (existing_rental_date <= new_return_date) AND (existing_return_date >= new_rental_date)
        $query = Order::where('car_id', $carId)
            ->whereNotIn('status', ['cancelled', 'finished'])
            ->where(function ($q) use ($rentalStart, $returnEnd) {
                $q->where(function ($inner) use ($rentalStart, $returnEnd) {
                    $inner->where('rental_date', '<=', $returnEnd)
                        ->where('return_date', '>=', $rentalStart);
                });
            });

        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        $conflictingOrder = $query->first();

        if ($conflictingOrder) {
            return [
                'available' => false,
                'message' => __('messages.car_not_available_for_dates', [
                    'from' => $conflictingOrder->rental_date->format('d.m.Y'),
                    'to' => $conflictingOrder->return_date->format('d.m.Y'),
                ]),
                'conflicting_order' => $conflictingOrder,
            ];
        }

        return [
            'available' => true,
            'message' => null,
        ];
    }

    /**
     * Get unavailable dates for a car.
     *
     * @param  int  $carId  The car ID
     * @return array Array of date ranges that are unavailable
     */
    public function getUnavailableDates(int $carId): array
    {
        $orders = Order::where('car_id', $carId)
            ->whereNotIn('status', ['cancelled', 'finished'])
            ->where('return_date', '>=', Carbon::today())
            ->select('rental_date', 'return_date')
            ->get();

        $unavailableDates = [];

        foreach ($orders as $order) {
            $unavailableDates[] = [
                'from' => $order->rental_date->format('Y-m-d'),
                'to' => $order->return_date->format('Y-m-d'),
            ];
        }

        return $unavailableDates;
    }
}
