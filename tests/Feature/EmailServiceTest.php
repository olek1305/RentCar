<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Services\CacheService;
use App\Services\MailService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected MailService $mailService;
    protected SmsService $smsService;
    protected CacheService $cacheService;
    protected PaymentService|MockInterface $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailService = new MailService();
        $this->smsService = new SmsService();
        $this->cacheService = new CacheService();

        // Mock the PaymentService to avoid actual Stripe calls
        $this->paymentService = Mockery::mock(PaymentService::class, [$this->mailService, $this->smsService]);

        // Mock both methods that are called
        $this->paymentService->shouldReceive('generatePaymentLink')
            ->andReturn('https://example.com/payment/mock-payment-link');

        $this->paymentService->shouldReceive('sendReservationPaymentLink')
            ->andReturn(true); // Assuming it returns true on success

        $this->orderService = new OrderService(
            $this->mailService,
            $this->smsService,
            $this->cacheService,
            $this->paymentService
        );
    }


    #[Test]
    public function it_sends_payment_link_immediately_after_order_creation()
    {
        $car = Car::factory()->create(['hidden' => false]);

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@gmail.com',
            'phone' => '123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'additional_info' => 'Test order',
            'delivery_option' => 'pickup',
            'additional_insurance' => true,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertEquals('pending', $result['order']->status);
        $this->assertFalse($result['requires_verification']);

        // Car should be hidden after a successful order
        $this->assertTrue($car->fresh()->hidden);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
