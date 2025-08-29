<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send payment link via SMS
     *
     * @param string $phone
     * @param string $paymentLink
     * @return bool
     */
    public function sendPaymentLink(string $phone, string $paymentLink): bool
    {
        $message = __('messages.payment_link_sms') . ' ' . $paymentLink;
        // TODO: Replace with real SMS sending (e.g., Twilio)
        Log::info("Payment link SMS to {$phone}: {$message}");
        return true;
    }

    /**
     * Send a custom message via SMS
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
