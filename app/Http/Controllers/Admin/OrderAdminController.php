<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterOrdersRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\CurrencySetting;
use App\Models\Order;
use App\Services\MailService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SmsService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class OrderAdminController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected MailService $mailService,
        protected SmsService $smsService,
    ) {}

    /**
     * List of orders with filtering by status and searching by email and phone.
     */
    public function index(FilterOrdersRequest $request): Factory|Application|View
    {
        $statuses = Order::statuses();

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

    public function show(Order $order): Factory|Application|View
    {
        $order->load('car');
        $statuses = Order::statuses();
        $currency = CurrencySetting::getDefaultCurrency();

        return view('admin.orders.show', compact('order', 'currency', 'statuses'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order->status = $request->validated('status');
        $order->save();

        return back()->with('success', __('messages.order_status_updated'));
    }

    /**
     * Send a payment link to a customer
     */
    public function sendPaymentLink(Order $order): RedirectResponse
    {
        $order->loadMissing('car');

        if (! $order->canSendPaymentLink()) {
            return back()->with('error', __('messages.cannot_send_payment_link'));
        }

        try {
            $this->paymentService->sendAdminPaymentLink($order);

            return back()->with('success', __('messages.payment_link_sent'));
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error sending payment link: '.$e->getMessage(), [
                'order_id' => $order->id,
                'error' => $e->getError(),
            ]);

            return back()->with('error', __('messages.stripe_api_error'));
        } catch (Exception $e) {
            Log::error('Error sending payment link: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_sending_payment_link').': '.$e->getMessage());
        }
    }

    /**
     * Mark order as finished (a car returned)
     */
    public function markAsFinished(Order $order): RedirectResponse
    {
        if (! $order->canBeFinished()) {
            return back()->with('error', __('messages.cannot_finish_order'));
        }

        $order->status = 'finished';
        $order->returned_at = now();
        $order->save();

        return back()->with('success', __('messages.order_finished_successfully'));
    }

    /**
     * Force cancel an order (admin action)
     */
    public function cancelOrder(Order $order): RedirectResponse
    {
        if (in_array($order->status, ['completed', 'finished'])) {
            return back()->with('error', __('messages.cannot_cancel_completed_order'));
        }

        $order->status = 'cancelled';
        $order->save();

        return back()->with('success', __('messages.order_cancelled_successfully'));
    }

    /**
     * Renew email verification token and resend verification email
     */
    public function renewEmailToken(Order $order): RedirectResponse
    {
        $order->loadMissing('car');

        if (! $order->canRenewVerificationToken()) {
            return back()->with('error', __('messages.cannot_renew_token_for_this_status'));
        }

        if ($order->email_verified_at) {
            return back()->with('error', __('messages.email_already_verified'));
        }

        try {
            $tokens = $this->orderService->generateVerificationToken();

            $order->email_verification_token = $tokens['hashedToken'];
            $order->email_verification_sent_at = now();
            $order->save();

            $verificationUrl = route('orders.verify-email-payment', [
                'order' => $order->id,
                'token' => $tokens['token'],
            ]);

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
    public function renewSmsToken(Order $order): RedirectResponse
    {
        $order->loadMissing('car');

        if (! $order->canRenewVerificationToken()) {
            return back()->with('error', __('messages.cannot_renew_token_for_this_status'));
        }

        if ($order->sms_verified_at) {
            return back()->with('error', __('messages.sms_already_verified'));
        }

        try {
            $tokens = $this->orderService->generateVerificationToken();

            $order->sms_verification_token = $tokens['hashedToken'];
            $order->sms_verification_sent_at = now();
            $order->save();

            $paymentLink = $this->paymentService->generateReservationPaymentLink($order);

            if (! $paymentLink) {
                return back()->with('error', __('messages.error_generating_payment_link'));
            }

            $order->payment_link_sent_at = now();
            $order->save();

            $this->smsService->sendPaymentLink($order->phone, $paymentLink);

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
    public function sendFinalPaymentLink(Order $order): RedirectResponse
    {
        $order->loadMissing('car');

        if ($order->status !== 'finished') {
            return back()->with('error', __('messages.order_must_be_finished'));
        }

        if (! $order->email_verified_at && ! $order->sms_verified_at) {
            return back()->with('error', __('messages.customer_not_verified'));
        }

        try {
            $this->paymentService->sendFinalPaymentLink($order);

            return back()->with('success', __('messages.final_payment_link_sent'));

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error sending final payment link: '.$e->getMessage(), [
                'order_id' => $order->id,
                'error' => $e->getError(),
            ]);

            return back()->with('error', __('messages.stripe_api_error'));
        } catch (Exception $e) {
            Log::error('Error sending final payment link: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_sending_final_payment_link').': '.$e->getMessage());
        }
    }

    /**
     * Resend confirmation email based on order status
     */
    public function resendConfirmationEmail(Order $order): RedirectResponse
    {
        $order->loadMissing('car');

        try {
            $emailSent = false;
            $successMessage = null;

            if ($order->status === 'paid' && $order->paid_at) {
                $emailSent = $this->mailService->sendPaymentSuccess($order, 'reservation');
                $successMessage = __('messages.reservation_confirmation_resent');
            } elseif ($order->status === 'completed' && $order->final_paid_at) {
                $emailSent = $this->mailService->sendPaymentSuccess($order, 'final');
                $successMessage = __('messages.final_confirmation_resent');
            } elseif (in_array($order->status, ['awaiting_payment', 'verified']) && $order->email_verified_at) {
                $paymentLink = $this->paymentService->generateReservationPaymentLink($order);
                if ($paymentLink) {
                    $emailSent = $this->mailService->sendPaymentLink($order, $paymentLink);
                    $order->payment_link_sent_at = now();
                    $order->save();
                    $successMessage = __('messages.payment_link_resent');
                }
            } elseif ($order->status === 'awaiting_final_payment' && $order->final_payment_session_id) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = Session::retrieve($order->final_payment_session_id);

                if ($session && $session->url && $session->status === 'open') {
                    $emailSent = $this->mailService->sendFinalPaymentLink($order, $session->url);
                    $order->final_payment_link_sent_at = now();
                    $order->save();
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
