<x-layout-email>
    <div class="max-w-2xl mx-auto my-8 bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-green-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">{{ __('messages.email_final_payment') }}</h1>
            <p class="text-sm text-green-100 mt-1">{{ config('app.name') }}</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Greeting -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ __('messages.email_greeting', ['name' => $order->first_name . ' ' . $order->last_name]) }}
                </h2>
                <p class="text-gray-600 mt-2">{{ __('messages.email_thanks_return') }}</p>
                <p class="text-gray-600 mt-2">{{ __('messages.email_final_payment_info', ['amount' => number_format($order->calculateFinalPaymentAmount(), 2) . ' ' . ($order->payment_currency ?? 'EUR')]) }}</p>
            </div>

            <!-- Payment Button -->
            <div class="text-center my-8">
                <a href="{{ $paymentLink }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                    {{ __('messages.email_pay_final_amount') }}
                </a>
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
                        <span class="text-gray-800">{{ $order->car->model }}</span>
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
                        <span class="text-gray-600 font-medium">{{ __('messages.rental_days') }}:</span>
                        <span class="text-gray-800">{{ $order->getRentalDays() }}</span>
                    </div>
                    @if($order->additional_insurance)
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.additional_insurance') }}:</span>
                        <span class="text-gray-800">{{ __('messages.yes') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.reservation_paid') }}:</span>
                        <span class="text-gray-800">{{ number_format($order->payment_amount ?? 5, 2) }} {{ $order->payment_currency ?? 'EUR' }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-3 mt-3">
                        <span class="text-gray-600 font-medium">{{ __('messages.remaining_amount') }}:</span>
                        <span class="text-green-600 font-bold">{{ number_format($order->calculateFinalPaymentAmount(), 2) }} {{ $order->payment_currency ?? 'EUR' }}</span>
                    </div>
                </div>
            </div>

            <!-- Important Info -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r mb-6">
                <div class="flex">
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-yellow-800">{{ __('messages.email_important_info') }}</h4>
                        <p class="text-sm text-yellow-700 mt-1">{{ __('messages.email_link_expiry') }}</p>
                    </div>
                </div>
            </div>

            <!-- Contact -->
            <p class="text-gray-600">{{ __('messages.email_contact_us') }}</p>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 border-t border-gray-200">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.email_rights_reserved') }}</p>
            </div>
        </div>
    </div>
</x-layout-email>
