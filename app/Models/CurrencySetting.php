<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencySetting extends Model
{
    use HasFactory;

    protected $fillable = ['currency_code', 'currency_symbol', 'currency_name', 'is_default'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public static function getDefaultCurrency(): self
    {
        return self::where('is_default', true)->first() ?? new self([
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_name' => 'US Dollar',
        ]);
    }

    public function prepareForStripe(float $amount): array
    {
        $currency = app('currency');

        // Stripe requires amounts in cents
        $amountInCents = $amount * 100;

        return [
            'amount' => $amountInCents,
            'currency' => strtolower($currency->currency_code),
        ];
    }
}
