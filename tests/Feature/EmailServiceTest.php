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
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected MailService $mailService;
    protected SmsService $smsService;
    protected CacheService $cacheService;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailService = new MailService();
        $this->smsService = new SmsService();
        $this->cacheService = new CacheService();
        $this->paymentService = new PaymentService($this->mailService, $this->smsService);
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
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'additional_info' => 'Test order',
            'delivery_option' => 'pickup',
        ];

        $result = $this->orderService->createOrder($orderData);

        $this->assertTrue($result['success']);
        $this->assertEquals('awaiting_payment', $result['order']->status);
        $this->assertFalse($result['requires_verification']);

        // Car should be hidden after a successful order
        $this->assertTrue($car->fresh()->hidden);
    }

}
