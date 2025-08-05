<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Car;
use Carbon\Carbon;

class OrderService
{
    /**
     * @param MailService $mailService
     * @param SmsService $smsService
     */
    public function __construct(protected MailService $mailService, protected SmsService $smsService)
    {
        //
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
     * Create a new order with verification process.
     *
     * @param array $data
     * @return array
     */
    public function createOrder(array $data): array
    {
        $verificationMethod = $data['verification_method'];

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

        $order = Order::create([
            ...$data,
            'status' => 'pending_verification',
            'verification_method' => $verificationMethod,
            'email_verified_at' => null,
            'sms_verified_at' => $verificationMethod === 'sms' ? now() : null,
        ]);
        $car->update(['hidden' => true]);

        // Handle verification based on method
        if ($verificationMethod === 'email') {
            $this->mailService->sendVerificationEmail($order);
            return [
                'success' => true,
                'message' => __('Verification email sent. Please check your inbox.'),
                'order' => $order,
                'requires_verification' => true,
                'verification_method' => 'email'
            ];
        } else {
            return [
                'success' => true,
                'message' => __('Order created successfully.'),
                'order' => $order,
                'requires_verification' => false,
                'verification_method' => 'sms'
            ];
        }
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
     * Verify an email token and activate the order.
     *
     * @param int|null $orderId
     * @param string $token
     * @return array
     */
    public function verifyEmailToken(?int $orderId, string $token): array
    {
        $query = Order::query()
            ->where('email_verification_token', $token);

        if ($orderId) {
            $query->where('id', $orderId);
        }

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

        $order->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'status' => 'pending'
        ]);

        return [
            'success' => true,
            'message' => __('Email verified successfully'),
            'order' => $order
        ];
    }

    /**
     * Verify SMS code and activate the order.
     *
     * @param $orderId
     * @param $code
     * @return array
     */
    public function verifySmsCode($orderId, $code): array
    {
        $order = Order::findOrFail($orderId);

        if ($order->sms_verification_code != $code) {
            return ['success' => false, 'message' => __('Invalid verification code')];
        }

        if ($order->sms_verification_sent_at && $order->sms_verification_sent_at->addHours(24)->isPast()) {
            return ['success' => false, 'message' => __('The verification code has expired. Please request a new one.')];
        }

        if ($order->sms_verified_at) {
            return ['success' => false, 'message' => __('SMS already verified')];
        }

        $order->update([
            'sms_verified_at' => now(),
            'sms_verification_code' => null,
            'status' => 'pending'
        ]);

        return ['success' => true, 'message' => __('SMS verified successfully')];
    }
}
