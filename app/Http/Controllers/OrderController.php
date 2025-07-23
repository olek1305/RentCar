<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Car;
use App\Services\OrderService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
     * @param StoreOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $result = $this->orderService->createOrder($request->validated());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        if ($result['requires_verification']) {
            return redirect()->route('orders.verification', $result['order']->id)
                ->with('success', $result['message']);
        }

        return redirect()->route('home')
            ->with('success', __('messages.order_created'));
    }


    /**
     * @param $orderId
     * @return Factory|View|Application
     */
    public function verification($orderId): Factory|View|Application
    {
        $order = Order::findOrFail($orderId);
        return view('orders.verification', compact('order'));
    }

    /**
     * @param $token
     * @return RedirectResponse
     */
    public function verifyEmail($token): RedirectResponse
    {
        $result = $this->orderService->verifyEmailToken($token);

        if (!$result['success']) {
            return redirect()->route('home')->with('error', $result['message']);
        }

        return redirect()->route('orders.verification', $result['order']->id)
            ->with('success', $result['message']);
    }

    /**
     * @param Request $request
     * @param $orderId
     * @return RedirectResponse
     */
    public function verifySms(Request $request, $orderId): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:4']);

        $result = $this->orderService->verifySmsCode($orderId, $request->code);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('orders.verification', $result['order']->id)
            ->with('success', $result['message']);
    }

    /**
     * @param $orderId
     * @param $type
     * @return RedirectResponse
     */
    public function resendVerification($orderId, $type): RedirectResponse
    {
        $order = Order::findOrFail($orderId);

        if ($type === 'email') {
            $this->orderService->getMailService()->sendVerificationEmail($order);
            return back()->with('success', __('Verification email resent!'));
        }

        return back()->with('error', __('Invalid verification type.'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|regex:/^[0-9 ]+$/|min:9']);
        return $this->orderService->getSmsService()->sendVerificationCode($request->phone);
    }
}
