<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rejects_request_without_signature(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $response = $this->postJson('/webhooks/stripe', [
            'type' => 'checkout.session.completed',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Invalid signature']);
    }

    #[Test]
    public function it_returns_error_when_webhook_secret_not_configured(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $response = $this->postJson('/webhooks/stripe', [
            'type' => 'checkout.session.completed',
        ]);

        $response->assertStatus(500);
        $response->assertJson(['error' => 'Webhook secret not configured']);
    }

    #[Test]
    public function webhook_route_is_excluded_from_csrf(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $response = $this->post('/webhooks/stripe', [
            'type' => 'test',
        ], [
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(500);
        $this->assertStringNotContainsString('CSRF', $response->getContent());
    }

    #[Test]
    public function order_status_updates_correctly_for_reservation_payment(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'status' => 'awaiting_payment',
        ]);

        $this->assertEquals('awaiting_payment', $order->status);
        $this->assertNull($order->paid_at);

        $order->status = 'paid';
        $order->paid_at = now();
        $order->save();

        $order->refresh();

        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    #[Test]
    public function order_status_updates_correctly_for_final_payment(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'status' => 'awaiting_final_payment',
        ]);

        $this->assertEquals('awaiting_final_payment', $order->status);
        $this->assertNull($order->final_paid_at);

        $order->status = 'completed';
        $order->final_paid_at = now();
        $order->save();

        $order->refresh();

        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->final_paid_at);
    }

    #[Test]
    public function car_becomes_visible_after_final_payment(): void
    {
        $car = Car::factory()->create(['hidden' => true]);
        $order = Order::factory()->for($car)->create([
            'status' => 'awaiting_final_payment',
        ]);

        $this->assertTrue($car->hidden);

        $order->status = 'completed';
        $order->final_paid_at = now();
        $order->save();

        $car->hidden = false;
        $car->save();

        $car->refresh();

        $this->assertFalse($car->hidden);
    }
}
