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
            'status' => 'required|in:pending,verified,confirmed,awaiting_payment,paid,completed,returned,finished,awaiting_final_payment,cancelled',
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

    /**
     * Send final payment link for remaining rental cost after car return
     */
    public function sendFinalPaymentLink($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        if ($order->status !== 'finished') {
            return back()->with('error', __('messages.order_must_be_finished'));
        }

        if (! $order->email_verified_at && ! $order->sms_verified_at) {
            return back()->with('error', __('messages.customer_not_verified'));
        }

        try {
            if (empty(config('services.stripe.secret'))) {
                throw new Exception('Stripe secret key is not configured');
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $currency = CurrencySetting::getDefaultCurrency();
            $finalAmount = $order->calculateFinalPaymentAmount();

            if ($finalAmount <= 0) {
                return back()->with('error', __('messages.no_remaining_amount'));
            }

            if (! preg_match('/^[a-z]{3}$/i', $currency->currency_code)) {
                throw new Exception('Invalid currency format: '.$currency->currency_code);
            }

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency->currency_code),
                        'product_data' => [
                            'name' => __('messages.final_payment_for').' '.$order->car->model,
                            'description' => __('messages.order').' #'.$order->id.' - '.__('messages.remaining_balance'),
                        ],
                        'unit_amount' => (int) ($finalAmount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success', $order->id).'?session_id={CHECKOUT_SESSION_ID}&type=final',
                'cancel_url' => route('payment.cancel', $order->id),
                'client_reference_id' => 'order_final_'.$order->id,
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->email,
                    'payment_type' => 'final',
                ],
            ]);

            $order->update([
                'final_payment_session_id' => $session->id,
                'final_payment_link_sent_at' => now(),
                'final_payment_amount' => $finalAmount,
                'status' => 'awaiting_final_payment',
            ]);

            $paymentLink = $session->url;

            // Send via email if verified
            if ($order->email_verified_at) {
                $this->mailService->sendFinalPaymentLink($order, $paymentLink);
            }

            Log::info('Final payment link sent for order #'.$order->id, [
                'amount' => $finalAmount,
                'currency' => $currency->currency_code,
            ]);

            return back()->with('success', __('messages.final_payment_link_sent'));

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error sending final payment link: '.$e->getMessage(), [
                'order_id' => $id,
                'error' => $e->getError(),
            ]);

            return back()->with('error', __('messages.stripe_api_error'));
        } catch (Exception $e) {
            Log::error('Error sending final payment link: '.$e->getMessage(), [
                'order_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_sending_final_payment_link').': '.$e->getMessage());
        }
    }

    /**
     * Resend confirmation email based on order status
     */
    public function resendConfirmationEmail($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        try {
            $emailSent = false;

            // Determine which email to send based on order status
            if ($order->status === 'paid' && $order->paid_at) {
                // Resend reservation payment confirmation
                $emailSent = $this->mailService->sendPaymentSuccess($order, 'reservation');
                $successMessage = __('messages.reservation_confirmation_resent');
            } elseif ($order->status === 'completed' && $order->final_paid_at) {
                // Resend final payment confirmation
                $emailSent = $this->mailService->sendPaymentSuccess($order, 'final');
                $successMessage = __('messages.final_confirmation_resent');
            } elseif (in_array($order->status, ['awaiting_payment', 'verified']) && $order->email_verified_at) {
                // Resend payment link
                $paymentLink = $this->paymentService->generateReservationPaymentLink($order);
                if ($paymentLink) {
                    $emailSent = $this->mailService->sendPaymentLink($order, $paymentLink);
                    $order->update(['payment_link_sent_at' => now()]);
                    $successMessage = __('messages.payment_link_resent');
                }
            } elseif ($order->status === 'awaiting_final_payment' && $order->final_payment_session_id) {
                // Retrieve Stripe session to get the actual payment URL
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = Session::retrieve($order->final_payment_session_id);

                if ($session && $session->url && $session->status === 'open') {
                    $emailSent = $this->mailService->sendFinalPaymentLink($order, $session->url);
                    $order->update(['final_payment_link_sent_at' => now()]);
                    $successMessage = __('messages.final_payment_link_resent');
                } else {
                    return back()->with('error', __('messages.payment_session_expired'));
                }
            } else {
                return back()->with('error', __('messages.no_email_to_resend'));
            }


            if ($emailSent) {
                Log::info('Confirmation email resent for order #'.$order->id, [
                    'status' => $order->status,
                    'admin_action' => true,
                ]);

                return back()->with('success', $successMessage ?? __('messages.email_resent_successfully'));
            }

            return back()->with('error', __('messages.failed_to_resend_email'));

        } catch (Exception $e) {
            Log::error('Error resending confirmation email: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_resending_email').': '.$e->getMessage());
        }
    }
}
