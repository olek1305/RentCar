<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Car;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * @param StoreOrderRequest $request
     * @return RedirectResponse
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Combine time fields
        $validated['rental_time'] = $validated['rental_time_hour'] . ':' . $validated['rental_time_minute'];
        $validated['return_time'] = $validated['return_time_hour'] . ':' . $validated['return_time_minute'];

        // Remove the individual time components
        unset($validated['rental_time_hour'], $validated['rental_time_minute'],
            $validated['return_time_hour'], $validated['return_time_minute']);

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
