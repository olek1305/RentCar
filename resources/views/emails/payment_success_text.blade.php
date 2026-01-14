@if($paymentType === 'final')
{{ __('messages.email_final_payment_success') }}
@else
{{ __('messages.email_reservation_payment_success') }}
@endif

{{ __('messages.email_greeting', ['name' => $order->first_name . ' ' . $order->last_name]) }}

@if($paymentType === 'final')
{{ __('messages.email_final_payment_confirmed') }}
@else
{{ __('messages.email_reservation_confirmed') }}
@endif

{{ __('messages.email_order_details') }}:
- {{ __('messages.email_order_number') }}: #{{ $order->id }}
- {{ __('messages.car') }}: {{ $order->car->model }} ({{ $order->car->year }})
- {{ __('messages.rental_date') }}: {{ $order->rental_date->format('d.m.Y') }}
- {{ __('messages.return_date') }}: {{ $order->return_date->format('d.m.Y') }}
- {{ __('messages.pickup_time') }}: {{ $order->rental_time }}
@if($paymentType === 'final')
- {{ __('messages.amount_paid') }}: {{ $order->payment_currency }} {{ number_format($order->final_payment_amount, 2) }}
@else
- {{ __('messages.reservation_fee_paid') }}: {{ $order->payment_currency }} {{ number_format($order->payment_amount, 2) }}
@endif

{{ __('messages.email_next_steps') }}:
@if($paymentType === 'final')
{{ __('messages.email_thank_you_for_rental') }}
@else
{{ __('messages.email_pickup_instructions') }}
@endif

{{ __('messages.email_contact_us') }}

--
{{ date('Y') }} {{ config('app.name') }}
