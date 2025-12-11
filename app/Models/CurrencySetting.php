<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencySetting extends Model
{
    protected $fillable = ['currency_code', 'currency_symbol', 'currency_name', 'is_default'];

    public static function getDefaultCurrency()
    {
        return self::where('is_default', true)->first() ?? new self([
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'currency_name' => 'US Dollar',
        ]);
    }

    public function prepareForStripe($amount): array
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
