<?php

namespace Tests\Unit;

use App\Models\Car;
use App\Models\Order;
use App\Services\CarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarServiceTest extends TestCase
{
    use RefreshDatabase;

    private CarService $carService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carService = new CarService;
    }

    #[Test]
    public function unhiding_car_cancels_unpaid_orders(): void
    {
        $car = Car::factory()->create(['hidden' => true]);

        $pendingOrder = Order::factory()->pending()->create(['car_id' => $car->id]);
        $verifiedOrder = Order::factory()->verified()->create(['car_id' => $car->id]);
        $awaitingPaymentOrder = Order::factory()->awaitingPayment()->create(['car_id' => $car->id]);
        $paidOrder = Order::factory()->paid()->create(['car_id' => $car->id]);

        $this->carService->toggleCarVisibility($car);

        $this->assertFalse($car->fresh()->hidden);

        $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('orders', ['id' => $verifiedOrder->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('orders', ['id' => $awaitingPaymentOrder->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('orders', ['id' => $paidOrder->id, 'status' => 'paid']);
    }

    #[Test]
    public function hiding_car_does_not_cancel_orders(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $pendingOrder = Order::factory()->pending()->create(['car_id' => $car->id]);
        $verifiedOrder = Order::factory()->verified()->create(['car_id' => $car->id]);

        $this->carService->toggleCarVisibility($car);

        $this->assertTrue($car->fresh()->hidden);

        $this->assertDatabaseHas('orders', ['id' => $pendingOrder->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $verifiedOrder->id, 'status' => 'verified']);
    }

    #[Test]
    public function unhiding_car_does_not_cancel_completed_or_finished_orders(): void
    {
        $car = Car::factory()->create(['hidden' => true]);

        $completedOrder = Order::factory()->completed()->create(['car_id' => $car->id]);
        $finishedOrder = Order::factory()->finished()->create(['car_id' => $car->id]);

        $this->carService->toggleCarVisibility($car);

        $this->assertDatabaseHas('orders', ['id' => $completedOrder->id, 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['id' => $finishedOrder->id, 'status' => 'finished']);
    }
}
