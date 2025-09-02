<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentService
{
    public function __construct(
        protected MailService $mailService,
        protected SmsService $smsService
    ) {}

    /**
     * Send a reservation payment link via SMS and email
     *
     * @param Order $order
     * @throws Exception
     */
    public function sendReservationPaymentLink(Order $order): void
    {
        try {
            $paymentLink = $this->generateReservationPaymentLink($order);

            if (!$paymentLink) {
                throw new Exception(__('messages.error_generating_payment_link'));
            }

            $order->update([
                'payment_link_sent_at' => now(),
                'status' => 'awaiting_payment',
            ]);

            // Send SMS
            $message = __('messages.reservation_fee_sms', [
                'orderId' => $order->id,
                'amount' => $order->getReservationFee(),
                'link' => $paymentLink
            ]);
            $this->smsService->sendCustomMessage($order, $message);

            // Send Email
            $this->mailService->sendPaymentLink($order, $paymentLink);

            Log::info('Reservation payment link sent for order #' . $order->id, [
                'payment_link' => $paymentLink,
                'amount' => $order->getReservationFee(),
            ]);

        } catch (Exception $e) {
            Log::error('Error sending reservation payment link: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
            throw $e;
        }
    }

    /**
     * Generate Stripe payment link for reservation fee
     */
    public function generateReservationPaymentLink(Order $order): ?string
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->payment_currency),
                        'product_data' => [
                            'name' => __('messages.reservation_fee_product') . $order->car->model,
                            'description' => __('messages.order') . ' #' . $order->id
                        ],
                        'unit_amount' => (int)($order->getReservationFee() * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', $order->id),
                'client_reference_id' => 'order_' . $order->id,
                'customer_email' => $order->email,
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->email,
                    'customer_name' => trim($order->first_name . ' ' . $order->last_name),
                    'customer_phone' => $order->phone,
                    'type' => 'reservation_fee'
                ],
            ]);

            $order->update([
                'payment_session_id' => $session->id,
            ]);

            return $session->url;

        } catch (Exception $e) {
            Log::error('Error generating Stripe payment link: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
            return null;
        }
    }

    /**
     * Generate payment link for final settlement
     */
    public function generateFinalPaymentLink(Order $order, float $amount, string $description): ?string
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->payment_currency),
                        'product_data' => [
                            'name' => __('messages.final_settlement_product') . $order->car->model,
                            'description' => $description,
                        ],
                        'unit_amount' => (int)($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.final.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.final.cancel', $order->id),
                'client_reference_id' => 'order_final_' . $order->id,
                'customer_email' => $order->email,
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->email,
                    'customer_name' => trim($order->first_name . ' ' . $order->last_name),
                    'customer_phone' => $order->phone,
                    'type' => 'final_settlement'
                ],
            ]);

            return $session->url;

        } catch (Exception $e) {
            Log::error('Error generating final payment link: ' . $e->getMessage(), [
                'order_id' => $order->id,
            ]);
            return null;
        }
    }
}
