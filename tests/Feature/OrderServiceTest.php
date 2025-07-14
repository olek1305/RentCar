<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = new OrderService();
    }

    #[Test]
    public function it_creates_an_order_and_hides_car()
    {
        $car = Car::factory()->create(['hidden' => false]);

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay()->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'airport_delivery' => false,
            'additional_info' => 'Test order',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('orders', ['email' => 'john@example.com']);
        $this->assertTrue($car->fresh()->hidden);
    }

    #[Test]
    public function it_fails_to_order_a_hidden_car()
    {
        $hiddenCar = Car::factory()->create(['hidden' => true]);

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'car_id' => $hiddenCar->id,
            'rental_date' => now()->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'airport_delivery' => false,
            'additional_info' => 'Test order'
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertEquals(__('message.order_unavailable'), $result['message']);
    }
}
