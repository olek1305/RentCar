<h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('messages.rental_prices') }}</h2>
@php
    $prices = is_array($car->rental_prices)
        ? $car->rental_prices
        : (json_decode($car->rental_prices, true) ?? ['1-2' => 0, '3-6' => 0, '7+' => 0]);

    $price1 = $prices['1-2'] ?? 0;
    $price2 = $prices['3-6'] ?? 0;
    $price3 = $prices['7+'] ?? 0;
@endphp
<ul class="mb-8 space-y-1 text-gray-700">
    <li>1-2 {{ __('messages.days') }}: {!! trans_currency('messages.price_format', $price1) !!}</li>
    <li>3-6 {{ __('messages.days') }}: {!! trans_currency('messages.price_format', $price2) !!}</li>
    <li>7+ {{ __('messages.days') }}: {!! trans_currency('messages.price_format', $price3) !!}</li>
</ul>
