<?php

namespace App\Services;

use App\Mail\PaymentConfirmationMail;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Send payment link via email
     *
     * @param Order $order
     * @param string $paymentLink
     * @return bool
     */
    public function sendPaymentLink(Order $order, string $paymentLink): bool
    {
        Mail::to($order->email)->send(new PaymentConfirmationMail($order, $paymentLink));
        return true;
    }

    /**
     * Send payment confirmation via email
     *
     * @param Order $order
     * @param string $paymentLink
     * @return bool
     */
    public function sendPaymentConfirmation(Order $order, string $paymentLink): bool
    {
        Mail::to($order->email)->send(new PaymentConfirmationMail($order, $paymentLink));
        return true;
    }
}
