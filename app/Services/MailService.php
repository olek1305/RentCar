<?php

namespace App\Services;

use App\Mail\PaymentConfirmationMail;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Send payment link via email
     */
    public function sendPaymentLink(Order $order, string $paymentLink): bool
    {
        try {
            if (! $order->email || ! filter_var($order->email, FILTER_VALIDATE_EMAIL)) {
                Log::error('Invalid order or email address', ['order_id' => $order->id ?? 'unknown']);

                return false;
            }

            if (empty($paymentLink)) {
                Log::error('Empty payment link provided', ['order_id' => $order->id]);

                return false;
            }

            Mail::to($order->email)->send(new PaymentConfirmationMail($order, $paymentLink));

            Log::info('Payment link email sent successfully', [
                'order_id' => $order->id,
                'email' => $order->email,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to send payment link email', [
                'order_id' => $order->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send payment confirmation via email
     */
    public function sendPaymentConfirmation(Order $order, string $paymentLink): bool
    {
        try {
            // Validate input data
            if (! $order->email || ! filter_var($order->email, FILTER_VALIDATE_EMAIL)) {
                Log::error('Invalid order or email address', ['order_id' => $order->id ?? 'unknown']);

                return false;
            }

            // Send payment confirmation email
            Mail::to($order->email)->send(new PaymentConfirmationMail($order, $paymentLink));

            Log::info('Payment confirmation email sent successfully', [
                'order_id' => $order->id,
                'email' => $order->email,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'order_id' => $order->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
