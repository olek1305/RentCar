<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(protected MailService $mailService) {}

    public function handleStripe(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            Log::error('Stripe webhook secret not configured');

            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (Exception $e) {
            Log::error('Stripe webhook error', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Webhook error'], 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        return match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => response()->json(['status' => 'ignored']),
        };
    }

    protected function handleCheckoutCompleted(object $session): JsonResponse
    {
        $orderId = $session->metadata->order_id ?? null;
        $paymentType = $session->metadata->type ?? 'standard';

        if (! $orderId) {
            Log::warning('Checkout completed without order_id in metadata');

            return response()->json(['error' => 'Missing order_id'], 400);
        }

        $order = Order::find($orderId);

        if (! $order) {
            Log::warning('Order not found for checkout session', ['order_id' => $orderId]);

            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($session->payment_status !== 'paid') {
            Log::info('Checkout completed but not paid', [
                'order_id' => $orderId,
                'status' => $session->payment_status,
            ]);

            return response()->json(['status' => 'not_paid']);
        }

        $isFinalPayment = $paymentType === 'final' || $paymentType === 'final_settlement';

        if ($isFinalPayment) {
            $this->processFinalPayment($order);
        } else {
            $this->processReservationPayment($order);
        }

        return response()->json(['status' => 'success']);
    }

    protected function processReservationPayment(Order $order): void
    {
        if (in_array($order->status, ['paid', 'completed', 'finished'])) {
            Log::info('Order already paid, skipping webhook update', ['order_id' => $order->id]);

            return;
        }

        DB::transaction(function () use ($order) {
            $order->status = 'paid';
            $order->paid_at = now();
            $order->save();

            if ($order->car) {
                $order->car->hidden = true;
                $order->car->save();
            }
        });

        $this->mailService->sendPaymentSuccess($order, 'reservation');

        Log::info('Reservation payment processed via webhook', ['order_id' => $order->id]);
    }

    protected function processFinalPayment(Order $order): void
    {
        if ($order->status === 'completed') {
            Log::info('Order already completed, skipping webhook update', ['order_id' => $order->id]);

            return;
        }

        DB::transaction(function () use ($order) {
            $order->status = 'completed';
            $order->final_paid_at = now();
            $order->save();

            if ($order->car) {
                $order->car->hidden = false;
                $order->car->save();
            }
        });

        $this->mailService->sendPaymentSuccess($order, 'final');

        Log::info('Final payment processed via webhook', ['order_id' => $order->id]);
    }

    protected function handlePaymentSucceeded(object $paymentIntent): JsonResponse
    {
        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
        ]);

        return response()->json(['status' => 'logged']);
    }

    protected function handlePaymentFailed(object $paymentIntent): JsonResponse
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;

        Log::warning('Payment failed', [
            'payment_intent_id' => $paymentIntent->id,
            'order_id' => $orderId,
            'error' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
        ]);

        return response()->json(['status' => 'logged']);
    }
}
