<?php

namespace App\Services;

use App\Mail\FinalPaymentMail;
use App\Mail\PaymentConfirmationMail;
use App\Mail\PaymentSuccessMail;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Validate order has a valid email address.
     */
    private function isValidOrderEmail(Order $order): bool
    {
        if (! $order->email || ! filter_var($order->email, FILTER_VALIDATE_EMAIL)) {
            Log::error('Invalid order or email address', ['order_id' => $order->id ?? 'unknown']);

            return false;
        }

        return true;
    }

    /**
     * Send payment link via email
     */
    public function sendPaymentLink(Order $order, string $paymentLink): bool
    {
        try {
            if (! $this->isValidOrderEmail($order)) {
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
            if (! $this->isValidOrderEmail($order)) {
                return false;
            }

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

    /**
     * Send final payment link via email
     */
    public function sendFinalPaymentLink(Order $order, string $paymentLink): bool
    {
        try {
            if (! $this->isValidOrderEmail($order)) {
                return false;
            }

            if (empty($paymentLink)) {
                Log::error('Empty final payment link provided', ['order_id' => $order->id]);

                return false;
            }

            Mail::to($order->email)->send(new FinalPaymentMail($order, $paymentLink));

            Log::info('Final payment link email sent successfully', [
                'order_id' => $order->id,
                'email' => $order->email,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to send final payment link email', [
                'order_id' => $order->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send payment success confirmation via email
     */
    public function sendPaymentSuccess(Order $order, string $paymentType = 'reservation'): bool
    {
        try {
            if (! $this->isValidOrderEmail($order)) {
                return false;
            }

            Mail::to($order->email)->send(new PaymentSuccessMail($order, $paymentType));

            Log::info('Payment success email sent successfully', [
                'order_id' => $order->id,
                'email' => $order->email,
                'payment_type' => $paymentType,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to send payment success email', [
                'order_id' => $order->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
