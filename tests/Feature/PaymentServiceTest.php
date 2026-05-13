<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\CurrencySetting;
use App\Models\Order;
use App\Services\MailService;
use App\Services\PaymentService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected $mailService;

    protected $smsService;

    protected function setUp(): void
    {
        parent::setUp();

        CurrencySetting::factory()->eur()->default()->create();

        $this->mailService = Mockery::mock(MailService::class);
        $this->smsService = Mockery::mock(SmsService::class);

        $this->paymentService = new PaymentService(
            $this->mailService,
            $this->smsService
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
        $order = Order::factory()->create([
            'car_id' => $car->id,
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
        $order = Order::factory()->finished()->create([
            'car_id' => $car->id,
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
        $order = Order::factory()->create([
            'car_id' => $car->id,
            'status' => 'verified',
            'payment_currency' => 'EUR',
        ]);

        $result = $this->paymentService->generateReservationPaymentLink($order);

        $this->assertNull($result);
    }

    #[Test]
    public function order_has_correct_reservation_fee(): void
    {
        $order = Order::factory()->create();

        $this->assertEquals(5.0, $order->getReservationFee());
    }

    #[Test]
    public function order_has_correct_static_reservation_fee(): void
    {
        $this->assertEquals(5.0, Order::getStaticReservationFee());
    }

    #[Test]
    public function order_has_correct_static_insurance_cost(): void
    {
        $this->assertEquals(15.0, Order::getStaticAdditionalInsuranceCost());
    }

    #[Test]
    public function order_without_insurance_has_zero_insurance_cost(): void
    {
        $order = Order::factory()->create([
            'additional_insurance' => false,
        ]);

        $this->assertEquals(0.0, $order->getAdditionalInsuranceCost());
    }

    #[Test]
    public function order_with_insurance_has_correct_insurance_cost(): void
    {
        $order = Order::factory()->create([
            'additional_insurance' => true,
        ]);

        $this->assertEquals(15.0, $order->getAdditionalInsuranceCost());
    }

    #[Test]
    public function order_can_send_payment_link_when_pending(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertTrue($order->canSendPaymentLink());
    }

    #[Test]
    public function order_can_send_payment_link_when_confirmed(): void
    {
        $order = Order::factory()->create(['status' => 'confirmed']);

        $this->assertTrue($order->canSendPaymentLink());
    }

    #[Test]
    public function order_cannot_send_payment_link_when_paid(): void
    {
        $order = Order::factory()->paid()->create();

        $this->assertFalse($order->canSendPaymentLink());
    }

    #[Test]
    public function order_cannot_send_payment_link_when_completed(): void
    {
        $order = Order::factory()->completed()->create();

        $this->assertFalse($order->canSendPaymentLink());
    }

    #[Test]
    public function order_can_be_finished_when_paid(): void
    {
        $order = Order::factory()->paid()->create();

        $this->assertTrue($order->canBeFinished());
    }

    #[Test]
    public function order_can_be_finished_when_completed(): void
    {
        $order = Order::factory()->completed()->create();

        $this->assertTrue($order->canBeFinished());
    }

    #[Test]
    public function order_cannot_be_finished_when_pending(): void
    {
        $order = Order::factory()->pending()->create();

        $this->assertFalse($order->canBeFinished());
    }

    #[Test]
    public function order_calculates_rental_days_correctly(): void
    {
        $order = Order::factory()->create([
            'rental_date' => now(),
            'return_date' => now()->addDays(5),
        ]);

        $this->assertEquals(5, $order->getRentalDays());
    }

    #[Test]
    public function order_returns_minimum_one_day_for_same_day_rental(): void
    {
        $order = Order::factory()->create([
            'rental_date' => now(),
            'return_date' => now(),
        ]);

        $this->assertEquals(1, $order->getRentalDays());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
