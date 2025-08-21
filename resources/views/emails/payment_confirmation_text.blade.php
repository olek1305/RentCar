{{ __('messages.email_confirm_payment') }}

{{ __('messages.email_greeting', ['name' => $order->first_name . ' ' . $order->last_name]) }}

{{ strip_tags(__('messages.email_thanks_verification')) }}
{{ strip_tags(__('messages.email_reservation_fee_info', ['amount' => '5 EUR'])) }}

{{ __('messages.email_pay_reservation_fee') }}: {{ $paymentLink }}

{{ __('messages.email_order_details') }}:
- {{ __('messages.email_order_number') }}: #{{ $order->id }}
- {{ __('messages.car') }}: {{ $order->car->model }}
- {{ __('messages.rental_date') }}: {{ $order->rental_date->format('d.m.Y') }}
- {{ __('messages.pickup_time') }}: {{ $order->rental_time }}
- {{ __('messages.return_time') }}: {{ $order->return_time }}
- {{ __('messages.email_reservation_amount') }}: 5 EUR

{{ __('messages.email_important_info') }}:
{{ __('messages.email_link_expiry') }}

{{ __('messages.email_contact_us') }}

--
© {{ date('Y') }} {{ config('app.name') }}
