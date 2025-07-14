<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Car;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    protected OrderService $orderService;

    /**
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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

        return redirect()->route('home')
            ->with('success', __('messages.order_created'));
    }
}
