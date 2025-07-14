<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Car;
use Carbon\Carbon;

class OrderService
{
    public function createOrder(array $data): array
    {
        $data['rental_time'] = $data['rental_time_hour'] . ':' . $data['rental_time_minute'];
        $data['return_time'] = $data['return_time_hour'] . ':' . $data['return_time_minute'];
        unset($data['rental_time_hour'], $data['rental_time_minute'], $data['return_time_hour'], $data['return_time_minute']);

        if ($this->hasDuplicateOrder($data)) {
            return ['success' => false, 'message' => __('message.order_already')];
        }

        $car = Car::findOrFail($data['car_id']);

        if ($car->hidden) {
            return ['success' => false, 'message' => __('message.order_unavailable')];
        }

        Order::create($data);
        $car->update(['hidden' => true]);

        return ['success' => true, 'message' => __('messages.order_created')];
    }

    protected function hasDuplicateOrder(array $data): bool
    {
        return Order::where(function ($query) use ($data) {
            $query->where('email', $data['email'])
                ->orWhere('phone', $data['phone']);
        })
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }
}
