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
}
