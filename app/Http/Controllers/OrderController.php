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

        if ($result['requires_verification']) {
            return redirect()->route('orders.verification', $result['order']->id)
                ->with('success', $result['message'])
                ->with('verification_method', $result['verification_method']);
        }

        return redirect()->route('home')
            ->with('success', __('messages.order_created'));
    }


    /**
     * Display the order verification page.
     *
     * @param int $orderId
     * @return Factory|View|Application
     */
    public function verification($orderId): Factory|View|Application
    {
        $order = Order::findOrFail($orderId);
        return view('orders.verification', compact('order'));
    }

    /**
     * Verify email using the signed URL token
     *
     * @param Request $request
     * @param string $token
     * @return RedirectResponse
     */
    public function verifyEmail(Request $request, string $token): RedirectResponse
    {
        if (!URL::hasValidSignature($request)) {
            $expires = $request->query('expires');

            if ($expires && now()->getTimestamp() > $expires) {
                return redirect()->route('home')
                    ->with('error', __('The verification link has expired. Please request a new one.'));
            }

            return redirect()->route('home')
                ->with('error', __('The verification link is invalid. Please try again.'));
        }

        $result = $this->orderService->verifyEmailToken(null, $token);

        if (!$result['success']) {
            return redirect()->route('home')
                ->with('error', $result['message']);
        }

        return redirect()->route('orders.verification', $result['order']->id)
            ->with('success', __('Email verified successfully!'));
    }

    /**
     * Verify SMS code for order confirmation.
     *
     * @param Request $request
     * @param int $orderId
     * @return RedirectResponse
     */
    public function verifySms(Request $request, int $orderId): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:4']);

        $result = $this->orderService->verifySmsCode($orderId, $request->code);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('orders.verification', $result['order']->id)
            ->with('verifiedSms', true)
            ->with('success', $result['message']);
    }

    /**
     * Resend verification code (email or SMS).
     *
     * @param int $orderId
     * @param string $type 'email' or 'sms'
     * @return RedirectResponse
     */
    public function resendVerification(int $orderId, string $type): RedirectResponse
    {
        $order = Order::findOrFail($orderId);

        if ($type === 'email') {
            $this->orderService->getMailService()->sendVerificationEmail($order);
            return back()->with('success', __('Verification email resent!'));
        }

        return back()->with('error', __('Invalid verification type.'));
    }


    /**
     * Send SMS verification code to phone number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|regex:/^[0-9 ]+$/|min:9']);
        return $this->orderService->getSmsService()->sendVerificationCode($request->phone);
    }
}
