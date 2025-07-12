<?php

use App\Models\CurrencySetting;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Foundation\Application;

function trans_currency($key, $price, $replace = [], $locale = null): Application|array|string|Translator|null
{
    try {
        $currency = app()->bound('currency')
            ? app('currency')
            : CurrencySetting::getDefaultCurrency();

        $replace['price'] = $currency->currency_symbol . number_format($price, 2);

        return __($key, $replace, $locale);
    } catch (\Exception $e) {
        return __($key, ['price' => number_format($price, 2)]);
    }
}
