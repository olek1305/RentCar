<?php

namespace App\Services;

use App\Models\CurrencySetting;
use App\Models\Order;
use App\Models\Car;
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
     * @param Order $order
     * @return void
     * @throws Exception
     */
    protected function sendReservationPaymentLink(Order $order): void
    {
        $this->paymentService->sendReservationPaymentLink($order);
    }

    /**
     * @return MailService
     */
    public function getMailService(): MailService
    {
        return $this->mailService;
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

        // Create order directly as pending - no verification needed
        $order = Order::create([
            ...$data,
            'status' => 'pending',
            'payment_amount' => Order::getStaticReservationFee(), // fee
            'payment_currency' => CurrencySetting::getDefaultCurrency()->currency_code,
            'additional_insurance_cost' => $data['additional_insurance'] ? Order::getStaticAdditionalInsuranceCost() : null,
        ]);

        $car->update(['hidden' => true]);
        $this->cacheService->clearCarsCache();

        // Automatically send a payment link for the reservation fee
        $this->sendReservationPaymentLink($order);

        return [
            'success' => true,
            'message' => __('messages.order_created_payment_link_sent'),
            'order' => $order,
            'requires_verification' => false
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

    /**
     * Verify an email token and send payment link
     *
     * @param int|null $orderId
     * @param string $token
     * @return array
     */
    public function verifyEmailToken(?int $orderId, string $token): array
    {
        $hashedToken = hash('sha256', $token);
        $order = Order::where('id', $orderId)
            ->where('email_verification_token', $hashedToken)
            ->first();

        if (!$order) {
            return [
                'success' => false,
                'message' => __('Invalid verification token'),
                'order' => null
            ];
        }

        if ($order->email_verification_sent_at) {
            $expirationTime = $order->email_verification_sent_at->addHours(24);
            if (now()->gt($expirationTime)) {
                return [
                    'success' => false,
                    'message' => __('The verification link has expired. Please request a new one.'),
                    'order' => $order
                ];
            }
        }

        if ($order->email_verified_at) {
            return [
                'success' => false,
                'message' => __('Email already verified'),
                'order' => $order
            ];
        }

        // Verification - send a payment link
        $paymentLink = $this->paymentService->generateReservationPaymentLink($order);

        if (!$paymentLink) {
            return [
                'success' => false,
                'message' => __('Error generating payment link'),
                'order' => $order
            ];
        }

        $order->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'status' => 'awaiting_payment',
            'payment_link_sent_at' => now(),
        ]);

        // Send an email with a payment link
        $this->mailService->sendPaymentConfirmation($order, $paymentLink);

        return [
            'success' => true,
            'message' => __('email_confirm_payment'),
            'order' => $order
        ];
    }
}
