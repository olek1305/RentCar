<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Order;
use App\Services\CacheService;
use App\Services\MailService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;

    protected PaymentService|MockInterface $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->paymentService = Mockery::mock(PaymentService::class);
        $this->paymentService->shouldReceive('generateReservationPaymentLink')
            ->andReturn('https://checkout.stripe.com/mock-session');
        $this->paymentService->shouldReceive('sendReservationPaymentLink')
            ->andReturnNull();

        $this->orderService = new OrderService(
            app(MailService::class),
            app(SmsService::class),
            app(CacheService::class),
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
            'delivery_option' => 'pickup',
            'additional_insurance' => true,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
            'verification_method' => 'email',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('orders', [
            'email' => 'john@gmail.com',
            'status' => 'pending',
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
            'delivery_option' => 'pickup',
            'additional_insurance' => false,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
            'verification_method' => 'email',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertEquals(__('messages.order_unavailable'), $result['message']);
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
            'verification_method' => 'email',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('orders', [
            'email' => 'jane@example.com',
            'status' => 'pending',
            'delivery_option' => 'delivery',
            'delivery_address' => '123 Main Street, City, 12345',
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
            'verification_method' => 'email',
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
            'verification_method' => 'email',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertEquals(__('messages.must_accept_terms_and_privacy'), $result['message']);
    }

    #[Test]
    public function it_checks_car_availability_returns_available_when_no_conflicts()
    {
        $car = Car::factory()->create(['hidden' => false]);

        $result = $this->orderService->checkCarAvailability(
            $car->id,
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        );

        $this->assertTrue($result['available']);
        $this->assertNull($result['message']);
    }

    #[Test]
    public function it_checks_car_availability_returns_unavailable_when_dates_overlap()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create existing order for this car
        Order::factory()->forDates(
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        )->create([
            'car_id' => $car->id,
            'status' => 'paid',
        ]);

        // Try to book overlapping dates
        $result = $this->orderService->checkCarAvailability(
            $car->id,
            now()->addDays(7)->format('Y-m-d'),
            now()->addDays(12)->format('Y-m-d')
        );

        $this->assertFalse($result['available']);
        $this->assertNotNull($result['message']);
        $this->assertArrayHasKey('conflicting_order', $result);
    }

    #[Test]
    public function it_checks_car_availability_ignores_cancelled_orders()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create cancelled order for this car
        Order::factory()->forDates(
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        )->cancelled()->create([
            'car_id' => $car->id,
        ]);

        // Same dates should be available since order was cancelled
        $result = $this->orderService->checkCarAvailability(
            $car->id,
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        );

        $this->assertTrue($result['available']);
    }

    #[Test]
    public function it_checks_car_availability_ignores_finished_orders()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create finished order for this car
        Order::factory()->forDates(
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        )->finished()->create([
            'car_id' => $car->id,
        ]);

        // Same dates should be available since order was finished
        $result = $this->orderService->checkCarAvailability(
            $car->id,
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        );

        $this->assertTrue($result['available']);
    }

    #[Test]
    public function it_checks_car_availability_excludes_specific_order()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create order for this car
        $existingOrder = Order::factory()->forDates(
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        )->create([
            'car_id' => $car->id,
            'status' => 'paid',
        ]);

        // When updating the same order, it should be available (exclude itself)
        $result = $this->orderService->checkCarAvailability(
            $car->id,
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d'),
            $existingOrder->id
        );

        $this->assertTrue($result['available']);
    }

    #[Test]
    public function it_checks_car_availability_detects_adjacent_dates_correctly()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create order from day 5 to day 10
        Order::factory()->forDates(
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        )->create([
            'car_id' => $car->id,
            'status' => 'paid',
        ]);

        // Booking from day 11 should be available (no overlap)
        $result = $this->orderService->checkCarAvailability(
            $car->id,
            now()->addDays(11)->format('Y-m-d'),
            now()->addDays(15)->format('Y-m-d')
        );

        $this->assertTrue($result['available']);
    }

    #[Test]
    public function it_fails_to_create_order_when_car_not_available_for_dates()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create existing order
        Order::factory()->forDates(
            now()->addDays(2)->format('Y-m-d'),
            now()->addDays(5)->format('Y-m-d')
        )->create([
            'car_id' => $car->id,
            'status' => 'paid',
        ]);

        // Try to create order with overlapping dates
        $orderData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@gmail.com',
            'phone' => '987654321',
            'car_id' => $car->id,
            'rental_date' => now()->addDays(3)->format('Y-m-d'),
            'return_date' => now()->addDays(7)->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'delivery_option' => 'pickup',
            'additional_insurance' => false,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
            'verification_method' => 'email',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString(__('messages.car_not_available_for_dates', ['from' => '', 'to' => '']), str_replace([now()->addDays(2)->format('d.m.Y'), now()->addDays(5)->format('d.m.Y')], ['', ''], $result['message']));
    }

    #[Test]
    public function it_returns_unavailable_dates_for_car()
    {
        $car = Car::factory()->create(['hidden' => false]);

        // Create multiple orders
        Order::factory()->forDates(
            now()->addDays(5)->format('Y-m-d'),
            now()->addDays(10)->format('Y-m-d')
        )->create([
            'car_id' => $car->id,
            'status' => 'paid',
        ]);

        Order::factory()->forDates(
            now()->addDays(15)->format('Y-m-d'),
            now()->addDays(20)->format('Y-m-d')
        )->create([
            'car_id' => $car->id,
            'status' => 'pending',
        ]);

        $unavailableDates = $this->orderService->getUnavailableDates($car->id);

        $this->assertCount(2, $unavailableDates);
        $this->assertEquals(now()->addDays(5)->format('Y-m-d'), $unavailableDates[0]['from']);
        $this->assertEquals(now()->addDays(10)->format('Y-m-d'), $unavailableDates[0]['to']);
    }
}
