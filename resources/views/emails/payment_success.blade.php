<x-layout-email>
    <div class="max-w-2xl mx-auto my-8 bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-green-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">
                @if($paymentType === 'final')
                    {{ __('messages.email_final_payment_success') }}
                @else
                    {{ __('messages.email_reservation_payment_success') }}
                @endif
            </h1>
            <p class="text-sm text-green-100 mt-1">{{ config('app.name') }}</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Success Icon -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Greeting -->
            <div class="mb-6 text-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ __('messages.email_greeting', ['name' => $order->first_name . ' ' . $order->last_name]) }}
                </h2>
                <p class="text-gray-600 mt-2">
                    @if($paymentType === 'final')
                        {{ __('messages.email_final_payment_confirmed') }}
                    @else
                        {{ __('messages.email_reservation_confirmed') }}
                    @endif
                </p>
            </div>

            <!-- Order Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.email_order_details') }}</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.email_order_number') }}:</span>
                        <span class="text-gray-800">#{{ $order->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.car') }}:</span>
                        <span class="text-gray-800">{{ $order->car->model }} ({{ $order->car->year }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.rental_date') }}:</span>
                        <span class="text-gray-800">{{ $order->rental_date->format('d.m.Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.return_date') }}:</span>
                        <span class="text-gray-800">{{ $order->return_date->format('d.m.Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.pickup_time') }}:</span>
                        <span class="text-gray-800">{{ $order->rental_time }}</span>
                    </div>
                    @if($paymentType === 'final')
                        <div class="flex justify-between border-t pt-3 mt-3">
                            <span class="text-gray-600 font-medium">{{ __('messages.amount_paid') }}:</span>
                            <span class="text-green-600 font-bold">{{ $currency->currency_symbol }}{{ number_format($order->final_payment_amount, 2) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between border-t pt-3 mt-3">
                            <span class="text-gray-600 font-medium">{{ __('messages.reservation_fee_paid') }}:</span>
                            <span class="text-green-600 font-bold">{{ $currency->currency_symbol }}{{ number_format($order->payment_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r mb-6">
                <div class="flex">
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-blue-800">{{ __('messages.email_next_steps') }}</h4>
                        @if($paymentType === 'final')
                            <p class="text-sm text-blue-700 mt-1">{{ __('messages.email_thank_you_for_rental') }}</p>
                        @else
                            <p class="text-sm text-blue-700 mt-1">{{ __('messages.email_pickup_instructions') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <p class="text-gray-600 text-center">{{ __('messages.email_contact_us') }}</p>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 border-t border-gray-200">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.email_rights_reserved') }}</p>
            </div>
        </div>
    </div>
</x-layout-email>
