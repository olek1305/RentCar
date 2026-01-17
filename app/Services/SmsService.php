<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send payment link via SMS.
     *
     * Note: Currently logs to file. Implement Twilio/SMS provider for production.
     */
    public function sendPaymentLink(string $phone, string $paymentLink): bool
    {
        $message = __('messages.payment_link_sms').' '.$paymentLink;
        Log::info("Payment link SMS to {$phone}: {$message}");

        return true;
    }

    /**
     * Send a custom message via SMS.
     *
     * Note: Currently logs to file. Implement Twilio/SMS provider for production.
     */
    public function sendCustomMessage(Order $order, string $messageText): bool
    {
        Log::info("Custom SMS to {$order->phone}: {$messageText}");

        return true;
    }
}
