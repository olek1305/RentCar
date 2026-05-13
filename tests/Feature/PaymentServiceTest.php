<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CurrencySetting;
use App\Models\Order;
use App\Services\MailService;
use App\Services\PaymentService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        CurrencySetting::factory()->eur()->default()->create();

        $this->paymentService = new PaymentService(
            app(MailService::class),
            app(SmsService::class)
        );
    }

    #[Test]
    public function payment_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PaymentService::class, $this->paymentService);
    }

    #[Test]
    public function it_returns_null_when_stripe_key_is_missing(): void
    {
        config(['services.stripe.secret' => null]);

        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'status' => 'verified',
            'payment_currency' => 'EUR',
        ]);

        $result = $this->paymentService->generateReservationPaymentLink($order);

        $this->assertNull($result);
    }

    #[Test]
    public function it_throws_for_final_payment_when_stripe_key_is_missing(): void
    {
        config(['services.stripe.secret' => null]);

        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->finished()->create([
            'payment_currency' => 'EUR',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stripe secret key is not configured');

        $this->paymentService->sendFinalPaymentLink($order);
    }

    #[Test]
    public function it_returns_null_when_stripe_key_is_empty_string(): void
    {
        config(['services.stripe.secret' => '']);

        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'status' => 'verified',
            'payment_currency' => 'EUR',
        ]);

        $result = $this->paymentService->generateReservationPaymentLink($order);

        $this->assertNull($result);
    }

    #[Test]
    public function it_throws_for_zero_final_payment_amount(): void
    {
        config(['services.stripe.secret' => 'sk_test_mock']);

        $car = Car::factory()->create([
            'rental_prices' => json_encode(['1-2' => 2, '3-6' => 1, '7+' => 1]),
        ]);
        $order = Order::factory()->for($car)->create([
            'status' => 'finished',
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDay()->format('Y-m-d'),
            'delivery_option' => 'pickup',
            'additional_insurance' => false,
            'payment_amount' => 100.00,
            'payment_currency' => 'EUR',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(__('messages.no_remaining_amount'));

        $this->paymentService->sendFinalPaymentLink($order);
    }
}
