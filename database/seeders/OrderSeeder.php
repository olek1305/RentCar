<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Order;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    private \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run(): void
    {
        $cars = Car::all();
        $statuses = array_keys(Order::statuses());
        $deliveryOptions = ['pickup', 'airport', 'delivery'];

        $orders = [];

        // Generate 2-3 orders for each status
        foreach ($statuses as $status) {
            for ($i = 0; $i < rand(2, 3); $i++) {
                $car = $cars->random();
                $rentalDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
                $rentalTimeHour = rand(8, 16);
                $returnTimeHour = $rentalTimeHour + rand(1, 8);

                $returnDate = $this->faker->dateTimeBetween($rentalDate, $rentalDate->format('Y-m-d').' +7 days');

                $orders[] = [
                    'first_name' => $this->faker->firstName(),
                    'last_name' => $this->faker->lastName(),
                    'email' => $this->faker->unique()->email(),
                    'phone' => $this->faker->numerify('#########'),
                    'car_id' => $car->id,
                    'rental_date' => $rentalDate->format('Y-m-d'),
                    'return_date' => $returnDate->format('Y-m-d'),
                    'rental_time' => sprintf('%02d:%02d', $rentalTimeHour, rand(0, 59)),
                    'return_time' => sprintf('%02d:%02d', $returnTimeHour, rand(0, 59)),
                    'delivery_option' => $this->faker->randomElement($deliveryOptions),
                    'additional_info' => rand(0, 1) ? $this->faker->sentence() : null,
                    'status' => $status,
                    'acceptance_terms' => true,
                    'acceptance_privacy' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert all orders into the database. Add additional orders with different dates for better testing.
        for ($i = 0; $i < 10; $i++) {
            $car = $cars->random();
            $rentalDate = $this->faker->dateTimeBetween('-2 weeks', '+3 weeks');
            $rentalTimeHour = rand(8, 16);
            $returnTimeHour = $rentalTimeHour + rand(1, 8);

            $returnDate = $this->faker->dateTimeBetween($rentalDate, $rentalDate->format('Y-m-d').' +7 days');

            $orders[] = [
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'email' => $this->faker->unique()->email(),
                'phone' => $this->faker->numerify('###-###-###'),
                'car_id' => $car->id,
                'rental_date' => $rentalDate->format('Y-m-d'),
                'return_date' => $returnDate->format('Y-m-d'),
                'rental_time' => sprintf('%02d:%02d', $rentalTimeHour, rand(0, 59)),
                'return_time' => sprintf('%02d:%02d', $returnTimeHour, rand(0, 59)),
                'delivery_option' => $this->faker->randomElement($deliveryOptions),
                'additional_info' => rand(0, 1) ? $this->faker->sentence() : null,
                'status' => $this->faker->randomElement($statuses),
                'acceptance_terms' => true,
                'acceptance_privacy' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Enter all orders into the database
        DB::table('orders')->insert($orders);

        // Update timestamps for more realistic data
        $this->updateTimestamps();
    }

    private function updateTimestamps(): void
    {
        $orders = Order::all();

        foreach ($orders as $order) {
            $createdAt = $this->faker->dateTimeBetween('-2 months', 'now');

            $updateData = ['created_at' => $createdAt];

            // For orders with payment, set paid_at
            if (in_array($order->status, ['paid', 'completed', 'returned', 'finished'])) {
                $updateData['paid_at'] = $this->faker->dateTimeBetween($createdAt, 'now');
            }

            // For completed orders, set returned_at
            if (in_array($order->status, ['returned', 'finished'])) {
                $updateData['returned_at'] = $this->faker->dateTimeBetween(
                    $updateData['paid_at'] ?? $createdAt,
                    'now'
                );
            }

            // For canceled orders
            if ($order->status === 'cancelled') {
                $updateData['updated_at'] = $this->faker->dateTimeBetween($createdAt, 'now');
            }

            DB::table('orders')
                ->where('id', $order->id)
                ->update($updateData);
        }
    }
}
