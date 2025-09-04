<?php

namespace App\Services;

use App\Models\Car;
use App\Models\CurrencySetting;
use App\Models\Order;
use Carbon\Carbon;
use Exception;

class OrderService
{
    /**
     * @param MailService $mailService
     * @param SmsService $smsService
     * @param CacheService $cacheService
     * @param PaymentService $paymentService
     */
    public function __construct(protected MailService $mailService,
                                protected SmsService $smsService,
                                protected CacheService $cacheService,
                                protected PaymentService $paymentService
    )
    {
        //
    }

    /**
     * @return CacheService
     */
    public function getCacheService(): CacheService
    {
        return $this->cacheService;
    }

    /**
     * @return PaymentService
     */
    public function getPaymentService(): PaymentService
    {
        return $this->paymentService;
    }

    /**
     * @return SmsService
     */
    public function getSmsService(): SmsService
    {
        return $this->smsService;
    }


    /**
     * Create a new order.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createOrder(array $data): array
    {
        // Validate hours (6:00-20:00)
        $rentalHour = (int) $data['rental_time_hour'];
        $returnHour = (int) $data['return_time_hour'];

        if ($rentalHour < 6 || $rentalHour > 20) {
            return [
                'success' => false,
                'message' => __('messages.rental_time_must_be_between_6_20')
            ];
        }

        if ($returnHour < 6 || $returnHour > 20) {
            return [
                'success' => false,
                'message' => __('messages.return_time_must_be_between_6_20')
            ];
        }

        // Validate return date is after the rental date
        if (!isset($data['return_date']) || $data['return_date'] <= $data['rental_date']) {
            return [
                'success' => false,
                'message' => __('messages.return_date_must_be_after_rental_date')
            ];
        }

        // Validate terms and privacy acceptance
        if (empty($data['acceptance_terms']) || empty($data['acceptance_privacy'])) {
            return [
                'success' => false,
                'message' => __('messages.must_accept_terms_and_privacy')
            ];
        }

        // Validate delivery address if delivery service is selected
        if ($data['delivery_option'] === 'delivery' && empty($data['delivery_address'])) {
            return [
                'success' => false,
                'message' => __('messages.delivery_address_required')
            ];
        }

        // Process order data
        $data['rental_time'] = $data['rental_time_hour'] . ':' . $data['rental_time_minute'];
        $data['return_time'] = $data['return_time_hour'] . ':' . $data['return_time_minute'];

        unset($data['rental_time_hour'], $data['rental_time_minute'], $data['return_time_hour'], $data['return_time_minute']);

        $limitCheck = $this->checkOrderLimits($data);
        if ($limitCheck['limited']) {
            return [
                'success' => false,
                'message' => $limitCheck['message']
            ];
        }

        $car = Car::findOrFail($data['car_id']);

        if ($car->hidden) {
            return ['success' => false, 'message' => __('message.order_unavailable')];
        }

        // Get verification method
        $verificationMethod = $data['verification_method'] ?? 'email';

        $orderData = [
            ...$data,
            'status' => 'pending',
            'payment_amount' => Order::getStaticReservationFee(),
            'payment_currency' => CurrencySetting::getDefaultCurrency()->currency_code,
            'additional_insurance_cost' => $data['additional_insurance'] ? Order::getStaticAdditionalInsuranceCost() : null,
        ];

        // Create order
        $order = Order::create($orderData);

        // Always hide a car after creating order regardless of verification method
        $car->update(['hidden' => true]);
        $this->cacheService->clearCarsCache();

        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        if ($verificationMethod === 'email') {
            // Generate email verification token

            $order->update([
                'email_verification_token' => $hashedToken,
                'email_verification_sent_at' => now(),
            ]);

            // Create verification URL that will redirect to payment
            $verificationUrl = route('orders.verify-email-payment', [
                'order' => $order->id,
                'token' => $token
            ]);

            // Send the verification URL by email (not the direct Stripe link)
            $this->mailService->sendPaymentLink($order, $verificationUrl);

            $message = __('messages.order_created_email_verification_sent');
        } else {
            // Generate SMS verification token

            $order->update([
                'sms_verification_token' => $hashedToken,
                'sms_verification_sent_at' => now(),
            ]);

            // Generate a payment link and send via SMS
            $paymentLink = $this->paymentService->generateReservationPaymentLink($order);

            if (!$paymentLink) {
                $order->delete();
                // Restore car visibility if order creation failed
                $car->update(['hidden' => false]);
                $this->cacheService->clearCarsCache();
                return [
                    'success' => false,
                    'message' => __('messages.error_generating_payment_link')
                ];
            }

            $order->update(['payment_link_sent_at' => now()]);

            // Send payment link via SMS
            $this->smsService->sendPaymentLink($order->phone, $paymentLink);

            $message = __('messages.order_created_sms_payment_link_sent');
        }

        return [
            'success' => true,
            'message' => $message,
            'order' => $order,
            'verification_method' => $verificationMethod
        ];
    }

    /**
     * Check order limits for the current day.
     *
     * @param array $data
     * @return array
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
                'count' => $count
            ];
        }

        return [
            'limited' => false,
            'message' => null,
            'count' => $count
        ];
    }
}
