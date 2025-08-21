<p>Hello {{ $order->first_name }},</p>
<p>Please complete your payment for order #{{ $order->id }} by clicking the link below:</p>
<a href="{{ $paymentLink }}">{{ __('messages.pay_now') }}</a>
<p>Thank you!</p>
