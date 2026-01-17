<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CurrencySetting;
use App\Models\Order;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CurrencySetting::factory()->eur()->default()->create();
    }

    #[Test]
    public function it_cancels_pending_order(): void
    {
        $car = Car::factory()->create(['hidden' => true]);
        $order = Order::factory()->pending()->create([
            'car_id' => $car->id,
        ]);

        $cacheService = Mockery::mock(CacheService::class);
        $cacheService->shouldReceive('clearCarsCache')->once();
        $this->app->instance(CacheService::class, $cacheService);

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        $car->refresh();
        $this->assertFalse($car->hidden);
    }

    #[Test]
    public function it_cancels_awaiting_payment_order(): void
    {
        $car = Car::factory()->create(['hidden' => true]);
        $order = Order::factory()->create([
            'car_id' => $car->id,
            'status' => 'awaiting_payment',
        ]);

        $cacheService = Mockery::mock(CacheService::class);
        $cacheService->shouldReceive('clearCarsCache')->once();
        $this->app->instance(CacheService::class, $cacheService);

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    #[Test]
    public function it_cancels_awaiting_final_payment_order(): void
    {
        $car = Car::factory()->create(['hidden' => true]);
        $order = Order::factory()->create([
            'car_id' => $car->id,
            'status' => 'awaiting_final_payment',
        ]);

        $cacheService = Mockery::mock(CacheService::class);
        $cacheService->shouldReceive('clearCarsCache')->once();
        $this->app->instance(CacheService::class, $cacheService);

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    #[Test]
    public function it_prevents_cancellation_of_paid_order(): void
    {
        $order = Order::factory()->paid()->create();

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    #[Test]
    public function it_prevents_cancellation_of_completed_order(): void
    {
        $order = Order::factory()->completed()->create();

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('completed', $order->status);
    }

    #[Test]
    public function it_prevents_cancellation_of_finished_order(): void
    {
        $order = Order::factory()->finished()->create();

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('finished', $order->status);
    }

    #[Test]
    public function it_prevents_cancellation_of_verified_order(): void
    {
        $order = Order::factory()->verified()->create();

        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $response->assertRedirect('/');
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('verified', $order->status);
    }

    #[Test]
    public function it_makes_car_available_after_cancellation(): void
    {
        $car = Car::factory()->create(['hidden' => true]);
        $order = Order::factory()->pending()->create([
            'car_id' => $car->id,
        ]);

        $cacheService = Mockery::mock(CacheService::class);
        $cacheService->shouldReceive('clearCarsCache')->once();
        $this->app->instance(CacheService::class, $cacheService);

        $this->get(URL::signedRoute('payment.cancel', ['order' => $order]));

        $car->refresh();
        $this->assertFalse($car->hidden);
    }

    #[Test]
    public function cancel_route_requires_valid_signature(): void
    {
        $order = Order::factory()->pending()->create();

        $response = $this->get('/payment/cancel/'.$order->id);

        $response->assertForbidden();
    }

    #[Test]
    public function cancel_route_requires_valid_order(): void
    {
        $response = $this->get(URL::signedRoute('payment.cancel', ['order' => 99999]));

        $response->assertNotFound();
    }

    #[Test]
    public function success_route_requires_valid_order(): void
    {
        $response = $this->get(route('payment.success', ['order' => 99999, 'session_id' => 'test']));

        $response->assertNotFound();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}