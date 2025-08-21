<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    /**
     * @param Request $request
     * @param Order $order
     * @return Application|RedirectResponse|Redirector|object
     * @throws ApiErrorException
     */
    public function success(Request $request, Order $order)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($request->session_id);

        if ($session->payment_status === 'paid' && $session->id === $order->payment_session_id) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            return redirect('/')->with('success', __('messages.payment_successful'));
        }

        return redirect('/')->with('error', __('messages.payment_failed'));
    }

    public function cancel(Order $order)
    {
        $order->update([
            'status' => 'cancelled'
        ]);

        return redirect('/')->with('error', __('messages.payment_cancelled'));
    }
}
