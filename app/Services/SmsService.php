<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * @param string $phone
     * @return JsonResponse
     */
    public function sendVerificationCode(string $phone): JsonResponse
    {
        $code = rand(10000, 99999);

        // Store the code in cache for verification
        Cache::put('sms_verification_'.$phone, $code, now()->addMinutes(15));

        // TODO: Replace with real SMS sending
        Log::info("Verification code for phone {$phone}: {$code}");

        return response()->json([
            'success' => true,
            'message' => __('Verification code sent'),
            'code' => $code // Only for development, remove in production
        ]);
    }

    /**
     * @param string $phone
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $phone, string $code): bool
    {
        $storedCode = Cache::get('sms_verification_'.$phone);
        return $storedCode && $storedCode == $code;
    }

    /**
     * Send payment link via SMS
     *
     * @param Order $order
     * @param string $paymentLink
     * @return bool
     */
    public function sendPaymentLink(Order $order, string $paymentLink): bool
    {
        $message = __('messages.payment_link_sms') . ' ' . $paymentLink;
        // TODO: Replace with real SMS sending (e.g., Twilio)
        Log::info("Payment link SMS to {$order->phone}: {$message}");
        return true;
    }

    /**
     * Send custom message via SMS
     *
     * @param Order $order
     * @param string $messageText
     * @return bool
     */
    public function sendCustomMessage(Order $order, string $messageText): bool
    {
        // TODO: Replace with real SMS sending
        Log::info("Custom SMS to {$order->phone}: {$messageText}");
        return true;
    }
}
