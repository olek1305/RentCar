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

        if ($this->hasDuplicateOrder($data)) {
            return ['success' => false, 'message' => __('message.order_already')];
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
            'phone_verified_at' => $verificationMethod === 'sms' ? now() : null,
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
     * @param array $data
     * @return bool
     */
    protected function hasDuplicateOrder(array $data): bool
    {
        return Order::where(function ($query) use ($data) {
            $query->where('email', $data['email'])
                ->orWhere('phone', $data['phone']);
        })
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * @param $token
     * @return array
     */
    public function verifyEmailToken($token): array
    {
        $order = Order::where('email_verification_token', $token)->first();

        if (!$order) {
            return ['success' => false, 'message' => __('Invalid verification token')];
        }

        if ($order->email_verified_at) {
            return ['success' => false, 'message' => __('Email already verified')];
        }

        $order->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'status' => 'pending'
        ]);

        return ['success' => true, 'message' => __('Email verified successfully'), 'order' => $order];
    }

    /**
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

        if ($order->phone_verified_at) {
            return ['success' => false, 'message' => __('Phone already verified')];
        }

        $order->update([
            'phone_verified_at' => now(),
            'sms_verification_code' => null,
            'status' => 'pending'
        ]);

        return ['success' => true, 'message' => __('Phone verified successfully'), 'order' => $order];
    }
}
