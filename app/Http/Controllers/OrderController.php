<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Car;
use App\Services\OrderService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class OrderController extends Controller
{
    /**
     * @param OrderService $orderService
     */
    public function __construct(protected OrderService $orderService)
    {
        //
    }

    /**
     * Store a newly created order in storage.
     *
     * @param StoreOrderRequest $request Validated request data
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $result = $this->orderService->createOrder($request->validated());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('home')
            ->with('success', $result['message'])
            ->with('payment_info', __('messages.payment_link_sent'));
    }

    /**
     * Verify email token and redirect to payment
     */
    public function verifyEmailForPayment(Order $order, string $token): RedirectResponse
    {
        // Verify token
        $hashedToken = hash('sha256', $token);
        if ($order->email_verification_token !== $hashedToken) {
            return redirect()->route('home')->with('error', __('messages.invalid_verification_token'));
        }

        // Check if the token is expired (8 hours)
        if ($order->email_verification_sent_at && $order->email_verification_sent_at->addHours(8)->isPast()) {
            return redirect()->route('home')->with('error', __('messages.verification_token_expired'));
        }

        // Mark email as verified
        $order->update([
            'email_verified_at' => now(),
            'email_verification_token' => null, // Clear token after verification
            'status' => 'verified'
        ]);

        // Hide car after verification
        $order->car->update(['hidden' => true]);
        $this->orderService->getCacheService()->clearCarsCache();

        // Generate payment link
        $paymentLink = $this->orderService->getPaymentService()->generateReservationPaymentLink($order);

        if (!$paymentLink) {
            return redirect()->route('home')->with('error', __('messages.error_generating_payment_link'));
        }

        $order->update(['payment_link_sent_at' => now()]);

        // Redirect to Stripe payment
        return redirect()->away($paymentLink);
    }
}
