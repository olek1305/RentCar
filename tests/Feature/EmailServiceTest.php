<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Order;
use App\Services\CacheService;
use App\Services\MailService;
use App\Services\OrderService;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailService = new MailService();
        $this->smsService = new SmsService();
        $this->cacheService = new CacheService();
        $this->orderService = new OrderService(
            $this->mailService,
            $this->smsService,
            $this->cacheService
        );
    }

    #[Test]
    public function it_verifies_email_with_valid_token()
    {
        $car = Car::factory()->create(['hidden' => false]);

        $orderData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay()->format('Y-m-d'),
            'rental_time_hour' => '10',
            'rental_time_minute' => '00',
            'return_time_hour' => '12',
            'return_time_minute' => '00',
            'airport_delivery' => false,
            'additional_info' => 'Test order',
            'verification_method' => 'email'
        ];

        $result = $this->orderService->createOrder($orderData);
        $order = $result['order'];

        $token = Str::random(32);
        $hashedToken = hash('sha256', $token);
        $order->update([
            'email_verification_token' => $hashedToken,
            'email_verification_sent_at' => now()
        ]);

        $verificationResult = $this->orderService->verifyEmailToken($order->id, $token);

        $this->assertTrue($verificationResult['success']);
        $this->assertEquals(__('Email verified successfully'), $verificationResult['message']);

        $updatedOrder = $order->fresh();
        $this->assertNotNull($updatedOrder->email_verified_at);
        $this->assertNull($updatedOrder->email_verification_token);
        $this->assertEquals('pending', $updatedOrder->status);
    }

    #[Test]
    public function it_fails_to_verify_email_with_invalid_token()
    {
        $car = Car::factory()->create();
        $token = Str::random(32);
        $hashedToken = hash('sha256', $token);


        $order = Order::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay(),
            'rental_time' => '10:00',
            'return_time' => '12:00',
            'status' => 'pending_verification',
            'verification_method' => 'email',
            'email_verification_token' => $hashedToken,
            'email_verified_at' => null
        ]);

        $verificationResult = $this->orderService->verifyEmailToken($order->id, 'invalid_token');

        $this->assertFalse($verificationResult['success']);
        $this->assertEquals(__('Invalid verification token'), $verificationResult['message']);

        $updatedOrder = $order->fresh();
        $this->assertNull($updatedOrder->email_verified_at);
        $this->assertEquals($hashedToken, $updatedOrder->email_verification_token);
        $this->assertEquals('pending_verification', $updatedOrder->status);
    }

    #[Test]
    public function it_fails_to_verify_expired_token()
    {
        $car = Car::factory()->create();
        $token = Str::random(32);
        $hashedToken = hash('sha256', $token);

        $order = Order::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay(),
            'rental_time' => '10:00',
            'return_time' => '12:00',
            'status' => 'pending_verification',
            'verification_method' => 'email',
            'email_verification_token' => $hashedToken,
            'email_verification_sent_at' => now()->subHours(25),
            'email_verified_at' => null
        ]);

        $response = $this->orderService->verifyEmailToken($order->id, $token);

        $this->assertFalse($response['success']);
        $this->assertEquals(__('The verification link has expired. Please request a new one.'), $response['message']);
    }

    #[Test]
    public function it_fails_to_verify_already_verified_email()
    {
        $car = Car::factory()->create();
        $token = Str::random(32);
        $hashedToken = hash('sha256', $token);

        $order = Order::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '123456789',
            'car_id' => $car->id,
            'rental_date' => now()->addDay(),
            'rental_time' => '10:00',
            'return_time' => '12:00',
            'status' => 'pending',
            'verification_method' => 'email',
            'email_verification_token' => $hashedToken,
            'email_verified_at' => now()
        ]);

        $verificationResult = $this->orderService->verifyEmailToken($order->id, $token);

        $this->assertFalse($verificationResult['success']);
        $this->assertEquals(__('Email already verified'), $verificationResult['message']);
    }
}
