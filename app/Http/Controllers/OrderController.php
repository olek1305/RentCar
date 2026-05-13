<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\CacheService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected CacheService $cacheService,
    ) {}

    /**
     * Store a newly created order in storage.
     *
     * @throws Exception
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $result = $this->orderService->createOrder($request->validated());

        if (! $result['success']) {
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
        $hashedToken = hash('sha256', $token);
        if ($order->email_verification_token !== $hashedToken) {
            return redirect()->route('home')->with('error', __('messages.invalid_verification_token'));
        }

        if ($order->email_verification_sent_at && $order->email_verification_sent_at->addHours(8)->isPast()) {
            return redirect()->route('home')->with('error', __('messages.verification_token_expired'));
        }

        $order->email_verified_at = now();
        $order->email_verification_token = null;
        $order->status = 'verified';
        $order->save();

        $order->car->update(['hidden' => true]);
        $this->cacheService->clearCarsCache();

        $paymentLink = $this->paymentService->generateReservationPaymentLink($order);

        if (! $paymentLink) {
            return redirect()->route('home')->with('error', __('messages.error_generating_payment_link'));
        }

        $order->payment_link_sent_at = now();
        $order->save();

        return redirect()->away($paymentLink);
    }
}
