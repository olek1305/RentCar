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

class OrderServiceTest extends TestCase
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
    public function it_creates_an_order_and_hides_car()
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
            'delivery_option' => "pickup",
            'additional_insurance' => true,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('orders', [
            'email' => 'john@gmail.com',
            'status' => 'pending'
        ]);
        $this->assertTrue($car->fresh()->hidden);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_fails_to_order_a_hidden_car()
    {
        $hiddenCar = Car::factory()->create(['hidden' => true]);

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@gmail.com',
            'phone' => '123456789',
            'car_id' => $hiddenCar->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'additional_info' => 'Test order',
            'delivery_option' => "pickup",
            'additional_insurance' => false,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertEquals(__('message.order_unavailable'), $result['message']);
    }

    #[Test]
    public function it_creates_an_order_with_delivery_service_and_address()
    {
        $car = Car::factory()->create(['hidden' => false]);

        $orderData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '987654321',
            'car_id' => $car->id,
            'rental_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'additional_info' => 'Delivery order test',
            'delivery_option' => 'delivery',
            'delivery_address' => '123 Main Street, City, 12345',
            'additional_insurance' => true,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('orders', [
            'email' => 'jane@example.com',
            'status' => 'pending',
            'delivery_option' => 'delivery',
            'delivery_address' => '123 Main Street, City, 12345'
        ]);
        $this->assertTrue($car->fresh()->hidden);
    }

    #[Test]
    public function it_fails_when_delivery_address_is_missing_for_delivery_service()
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
            'delivery_option' => 'delivery',
            'delivery_address' => '', // Empty address
            'additional_insurance' => true,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertEquals(__('messages.delivery_address_required'), $result['message']);
    }

    #[Test]
    public function it_fails_when_terms_are_not_accepted()
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
            'delivery_option' => 'pickup',
            'additional_insurance' => true,
            'acceptance_terms' => '', // Not accepted
            'acceptance_privacy' => '1',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertEquals(__('messages.must_accept_terms_and_privacy'), $result['message']);
    }
}
