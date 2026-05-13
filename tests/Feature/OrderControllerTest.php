<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('generateReservationPaymentLink')
            ->andReturn('https://checkout.stripe.com/mock-session');
        $paymentService->shouldReceive('sendReservationPaymentLink')
            ->andReturnNull();

        $this->app->instance(PaymentService::class, $paymentService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function getValidOrderData(Car $car): array
    {
        return [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan.kowalski@gmail.com',
            'phone' => '+48123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay()->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'delivery_option' => 'pickup',
            'additional_insurance' => true,
            'acceptance_terms' => '1',
            'acceptance_privacy' => '1',
            'verification_method' => 'email',
        ];
    }

    #[Test]
    public function it_creates_order_successfully(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $response = $this->post(route('orders.store'), $this->getValidOrderData($car));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'email' => 'jan.kowalski@gmail.com',
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_fails_with_missing_required_fields(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), [
                'car_id' => $car->id,
            ]);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
            'phone',
            'rental_date',
            'return_date',
        ]);
    }

    #[Test]
    public function it_fails_with_invalid_email_format(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['email'] = 'invalid-email';

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function it_fails_with_invalid_phone(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['phone'] = 'abc';

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['phone']);
    }

    #[Test]
    public function it_fails_when_return_date_before_rental_date(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['rental_date'] = now()->addDays(5)->format('Y-m-d');
        $data['return_date'] = now()->addDays(2)->format('Y-m-d');

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['return_date']);
    }

    #[Test]
    public function it_fails_when_terms_not_accepted(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        unset($data['acceptance_terms']);

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['acceptance_terms']);
    }

    #[Test]
    public function it_requires_delivery_address_for_delivery_option(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['delivery_option'] = 'delivery';

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['delivery_address']);
    }

    #[Test]
    public function it_creates_order_with_delivery_address(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['delivery_option'] = 'delivery';
        $data['delivery_address'] = 'ul. Testowa 123, Warszawa';

        $response = $this->post(route('orders.store'), $data);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('orders', [
            'email' => 'jan.kowalski@gmail.com',
            'delivery_option' => 'delivery',
            'delivery_address' => 'ul. Testowa 123, Warszawa',
        ]);
    }

    #[Test]
    public function it_fails_for_nonexistent_car(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['car_id'] = 99999;

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['car_id']);
    }

    #[Test]
    public function it_fails_for_hidden_car(): void
    {
        $car = Car::factory()->create(['hidden' => true]);

        $response = $this->post(route('orders.store'), $this->getValidOrderData($car));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function it_validates_rental_time_hours(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);
        $data['rental_time_hour'] = '25';

        $response = $this->from(route('cars.show', $car))
            ->post(route('orders.store'), $data);

        $response->assertRedirect(route('cars.show', $car));
        $response->assertSessionHasErrors(['rental_time_hour']);
    }

    #[Test]
    public function verify_email_for_payment_with_valid_token(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $rawToken = 'valid-token-123';

        $order = Order::factory()->for($car)->create([
            'status' => 'pending',
            'email_verification_token' => hash('sha256', $rawToken),
            'email_verification_sent_at' => now(),
        ]);

        $response = $this->get(route('orders.verify-email-payment', [
            'order' => $order->id,
            'token' => $rawToken,
        ]));

        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('verified', $order->status);
        $this->assertNotNull($order->email_verified_at);
        $this->assertNull($order->email_verification_token);
    }

    #[Test]
    public function verify_email_for_payment_with_invalid_token(): void
    {
        $car = Car::factory()->create(['hidden' => false]);

        $order = Order::factory()->for($car)->create([
            'status' => 'pending',
            'email_verification_token' => hash('sha256', 'correct-token'),
            'email_verification_sent_at' => now(),
        ]);

        $response = $this->get(route('orders.verify-email-payment', [
            'order' => $order->id,
            'token' => 'wrong-token',
        ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    #[Test]
    public function verify_email_for_payment_with_expired_token(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $rawToken = 'valid-token-123';

        $order = Order::factory()->for($car)->create([
            'status' => 'pending',
            'email_verification_token' => hash('sha256', $rawToken),
            'email_verification_sent_at' => now()->subHours(10),
        ]);

        $response = $this->get(route('orders.verify-email-payment', [
            'order' => $order->id,
            'token' => $rawToken,
        ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('pending', $order->status);
    }

    #[Test]
    public function order_store_is_throttled(): void
    {
        $car = Car::factory()->create(['hidden' => false]);
        $data = $this->getValidOrderData($car);

        for ($i = 0; $i < 11; $i++) {
            $response = $this->post(route('orders.store'), $data);
        }

        $response->assertStatus(429);
    }
}
