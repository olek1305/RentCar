<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $rentalDate = $this->faker->dateTimeBetween('+1 day', '+30 days');
        $returnDate = (clone $rentalDate)->modify('+'.rand(1, 7).' days');

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'car_id' => Car::factory(),
            'rental_date' => $rentalDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'rental_time' => $this->faker->randomElement(['08:00', '10:00', '12:00', '14:00', '16:00']),
            'return_time' => $this->faker->randomElement(['08:00', '10:00', '12:00', '14:00', '16:00']),
            'delivery_option' => $this->faker->randomElement(['pickup', 'delivery', 'airport']),
            'delivery_address' => $this->faker->optional()->address(),
            'additional_insurance' => $this->faker->boolean(30),
            'additional_insurance_cost' => 15.00,
            'additional_info' => $this->faker->optional()->sentence(),
            'status' => 'pending',
            'payment_amount' => 5.00,
            'payment_currency' => 'EUR',
            'acceptance_terms' => true,
            'acceptance_privacy' => true,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);
    }

    public function awaitingPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'awaiting_payment',
            'email_verified_at' => now(),
            'payment_link_sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'email_verified_at' => now(),
            'paid_at' => now(),
            'payment_session_id' => 'cs_test_'.bin2hex(random_bytes(16)),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'email_verified_at' => now(),
            'paid_at' => now()->subDays(5),
            'final_paid_at' => now(),
            'payment_session_id' => 'cs_test_'.bin2hex(random_bytes(16)),
            'final_payment_session_id' => 'cs_test_'.bin2hex(random_bytes(16)),
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finished',
            'email_verified_at' => now(),
            'paid_at' => now()->subDays(5),
            'returned_at' => now(),
            'payment_session_id' => 'cs_test_'.bin2hex(random_bytes(16)),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function withEmailVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verification_token' => hash('sha256', 'test-token'),
            'email_verification_sent_at' => now(),
        ]);
    }

    public function withSmsVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'sms_verification_token' => hash('sha256', 'test-token'),
            'sms_verification_sent_at' => now(),
        ]);
    }

    public function awaitingFinalPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'awaiting_final_payment',
            'email_verified_at' => now(),
            'paid_at' => now()->subDays(5),
            'returned_at' => now(),
            'final_payment_link_sent_at' => now(),
            'final_payment_amount' => 150.00,
            'final_payment_session_id' => 'cs_test_'.bin2hex(random_bytes(16)),
        ]);
    }

    public function forDates(string $rentalDate, string $returnDate): static
    {
        return $this->state(fn (array $attributes) => [
            'rental_date' => $rentalDate,
            'return_date' => $returnDate,
        ]);
    }
}
