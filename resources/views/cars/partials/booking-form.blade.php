{{-- Booking Form --}}
<form method="POST" action="{{ route('orders.store') }}" class="space-y-6 bg-white p-6 rounded-lg shadow-md"
      id="booking-form">
    @csrf
    <input type="hidden" name="car_id" value="{{ $car->id }}">
    <input type="hidden" name="total_amount" id="total_amount" value="">

    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20">
                    <path fill="currentColor" fill-rule="evenodd"
                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                    {{ __('messages.reservation_fee_required') }}
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>{{ __('messages.reservation_fee_text', ['amount' => '20-50 ' . $currency->currency_symbol]) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-medium text-gray-700">{{ __('messages.first_name') }} <span
                    class="text-red-500">*</span></label>
            <input type="text" name="first_name" required value="{{ old('first_name') }}"
                   class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
        </div>
        <div>
            <label class="block font-medium text-gray-700">{{ __('messages.last_name') }} <span
                    class="text-red-500">*</span></label>
            <input type="text" name="last_name" required value="{{ old('last_name') }}"
                   class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-medium text-gray-700">Email<span
                    class="text-red-500">*</span></label>
            <input type="email" name="email" required value="{{ old('email') }}"
                   class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.payment_link_will_be_sent_email') }}</p>
        </div>
        <div>
            <label class="block font-medium text-gray-700">{{ __('messages.phone') }} <span
                    class="text-red-500">*</span></label>
            <input type="tel" name="phone" required value="{{ old('phone') }}"
                   class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.payment_link_will_be_sent_sms') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block font-medium text-gray-700">{{ __('messages.rental_date') }} <span
                    class="text-red-500">*</span></label>
            <input type="date" name="rental_date" id="rental_date" required min="{{ date('Y-m-d') }}"
                   value="{{ old('rental_date') }}"
                   class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
        </div>
        <div>
            <label class="block font-medium text-gray-700">{{ __('messages.return_date') }} <span
                    class="text-red-500">*</span></label>
            <input type="date" name="return_date" id="return_date" required min="{{ date('Y-m-d') }}"
                   value="{{ old('return_date') }}"
                   class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Rental Time -->
        <div class="border border-gray-300 rounded-md p-4">
                    <span class="block text-gray-900 font-semibold mb-4 text-lg">
                        {{ __('messages.rental_time') }} <span class="text-red-600">*</span>
                    </span>
            <div class="flex items-center space-x-4 justify-center">
                <!-- Hours -->
                <div class="flex flex-col items-center space-y-2 w-20">
                    <button
                        type="button"
                        onclick="adjustTime('rental', 'hour', 1)"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        ↑
                    </button>
                    <input
                        type="text"
                        name="rental_time_hour"
                        id="rental_time_hour"
                        value="{{ old('rental_time_hour', '09') }}"
                        readonly
                        class="w-full h-12 text-center text-xl font-semibold border border-gray-300 rounded-md bg-white cursor-default"
                    />
                    <button
                        type="button"
                        onclick="adjustTime('rental', 'hour', -1)"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        ↓
                    </button>
                    <span class="text-gray-600 font-medium text-sm select-none mt-1">{{ __('messages.hour') }}</span>
                </div>

                <span class="text-gray-500 font-extrabold text-4xl select-none">:</span>

                <!-- Minutes -->
                <div class="flex flex-col items-center space-y-2 w-20">
                    <button
                        type="button"
                        onclick="adjustTime('rental', 'minute', 5)"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        ↑
                    </button>
                    <input
                        type="text"
                        name="rental_time_minute"
                        id="rental_time_minute"
                        value="{{ old('rental_time_minute', '00') }}"
                        readonly
                        class="w-full h-12 text-center text-xl font-semibold border border-gray-300 rounded-md bg-white cursor-default"
                    />
                    <button
                        type="button"
                        onclick="adjustTime('rental', 'minute', -5)"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        ↓
                    </button>
                    <span class="text-gray-600 font-medium text-sm select-none mt-1">{{ __('messages.minute') }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">{{ __('messages.pickup_hours_6_20') }}</p>
        </div>

        <!-- Return Time -->
        <div class="border border-gray-300 rounded-md p-4">
                    <span class="block text-gray-900 font-semibold mb-4 text-lg">
                      {{ __('messages.return_time') }} <span class="text-red-600">*</span>
                    </span>
            <div class="flex items-center space-x-4 justify-center">
                <!-- Hours -->
                <div class="flex flex-col items-center space-y-2 w-20">
                    <button
                        type="button"
                        onclick="adjustTime('return', 'hour', 1)"
                        aria-label="Increase return hour"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition pointer-events-auto"
                    >
                        ↑
                    </button>
                    <input
                        type="text"
                        name="return_time_hour"
                        id="return_time_hour"
                        value="{{ old('return_time_hour', '10') }}"
                        required
                        readonly
                        class="w-full h-12 text-center text-xl font-semibold border border-gray-300 rounded-md bg-white cursor-default"
                    />
                    <button
                        type="button"
                        onclick="adjustTime('return', 'hour', -1)"
                        aria-label="Decrease return hour"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition pointer-events-auto"
                    >
                        ↓
                    </button>
                    <span class="text-gray-600 font-medium text-sm select-none mt-1">{{ __('messages.hour') }}</span>
                </div>

                <span class="text-gray-500 font-extrabold text-4xl select-none">:</span>

                <!-- Minutes -->
                <div class="flex flex-col items-center space-y-2 w-20">
                    <button
                        type="button"
                        onclick="adjustTime('return', 'minute', 5)"
                        aria-label="Increase return minute"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition pointer-events-auto"
                    >
                        ↑
                    </button>
                    <input
                        type="text"
                        name="return_time_minute"
                        id="return_time_minute"
                        value="{{ old('return_time_minute', '00') }}"
                        required
                        readonly
                        class="w-full h-12 text-center text-xl font-semibold border border-gray-300 rounded-md bg-white cursor-default"
                    />
                    <button
                        type="button"
                        onclick="adjustTime('return', 'minute', -5)"
                        aria-label="Decrease return minute"
                        class="w-full h-10 rounded-md border border-gray-400 flex items-center justify-center hover:bg-gray-100 active:bg-gray-200 transition pointer-events-auto"
                    >
                        ↓
                    </button>
                    <span class="text-gray-600 font-medium text-sm select-none mt-1">{{ __('messages.minute') }}</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">{{ __('messages.return_hours_6_20') }}</p>
        </div>
    </div>

    {{-- Additional Insurance Section --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('messages.additional_insurance') }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ __('messages.additional_insurance_text') }}</p>

        <div class="flex items-center">
            <input type="checkbox"
                   name="additional_insurance"
                   id="additional_insurance"
                   value="1"
                   {{ old('additional_insurance') ? 'checked' : '' }}
                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                   onchange="calculateCosts()">
            <label for="additional_insurance" class="ml-2 text-sm font-medium text-gray-900">
                {{ __('messages.add_additional_insurance') }}
                <span class="text-blue-600">(+15 €/{{ __('messages.day') }})</span>
            </label>
        </div>
    </div>

    {{-- Delivery Options --}}
    <div class="border border-gray-300 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.delivery_options') }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ __('messages.select_delivery_option') }}</p>

        <div class="space-y-3">
            <div class="flex items-center">
                <input type="radio" id="pickup" name="delivery_option" value="pickup"
                       class="mr-3" {{ old('delivery_option', 'pickup') == 'pickup' ? 'checked' : '' }}
                       onchange="toggleDeliveryAddress(); calculateCosts();">
                <label for="pickup" class="text-gray-700">
                    {{ __('messages.pickup_at_office') }} - {{ __('messages.free') }}
                </label>
            </div>

            <div class="flex items-center">
                <input type="radio" id="airport_delivery" name="delivery_option" value="airport"
                       class="mr-3" {{ old('delivery_option') == 'airport' ? 'checked' : '' }}
                       onchange="toggleDeliveryAddress(); calculateCosts();">
                <label for="airport_delivery" class="text-gray-700">
                    {{ __('messages.airport_pickup') }} - <span class="font-semibold">50 €</span>
                </label>
            </div>

            <div class="flex items-center">
                <input type="radio" id="delivery_service" name="delivery_option" value="delivery"
                       class="mr-3" {{ old('delivery_option') == 'delivery' ? 'checked' : '' }}
                       onchange="toggleDeliveryAddress(); calculateCosts();">
                <label for="delivery_service" class="text-gray-700">
                    {{ __('messages.delivery_service') }} - <span class="font-semibold">75 €</span>
                </label>
            </div>
        </div>

        {{-- Delivery Address Field --}}
        <div id="delivery-address-field" class="mt-4 {{ old('delivery_option') == 'delivery' ? '' : 'hidden' }}">
            <label for="delivery_address" class="block font-medium text-gray-700 mb-2">
                {{ __('messages.delivery_address') }} <span class="text-red-500">*</span>
            </label>
            <textarea
                id="delivery_address"
                name="delivery_address"
                rows="3"
                placeholder="{{ __('messages.delivery_address_placeholder') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >{{ old('delivery_address') }}</textarea>
            <p class="text-sm text-gray-500 mt-1">{{ __('messages.delivery_address_help') }}</p>
        </div>
    </div>

    {{-- Cost Summary --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ __('messages.cost_summary') }}</h3>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
                <span>{{ __('messages.rental_cost') }} (<span
                        id="rental-days">1</span> {{ __('messages.days') }}):</span>
                <span id="rental-cost">0 €</span>
            </div>

            <div class="flex justify-between text-gray-600" id="insurance-cost-row" style="display: none;">
                <span>{{ __('messages.additional_insurance') }}:</span>
                <span id="insurance-cost">0 €</span>
            </div>

            <div class="flex justify-between text-gray-600" id="delivery-cost-row" style="display: none;">
                <span>{{ __('messages.delivery_cost') }}:</span>
                <span id="delivery-cost">0 €</span>
            </div>

            <div class="flex justify-between font-medium text-gray-700">
                <span>{{ __('messages.total_rental_amount') }}:</span>
                <span id="total-rental-amount">0 €</span>
            </div>

            <hr class="my-3">

            <div class="bg-blue-50 p-3 rounded-md mb-3">
                <p class="text-xs text-blue-700 mb-2">{{ __('messages.reservation_model_explanation') }}</p>
            </div>

            <div class="flex justify-between">
                <span>{{ __('messages.reservation_fee') }}:</span>
                <span class="text-red-600 font-medium">5 €</span>
            </div>

            <div class="flex justify-between font-bold text-lg border-t pt-2">
                <span>{{ __('messages.total_to_pay_now') }}:</span>
                <span class="text-green-600" id="total-amount">5 €</span>
            </div>
        </div>

        <div class="mt-3 p-2 bg-yellow-100 rounded text-xs text-yellow-800">
            <strong>{{ __('messages.deposit_note') }}:</strong>
            1000-3000 € {{ __('messages.deposit_card_block_info') }}
        </div>
    </div>

    <label class="block">
        <span class="font-medium text-gray-700">{{ __('messages.additional_info') }}</span>
        <textarea name="additional_info" rows="3"
                  class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">{{ old('additional_info') }}</textarea>
    </label>

    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-700 mb-3">{{ __('messages.verification_method') }}</h3>

        <div class="space-y-3">
            <label class="flex items-center space-x-3 opacity-50 cursor-not-allowed">
                <input type="radio" name="verification_method" value="sms" disabled class="form-radio text-gray-400">
                <span class="text-gray-500">
                            {{ __('messages.verify_via_sms') }}
                            <span class="text-xs text-red-500 ml-1">({{ __('messages.disable') }})</span>
                        </span>
            </label>

            <label class="flex items-center space-x-3">
                <input type="radio" name="verification_method" value="email" checked class="form-radio text-blue-600">
                <span class="text-gray-700">{{ __('messages.verify_via_email') }}</span>
            </label>
        </div>
    </div>

    <div class="mb-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-700">{{ __('messages.terms_and_conditions') }}</h3>

        <div class="flex items-start space-x-3">
            <input type="checkbox" id="acceptance_terms" name="acceptance_terms" value="1" required
                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="acceptance_terms" class="text-gray-700 text-sm">
                {{ __('messages.i_accept_terms') }} <a href="#"
                                                       class="text-blue-600 hover:underline">{{ __('messages.terms_of_service') }}</a>
                <span class="text-red-500">*</span>
            </label>
        </div>

        <div class="flex items-start space-x-3">
            <input type="checkbox" id="acceptance_privacy" name="acceptance_privacy" value="1" required
                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="acceptance_privacy" class="text-gray-700 text-sm">
                {{ __('messages.i_accept_privacy_policy') }} <a href="#"
                                                                class="text-blue-600 hover:underline">{{ __('messages.privacy_policy') }}</a>
                <span class="text-red-500">*</span>
            </label>
        </div>
    </div>

    <button type="submit"
            class="w-full bg-[#e3171e] text-white py-3 px-6 rounded hover:bg-red-700 transition duration-200 font-medium">
        {{ __('messages.submit_reservation') }} - <span id="reservation-button-text">5 €</span>
    </button>
</form>
