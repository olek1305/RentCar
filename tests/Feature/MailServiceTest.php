<?php

namespace Tests\Feature;

use App\Mail\FinalPaymentMail;
use App\Mail\PaymentConfirmationMail;
use App\Mail\PaymentSuccessMail;
use App\Models\Car;
use App\Models\Order;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MailService $mailService;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->mailService = new MailService;
    }

    #[Test]
    public function it_sends_payment_link_email(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'test@gmail.com',
        ]);

        $result = $this->mailService->sendPaymentLink($order, 'https://stripe.com/pay/123');

        $this->assertTrue($result);

        Mail::assertQueued(PaymentConfirmationMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email);
        });
    }

    #[Test]
    public function it_returns_false_for_invalid_email(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'invalid-email',
        ]);

        $result = $this->mailService->sendPaymentLink($order, 'https://stripe.com/pay/123');

        $this->assertFalse($result);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_returns_false_for_empty_email(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => '',
        ]);

        $result = $this->mailService->sendPaymentLink($order, 'https://stripe.com/pay/123');

        $this->assertFalse($result);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_returns_false_for_empty_payment_link(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'test@gmail.com',
        ]);

        $result = $this->mailService->sendPaymentLink($order, '');

        $this->assertFalse($result);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sends_payment_confirmation_email(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'customer@gmail.com',
        ]);

        $result = $this->mailService->sendPaymentConfirmation($order, 'https://stripe.com/confirm/456');

        $this->assertTrue($result);

        Mail::assertQueued(PaymentConfirmationMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email);
        });
    }

    #[Test]
    public function it_sends_final_payment_link_email(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'customer@gmail.com',
        ]);

        $result = $this->mailService->sendFinalPaymentLink($order, 'https://stripe.com/final/789');

        $this->assertTrue($result);

        Mail::assertQueued(FinalPaymentMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email);
        });
    }

    #[Test]
    public function it_returns_false_for_empty_final_payment_link(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'customer@gmail.com',
        ]);

        $result = $this->mailService->sendFinalPaymentLink($order, '');

        $this->assertFalse($result);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sends_payment_success_email_for_reservation(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'customer@gmail.com',
        ]);

        $result = $this->mailService->sendPaymentSuccess($order, 'reservation');

        $this->assertTrue($result);

        Mail::assertQueued(PaymentSuccessMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email);
        });
    }

    #[Test]
    public function it_sends_payment_success_email_for_final_payment(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'customer@gmail.com',
        ]);

        $result = $this->mailService->sendPaymentSuccess($order, 'final');

        $this->assertTrue($result);

        Mail::assertQueued(PaymentSuccessMail::class, function ($mail) use ($order) {
            return $mail->hasTo($order->email);
        });
    }

    #[Test]
    public function it_returns_false_for_null_email(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->make([
            'email' => null,
        ]);
        $order->id = 1;

        $result = $this->mailService->sendPaymentLink($order, 'https://stripe.com/pay/123');

        $this->assertFalse($result);

        Mail::assertNothingSent();
    }

    #[Test]
    public function it_sends_multiple_emails_to_same_customer(): void
    {
        $car = Car::factory()->create();
        $order = Order::factory()->for($car)->create([
            'email' => 'customer@gmail.com',
        ]);

        $this->mailService->sendPaymentLink($order, 'https://stripe.com/pay/1');
        $this->mailService->sendPaymentConfirmation($order, 'https://stripe.com/confirm/2');
        $this->mailService->sendPaymentSuccess($order, 'reservation');

        Mail::assertQueuedCount(3);
    }
}
