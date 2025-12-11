<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrencySetting;
use App\Models\Order;
use App\Services\MailService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class OrderAdminController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected MailService $mailService
    ) {}

    /**
     * List of orders with filtering by status and searching by email and phone.
     */
    public function index(Request $request): Factory|Application|View
    {
        $statuses = Order::statuses();

        $request->validate([
            'status' => 'nullable|in:'.implode(',', array_keys($statuses)),
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $query = Order::with('car')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('email')) {
            $email = $request->string('email');
            $query->where('email', 'like', '%'.$email.'%');
        }

        if ($request->filled('phone')) {
            $phone = $request->string('phone');
            $query->where('phone', 'like', '%'.$phone.'%');
        }

        $orders = $query->paginate(10)->appends($request->query());

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => [
                'status' => $request->input('status'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ],
        ]);
    }

    public function show($id): Factory|Application|View
    {
        $order = Order::with('car')->findOrFail($id);
        $statuses = Order::statuses();
        $currency = CurrencySetting::getDefaultCurrency();

        return view('admin.orders.show', compact('order', 'currency', 'statuses'));
    }

    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,awaiting_payment,paid,completed,finished,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', __('messages.order_status_updated'));
    }

    /**
     * Send a payment link to a customer
     */
    public function sendPaymentLink($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        if (! $order->canSendPaymentLink()) {
            return back()->with('error', __('messages.cannot_send_payment_link'));
        }

        try {
            if (empty(config('services.stripe.secret'))) {
                throw new Exception('Stripe secret key is not configured');
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $totalAmount = $order->calculateTotalAmount();
            $currency = CurrencySetting::getDefaultCurrency();

            if (! preg_match('/^[a-z]{3}$/i', $currency->currency_code)) {
                throw new Exception('Invalid currency format: '.$currency->currency_code);
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency->currency_code),
                        'product_data' => [
                            'name' => 'Rental for '.$order->car->model,
                            'description' => 'Order #'.$order->id,
                        ],
                        'unit_amount' => (int) ($totalAmount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success', $order->id).'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', $order->id),
                'client_reference_id' => 'order_'.$order->id,
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->email,
                ],
            ]);

            $order->update([
                'payment_session_id' => $session->id,
                'payment_link_sent_at' => now(),
                'payment_amount' => $totalAmount,
                'payment_currency' => $currency->currency_code,
                'status' => 'awaiting_payment',
            ]);

            $paymentLink = $session->url;
            $message = __('messages.payment_link_sent');

            Log::info('Payment link sent for order #'.$order->id, [
                'payment_link' => $paymentLink,
                'amount' => $totalAmount,
                'currency' => $currency->currency_code,
            ]);

            return back()->with('success', $message);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error sending payment link: '.$e->getMessage(), [
                'order_id' => $id,
                'error' => $e->getError(),
            ]);

            return back()->with('error', __('messages.stripe_api_error'));
        } catch (Exception $e) {
            Log::error('Error sending payment link: '.$e->getMessage(), [
                'order_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_sending_payment_link').': '.$e->getMessage());
        }
    }

    /**
     * Mark order as finished (a car returned)
     */
    public function markAsFinished($id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if (! $order->canBeFinished()) {
            return back()->with('error', __('messages.cannot_finish_order'));
        }

        $order->update([
            'status' => 'finished',
            'returned_at' => now(),
        ]);

        return back()->with('success', __('messages.order_finished_successfully'));
    }

    /**
     * Force cancel an order (admin action)
     */
    public function cancelOrder($id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if (in_array($order->status, ['completed', 'finished'])) {
            return back()->with('error', __('messages.cannot_cancel_completed_order'));
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', __('messages.order_cancelled_successfully'));
    }

    /**
     * Renew email verification token and resend verification email
     */
    public function renewEmailToken($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        // Check if order can have token renewed
        if (in_array($order->status, ['completed', 'finished', 'cancelled', 'paid'])) {
            return back()->with('error', __('messages.cannot_renew_token_for_this_status'));
        }

        // Check if the email is already verified
        if ($order->email_verified_at) {
            return back()->with('error', __('messages.email_already_verified'));
        }

        try {
            // Generate a new verification token
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);

            $order->update([
                'email_verification_token' => $hashedToken,
                'email_verification_sent_at' => now(),
            ]);

            // Create verification URL that will redirect to payment
            $verificationUrl = route('orders.verify-email-payment', [
                'order' => $order->id,
                'token' => $token,
            ]);

            // Send the verification URL by email
            $this->mailService->sendPaymentLink($order, $verificationUrl);

            Log::info('Email verification token renewed for order #'.$order->id, [
                'admin_action' => true,
                'order_id' => $order->id,
            ]);

            return back()->with('success', __('messages.email_verification_token_renewed'));

        } catch (Exception $e) {
            Log::error('Error renewing email verification token: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_renewing_email_token').': '.$e->getMessage());
        }
    }

    /**
     * Renew SMS verification token and resend SMS with a payment link
     */
    public function renewSmsToken($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        // Check if order can have token renewed
        if (in_array($order->status, ['completed', 'finished', 'cancelled', 'paid'])) {
            return back()->with('error', __('messages.cannot_renew_token_for_this_status'));
        }

        // Check if SMS is already verified
        if ($order->sms_verified_at) {
            return back()->with('error', __('messages.sms_already_verified'));
        }

        try {
            // Generate a new verification token
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);

            $order->update([
                'sms_verification_token' => $hashedToken,
                'sms_verification_sent_at' => now(),
            ]);

            // Generate a payment link directly for SMS
            $paymentLink = $this->paymentService->generateReservationPaymentLink($order);

            if (! $paymentLink) {
                return back()->with('error', __('messages.error_generating_payment_link'));
            }

            $order->update(['payment_link_sent_at' => now()]);

            // Send payment link via SMS
            $this->orderService->getSmsService()->sendPaymentLink($order->phone, $paymentLink);

            Log::info('SMS verification token renewed for order #'.$order->id, [
                'admin_action' => true,
                'order_id' => $order->id,
            ]);

            return back()->with('success', __('messages.sms_verification_token_renewed'));

        } catch (Exception $e) {
            Log::error('Error renewing SMS verification token: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_renewing_sms_token').': '.$e->getMessage());
        }
    }
}
