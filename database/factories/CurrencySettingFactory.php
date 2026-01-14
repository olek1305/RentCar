<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencySettingFactory extends Factory
{
    public function definition(): array
    {
        $currencies = [
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound'],
            ['code' => 'PLN', 'symbol' => 'zł', 'name' => 'Polish Zloty'],
            ['code' => 'CHF', 'symbol' => 'CHF', 'name' => 'Swiss Franc'],
        ];

        $currency = $this->faker->randomElement($currencies);

        return [
            'currency_code' => $currency['code'],
            'currency_symbol' => $currency['symbol'],
            'currency_name' => $currency['name'],
            'is_default' => false,
        ];
    }

    public function eur(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency_code' => 'EUR',
            'currency_symbol' => '€',
            'currency_name' => 'Euro',
        ]);
    }

    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_name' => 'US Dollar',
        ]);
    }

    public function pln(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency_code' => 'PLN',
            'currency_symbol' => 'zł',
            'currency_name' => 'Polish Zloty',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
