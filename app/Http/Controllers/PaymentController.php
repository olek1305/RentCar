<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CacheService;
use App\Services\MailService;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function __construct(
        protected MailService $mailService,
        protected CacheService $cacheService
    ) {
        //
    }

    /**
     * Handle successful payment callback from Stripe
     *
     * @return Application|RedirectResponse|Redirector|object
     *
     * @throws ApiErrorException
     */
    public function success(Request $request, Order $order)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);

        // Check if this is a final payment
        $isFinalPayment = $request->query('type') === 'final';

        if ($session->payment_status === 'paid') {
            // Verify session ID matches
            $expectedSessionId = $isFinalPayment
                ? $order->final_payment_session_id
                : $order->payment_session_id;

            if ($session->id !== $expectedSessionId) {
                Log::warning('Session ID mismatch', [
                    'order_id' => $order->id,
                    'expected' => $expectedSessionId,
                    'received' => $session->id,
                ]);

                return redirect('/')->with('error', __('messages.payment_verification_failed'));
            }

            if ($isFinalPayment) {
                // Update guarded fields directly
                $order->status = 'completed';
                $order->final_paid_at = now();
                $order->save();

                // Send final payment success email
                $this->mailService->sendPaymentSuccess($order, 'final');

                // Make car available again after rental is fully completed
                $order->car?->update(['hidden' => false]);
                $this->cacheService->clearCarsCache();

                Log::info('Final payment successful', ['order_id' => $order->id]);

                return redirect('/')->with('success', __('messages.final_payment_successful'));
            } else {
                // Update guarded fields directly
                $order->status = 'paid';
                $order->paid_at = now();
                $order->save();

                // Send reservation payment success email
                $this->mailService->sendPaymentSuccess($order, 'reservation');

                Log::info('Reservation payment successful', ['order_id' => $order->id]);

                return redirect('/')->with('success', __('messages.payment_successful'));
            }
        }

        return redirect('/')->with('error', __('messages.payment_failed'));
    }

    /**
     * Handle cancelled payment callback from Stripe
     */
    public function cancel(Order $order): Application|Redirector|RedirectResponse
    {
        // Only allow cancellation of pending/awaiting payment orders
        if (!in_array($order->status, ['pending', 'awaiting_payment', 'awaiting_final_payment'])) {
            Log::warning('Attempted to cancel non-pending order', [
                'order_id' => $order->id,
                'status' => $order->status,
            ]);

            return redirect('/')->with('error', __('messages.order_cannot_be_cancelled'));
        }

        // Update guarded field directly
        $order->status = 'cancelled';
        $order->save();

        // Make car available again
        $order->car?->update(['hidden' => false]);
        $this->cacheService->clearCarsCache();

        Log::info('Payment cancelled', ['order_id' => $order->id]);

        return redirect('/')->with('error', __('messages.payment_cancelled'));
    }
}
