<?php

namespace App\Services;

use App\Models\CurrencySetting;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
     * @throws Exception
     */
    public function sendReservationPaymentLink(Order $order): void
    {
        try {
            $paymentLink = $this->generateReservationPaymentLink($order);

            if (! $paymentLink) {
                throw new Exception(__('messages.error_generating_payment_link'));
            }

            // Update guarded fields directly
            $order->payment_link_sent_at = now();
            $order->status = 'awaiting_payment';
            $order->save();

            // Send SMS
            $message = __('messages.reservation_fee_sms', [
                'orderId' => $order->id,
                'amount' => $order->getReservationFee(),
                'link' => $paymentLink,
            ]);
            $this->smsService->sendCustomMessage($order, $message);

            // Send Email
            $this->mailService->sendPaymentLink($order, $paymentLink);

            Log::info('Reservation payment link sent for order #'.$order->id, [
                'amount' => $order->getReservationFee(),
            ]);

        } catch (Exception $e) {
            Log::error('Error sending reservation payment link: '.$e->getMessage(), [
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
                            'name' => __('messages.reservation_fee_product').$order->car->model,
                            'description' => __('messages.order').' #'.$order->id,
                        ],
                        'unit_amount' => (int) ($order->getReservationFee() * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success', $order->id).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => URL::signedRoute('payment.cancel', ['order' => $order->id]),
                'client_reference_id' => 'order_'.$order->id,
                'customer_email' => $order->email,
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->email,
                    'customer_name' => trim($order->first_name.' '.$order->last_name),
                    'customer_phone' => $order->phone,
                    'type' => 'reservation_fee',
                ],
            ]);

            // Update guarded field directly
            $order->payment_session_id = $session->id;
            $order->save();

            return $session->url;

        } catch (Exception $e) {
            Log::error('Error generating Stripe payment link: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);

            return null;
        }
    }

    /**
     * Send a one-off admin payment link for the full rental amount.
     *
     * @throws Exception
     */
    public function sendAdminPaymentLink(Order $order): void
    {
        $totalAmount = $order->calculateTotalAmount();
        $currency = CurrencySetting::getDefaultCurrency();

        $this->assertStripeConfigured();
        $this->assertCurrencyCode($currency->currency_code);

        DB::transaction(function () use ($order, $totalAmount, $currency) {
            $session = $this->createStripeSession(
                order: $order,
                amount: $totalAmount,
                currency: $currency->currency_code,
                productName: 'Rental for '.$order->car->model,
                description: 'Order #'.$order->id,
                clientRefPrefix: 'order_',
                paymentType: null,
            );

            $order->payment_session_id = $session->id;
            $order->payment_link_sent_at = now();
            $order->payment_amount = $totalAmount;
            $order->payment_currency = $currency->currency_code;
            $order->status = 'awaiting_payment';
            $order->save();

            Log::info('Payment link sent for order #'.$order->id, [
                'amount' => $totalAmount,
                'currency' => $currency->currency_code,
            ]);
        });
    }

    /**
     * Send the final-settlement payment link to a returned car's customer.
     *
     * @throws Exception
     */
    public function sendFinalPaymentLink(Order $order): void
    {
        $finalAmount = $order->calculateFinalPaymentAmount();

        if ($finalAmount <= 0) {
            throw new Exception(__('messages.no_remaining_amount'));
        }

        $currency = CurrencySetting::getDefaultCurrency();

        $this->assertStripeConfigured();
        $this->assertCurrencyCode($currency->currency_code);

        $sessionUrl = DB::transaction(function () use ($order, $finalAmount, $currency) {
            $session = $this->createStripeSession(
                order: $order,
                amount: $finalAmount,
                currency: $currency->currency_code,
                productName: __('messages.final_payment_for').' '.$order->car->model,
                description: __('messages.order').' #'.$order->id.' - '.__('messages.remaining_balance'),
                clientRefPrefix: 'order_final_',
                paymentType: 'final',
            );

            $order->final_payment_session_id = $session->id;
            $order->final_payment_link_sent_at = now();
            $order->final_payment_amount = $finalAmount;
            $order->status = 'awaiting_final_payment';
            $order->save();

            return $session->url;
        });

        if ($order->email_verified_at) {
            $this->mailService->sendFinalPaymentLink($order, $sessionUrl);
        }

        Log::info('Final payment link sent for order #'.$order->id, [
            'amount' => $finalAmount,
            'currency' => $currency->currency_code,
        ]);
    }

    private function createStripeSession(
        Order $order,
        float $amount,
        string $currency,
        string $productName,
        string $description,
        string $clientRefPrefix,
        ?string $paymentType,
    ): Session {
        Stripe::setApiKey(config('services.stripe.secret'));

        $successUrl = route('payment.success', $order->id).'?session_id={CHECKOUT_SESSION_ID}';
        if ($paymentType) {
            $successUrl .= '&type='.$paymentType;
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => $productName,
                        'description' => $description,
                    ],
                    'unit_amount' => (int) ($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => URL::signedRoute('payment.cancel', ['order' => $order->id]),
            'client_reference_id' => $clientRefPrefix.$order->id,
            'customer_email' => $order->email,
            'metadata' => [
                'order_id' => $order->id,
                'customer_email' => $order->email,
                'customer_name' => trim($order->first_name.' '.$order->last_name),
                'customer_phone' => $order->phone,
                'type' => $paymentType ?? 'standard',
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    private function assertStripeConfigured(): void
    {
        if (empty(config('services.stripe.secret'))) {
            throw new Exception('Stripe secret key is not configured');
        }
    }

    /**
     * @throws Exception
     */
    private function assertCurrencyCode(string $code): void
    {
        if (! preg_match('/^[a-z]{3}$/i', $code)) {
            throw new Exception('Invalid currency format: '.$code);
        }
    }
}
