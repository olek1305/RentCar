<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send payment link via SMS
     */
    public function sendPaymentLink(string $phone, string $paymentLink): bool
    {
        $message = __('messages.payment_link_sms').' '.$paymentLink;
        // TODO: Replace with real SMS sending (e.g., Twilio)
        Log::info("Payment link SMS to {$phone}: {$message}");

        return true;
    }

    /**
     * Send a custom message via SMS
     */
    public function sendCustomMessage(Order $order, string $messageText): bool
    {
        // TODO: Replace with real SMS sending
        Log::info("Custom SMS to {$order->phone}: {$messageText}");

        return true;
    }
}
