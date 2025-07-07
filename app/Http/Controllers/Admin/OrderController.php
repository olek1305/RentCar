<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @return Factory|Application|View
     */
    public function index(): Factory|Application|View
    {
        $orders = Order::with('car')->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * @param Order $order
     * @return Factory|Application|View
     */
    public function show(Order $order): Factory|Application|View
    {
        return view('admin.orders.show', compact('order'));
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return RedirectResponse
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Status zamówienia zaktualizowany');
    }
}
