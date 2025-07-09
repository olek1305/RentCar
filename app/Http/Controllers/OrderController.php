<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Car;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'car_id' => 'required|exists:cars,id',
            'rental_date' => 'required|date|after_or_equal:today',
            'rental_time' => 'required',
            'return_time' => 'required',
            'extra_delivery_fee' => 'boolean',
            'airport_delivery' => 'boolean',
            'additional_info' => 'nullable|string',
        ]);

        $alreadyOrdered = Order::where(function ($query) use ($validated) {
            $query->where('email', $validated['email'])
                ->orWhere('phone', $validated['phone']);
        })
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyOrdered) {
            return back()->with('error', __('message.order_already'));
        }

        $car = Car::findOrFail($validated['car_id']);

        if ($car->hidden) {
            return redirect()->route('cars.show', $car->id)
                ->with('error', __('message.order_unavailable'));
        }

        Order::create($validated);

        $car->update(['hidden' => 1]);

        return redirect()->route('home')->with('success', __('messages.order_created'));
    }
}
