<?php

namespace Tests\Unit;

use App\Models\Car;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_static_reservation_fee()
    {
        $fee = Order::getStaticReservationFee();

        $this->assertEquals(5.00, $fee);
    }

    #[Test]
    public function it_returns_reservation_fee_for_order()
    {
        $order = new Order;

        $this->assertEquals(5.00, $order->getReservationFee());
    }

    #[Test]
    public function it_returns_static_additional_insurance_cost()
    {
        $cost = Order::getStaticAdditionalInsuranceCost();

        $this->assertEquals(15.00, $cost);
    }

    #[Test]
    public function it_returns_additional_insurance_cost_when_enabled()
    {
        $order = Order::factory()->make([
            'additional_insurance' => true,
            'additional_insurance_cost' => 20.00,
        ]);

        $this->assertEquals(20.00, $order->getAdditionalInsuranceCost());
    }

    #[Test]
    public function it_returns_zero_insurance_cost_when_disabled()
    {
        $order = Order::factory()->make([
            'additional_insurance' => false,
        ]);

        $this->assertEquals(0.00, $order->getAdditionalInsuranceCost());
    }

    #[Test]
    public function it_returns_default_insurance_cost_when_not_set()
    {
        $order = Order::factory()->make([
            'additional_insurance' => true,
            'additional_insurance_cost' => null,
        ]);

        $this->assertEquals(15.00, $order->getAdditionalInsuranceCost());
    }

    #[Test]
    public function it_can_send_payment_link_for_pending_status()
    {
        $order = Order::factory()->make(['status' => 'pending']);

        $this->assertTrue($order->canSendPaymentLink());
    }

    #[Test]
    public function it_can_send_payment_link_for_confirmed_status()
    {
        $order = Order::factory()->make(['status' => 'confirmed']);

        $this->assertTrue($order->canSendPaymentLink());
    }

    #[Test]
    public function it_cannot_send_payment_link_for_paid_status()
    {
        $order = Order::factory()->make(['status' => 'paid']);

        $this->assertFalse($order->canSendPaymentLink());
    }

    #[Test]
    public function it_cannot_send_payment_link_for_cancelled_status()
    {
        $order = Order::factory()->make(['status' => 'cancelled']);

        $this->assertFalse($order->canSendPaymentLink());
    }

    #[Test]
    public function it_can_be_finished_when_paid()
    {
        $order = Order::factory()->make(['status' => 'paid']);

        $this->assertTrue($order->canBeFinished());
    }

    #[Test]
    public function it_can_be_finished_when_completed()
    {
        $order = Order::factory()->make(['status' => 'completed']);

        $this->assertTrue($order->canBeFinished());
    }

    #[Test]
    public function it_cannot_be_finished_when_pending()
    {
        $order = Order::factory()->make(['status' => 'pending']);

        $this->assertFalse($order->canBeFinished());
    }

    #[Test]
    public function it_cannot_be_finished_when_cancelled()
    {
        $order = Order::factory()->make(['status' => 'cancelled']);

        $this->assertFalse($order->canBeFinished());
    }

    #[Test]
    public function it_calculates_rental_days_correctly()
    {
        $order = Order::factory()->make([
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertEquals(5, $order->getRentalDays());
    }

    #[Test]
    public function it_returns_minimum_one_day_for_same_day_rental()
    {
        $order = Order::factory()->make([
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->format('Y-m-d'),
        ]);

        $this->assertEquals(1, $order->getRentalDays());
    }

    #[Test]
    public function it_calculates_total_amount_from_payment_amount()
    {
        $order = Order::factory()->make([
            'payment_amount' => 150.00,
        ]);

        $this->assertEquals(150.00, $order->calculateTotalAmount());
    }

    #[Test]
    public function it_calculates_final_payment_amount()
    {
        $car = Car::factory()->create([
            'rental_prices' => json_encode([
                '1-2' => 50,
                '3-6' => 45,
                '7+' => 40,
            ]),
        ]);

        $order = Order::factory()->create([
            'car_id' => $car->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'delivery_option' => 'pickup',
            'additional_insurance' => false,
            'payment_amount' => 5.00, // Reservation fee
        ]);

        // 3 days * 45 (3-6 day rate) = 135, minus 5 reservation fee = 130
        $finalAmount = $order->calculateFinalPaymentAmount();

        $this->assertEquals(130.00, $finalAmount);
    }

    #[Test]
    public function it_calculates_final_payment_with_delivery_fee()
    {
        $car = Car::factory()->create([
            'rental_prices' => json_encode([
                '1-2' => 50,
                '3-6' => 45,
                '7+' => 40,
            ]),
        ]);

        $order = Order::factory()->create([
            'car_id' => $car->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_option' => 'delivery',
            'additional_insurance' => false,
            'payment_amount' => 5.00,
        ]);

        // 2 days * 50 (1-2 day rate) = 100 + 20 delivery fee = 120, minus 5 = 115
        $finalAmount = $order->calculateFinalPaymentAmount();

        $this->assertEquals(115.00, $finalAmount);
    }

    #[Test]
    public function it_calculates_final_payment_with_airport_fee()
    {
        $car = Car::factory()->create([
            'rental_prices' => json_encode([
                '1-2' => 50,
                '3-6' => 45,
                '7+' => 40,
            ]),
        ]);

        $order = Order::factory()->create([
            'car_id' => $car->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_option' => 'airport',
            'additional_insurance' => false,
            'payment_amount' => 5.00,
        ]);

        // 2 days * 50 (1-2 day rate) = 100 + 10 airport fee = 110, minus 5 = 105
        $finalAmount = $order->calculateFinalPaymentAmount();

        $this->assertEquals(105.00, $finalAmount);
    }

    #[Test]
    public function it_calculates_final_payment_with_insurance()
    {
        $car = Car::factory()->create([
            'rental_prices' => json_encode([
                '1-2' => 50,
                '3-6' => 45,
                '7+' => 40,
            ]),
        ]);

        $order = Order::factory()->create([
            'car_id' => $car->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_option' => 'pickup',
            'additional_insurance' => true,
            'additional_insurance_cost' => 15.00,
            'payment_amount' => 5.00,
        ]);

        // 2 days * 50 = 100 + (2 days * 15 insurance) = 130, minus 5 = 125
        $finalAmount = $order->calculateFinalPaymentAmount();

        $this->assertEquals(125.00, $finalAmount);
    }

    #[Test]
    public function it_returns_zero_for_negative_final_payment()
    {
        $car = Car::factory()->create([
            'rental_prices' => json_encode([
                '1-2' => 2,
                '3-6' => 1,
                '7+' => 1,
            ]),
        ]);

        $order = Order::factory()->create([
            'car_id' => $car->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(1)->format('Y-m-d'),
            'delivery_option' => 'pickup',
            'additional_insurance' => false,
            'payment_amount' => 100.00, // Paid more than total
        ]);

        $finalAmount = $order->calculateFinalPaymentAmount();

        $this->assertEquals(0, $finalAmount);
    }

    #[Test]
    public function it_uses_appropriate_price_tier_for_long_rentals()
    {
        $car = Car::factory()->create([
            'rental_prices' => json_encode([
                '1-2' => 50,
                '3-6' => 45,
                '7+' => 40,
            ]),
        ]);

        $order = Order::factory()->create([
            'car_id' => $car->id,
            'rental_date' => now()->format('Y-m-d'),
            'return_date' => now()->addDays(10)->format('Y-m-d'),
            'delivery_option' => 'pickup',
            'additional_insurance' => false,
            'payment_amount' => 5.00,
        ]);

        // 10 days * 40 (7+ day rate) = 400, minus 5 = 395
        $finalAmount = $order->calculateFinalPaymentAmount();

        $this->assertEquals(395.00, $finalAmount);
    }

    #[Test]
    public function it_returns_all_statuses()
    {
        $statuses = Order::statuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('pending', $statuses);
        $this->assertArrayHasKey('verified', $statuses);
        $this->assertArrayHasKey('awaiting_payment', $statuses);
        $this->assertArrayHasKey('confirmed', $statuses);
        $this->assertArrayHasKey('paid', $statuses);
        $this->assertArrayHasKey('completed', $statuses);
        $this->assertArrayHasKey('returned', $statuses);
        $this->assertArrayHasKey('finished', $statuses);
        $this->assertArrayHasKey('awaiting_final_payment', $statuses);
        $this->assertArrayHasKey('cancelled', $statuses);
    }

    #[Test]
    public function it_belongs_to_car()
    {
        $car = Car::factory()->create();
        $order = Order::factory()->create(['car_id' => $car->id]);

        $this->assertInstanceOf(Car::class, $order->car);
        $this->assertEquals($car->id, $order->car->id);
    }
}
