<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrencySetting;
use App\Models\Order;
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
        protected PaymentService $paymentService
    ) {}

    /**
     * @return Factory|Application|View
     */
    public function index(): Factory|Application|View
    {
        $orders = Order::with('car')->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * @param $id
     * @return Factory|Application|View
     */
    public function show($id): Factory|Application|View
    {
        $order = Order::with('car')->findOrFail($id);
        $statuses = Order::statuses();
        $currency = CurrencySetting::getDefaultCurrency();

        return view('admin.orders.show', compact('order','currency', 'statuses'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,awaiting_payment,paid,completed,finished,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', __('messages.order_status_updated'));
    }

    /**
     * Send a payment link to a customer
     *
     * @param $id
     * @return RedirectResponse
     */
    public function sendPaymentLink($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        if (!$order->canSendPaymentLink()) {
            return back()->with('error', __('messages.cannot_send_payment_link'));
        }

        try {
            if (empty(config('services.stripe.secret'))) {
                throw new Exception('Stripe secret key is not configured');
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $totalAmount = $order->calculateTotalAmount();
            $currency = CurrencySetting::getDefaultCurrency();

            if (!preg_match('/^[a-z]{3}$/i', $currency->currency_code)) {
                throw new Exception('Invalid currency format: ' . $currency->currency_code);
            }

//            #TODO add payment success and cancel routes
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency->currency_code),
                        'product_data' => [
                            'name' => 'Rental for ' . $order->car->model,
                            'description' => 'Order #' . $order->id,
                        ],
                        'unit_amount' => (int)($totalAmount * 100), // zawsze integer
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('payment.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', $order->id),
                'client_reference_id' => 'order_' . $order->id,
                'metadata' => [
                    'order_id' => $order->id,
                    'customer_email' => $order->email,
                ],
            ]);

            $order->update([
                'payment_session_id'   => $session->id,
                'payment_link_sent_at' => now(),
                'payment_amount'       => $totalAmount,
                'payment_currency'     => $currency->currency_code,
                'status'               => 'awaiting_payment',
            ]);

            $paymentLink = $session->url;
            $message = __('messages.payment_link_sent');


            Log::info('Payment link sent for order #' . $order->id, [
                'payment_link' => $paymentLink,
                'amount' => $totalAmount,
                'currency' => $currency->currency_code,
            ]);

            return back()->with('success', $message);

        } catch (ApiErrorException $e) {
            Log::error('Stripe API error sending payment link: ' . $e->getMessage(), [
                'order_id' => $id,
                'error' => $e->getError(),
            ]);
            return back()->with('error', __('messages.stripe_api_error'));
        } catch (\Exception $e) {
            Log::error('Error sending payment link: ' . $e->getMessage(), [
                'order_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', __('messages.error_sending_payment_link') . ': ' . $e->getMessage());
        }
    }

    /**
     * Mark order as finished (a car returned)
     *
     * @param $id
     * @return RedirectResponse
     */
    public function markAsFinished($id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if (!$order->canBeFinished()) {
            return back()->with('error', __('messages.cannot_finish_order'));
        }

        $order->update([
            'status' => 'finished',
            'returned_at' => now()
        ]);

        return back()->with('success', __('messages.order_finished_successfully'));
    }

    /**
     * Cancel expired orders (orders without payment after 24h)
     *
     * @return int Number of cancelled orders
     */
    public function cancelExpiredOrders(): int
    {
        $expiredOrders = Order::where('status', 'awaiting_payment')
            ->where('payment_link_sent_at', '<', now()->subHours(24))
            ->get();

        $cancelledCount = 0;
        foreach ($expiredOrders as $order) {
            $order->update(['status' => 'cancelled']);
            // Release the car
            if ($order->car) {
                $order->car->update(['hidden' => false]);
            }
            $cancelledCount++;
        }

        return $cancelledCount;
    }

    /**
     * Force cancel an order (admin action)
     *
     * @param $id
     * @return RedirectResponse
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

    public function sendFinalPaymentLink($id): RedirectResponse
    {
        $order = Order::with('car')->findOrFail($id);

        if ($order->status !== 'finished') {
            return back()->with('error', __('messages.cannot_send_final_payment_link'));
        }

        try {
            // Recalculate if needed, e.g., extra fees
            $finalAmount = $order->calculateTotalAmount(); // Or add logic for extras

            // Create a Stripe session (similar to before)
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::create([
                // ... same as sendPaymentLink, but change name to 'Final Payment for ...'
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower(CurrencySetting::getDefaultCurrency()->currency_code),
                            'product_data' => ['name' => 'Final Payment for ' . $order->car->model],
                            'unit_amount' => $finalAmount * 100,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'success_url' => route('payment.success', $order->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', $order->id),
            ]);

            $paymentLink = $session->url;

            // Send it with a custom message
            $messageText = __('messages.final_payment_message') . ' ' . $paymentLink;

//            #TODO sendCustomMessage?
            if ($order->email_verified_at) {
                // Assume MailService can send custom text
                $this->orderService->getMailService()->sendCustomMessage($order, $messageText);
            } elseif ($order->sms_verified_at) {
                $this->orderService->getSmsService()->sendCustomMessage($order, $messageText);
            }

            return back()->with('success', __('messages.final_payment_link_sent'));

        } catch (\Exception $e) {
            return back()->with('error', __('messages.error_sending_payment_link'));
        }
    }
}
