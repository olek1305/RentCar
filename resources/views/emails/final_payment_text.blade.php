{{ __('messages.email_final_payment') }}

{{ __('messages.email_greeting', ['name' => $order->first_name . ' ' . $order->last_name]) }}

{{ strip_tags(__('messages.email_thanks_return')) }}
{{ strip_tags(__('messages.email_final_payment_info', ['amount' => number_format($order->calculateFinalPaymentAmount(), 2) . ' ' . ($order->payment_currency ?? 'EUR')])) }}

{{ __('messages.email_pay_final_amount') }}: {{ $paymentLink }}

{{ __('messages.email_order_details') }}:
- {{ __('messages.email_order_number') }}: #{{ $order->id }}
- {{ __('messages.car') }}: {{ $order->car->model }}
- {{ __('messages.rental_date') }}: {{ $order->rental_date->format('d.m.Y') }}
- {{ __('messages.return_date') }}: {{ $order->return_date->format('d.m.Y') }}
- {{ __('messages.rental_days') }}: {{ $order->getRentalDays() }}
- {{ __('messages.reservation_paid') }}: {{ number_format($order->payment_amount ?? 5, 2) }} {{ $order->payment_currency ?? 'EUR' }}
- {{ __('messages.remaining_amount') }}: {{ number_format($order->calculateFinalPaymentAmount(), 2) }} {{ $order->payment_currency ?? 'EUR' }}

{{ __('messages.email_important_info') }}:
{{ __('messages.email_link_expiry') }}

{{ __('messages.email_contact_us') }}

--
{{ date('Y') }} {{ config('app.name') }}
