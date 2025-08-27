<x-layout>
    <x-slot:title>{{ $car->model }} - {{ __('messages.details') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">{{ $car->model }} ({{ $car->year }})</h1>

        {{-- Image slider --}}
        <div class="mb-8">
            @php
                $gallery = is_array($car->gallery_images)
                    ? $car->gallery_images
                    : json_decode($car->gallery_images, true) ?? [];

                // Create images array and filter out null/empty values
                $images = array_values(array_filter(array_merge(
                    [$car->main_image],
                    is_array($gallery) ? $gallery : []
                )));
            @endphp

            @if(!empty($images))
                {{-- Main Image Display --}}
                <div class="relative w-full h-64 md:h-80 rounded-lg overflow-hidden shadow-lg mb-4 cursor-zoom-in"
                     @if(!empty($images[0])) onclick="openModal('{{ Storage::url($images[0]) }}')" @endif>
                    @if(!empty($images[0]))
                        <img id="main-car-image"
                             src="{{ Storage::url($images[0]) }}"
                             alt="{{ $car->model }}"
                             class="w-full h-full object-cover transition-opacity duration-500">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">{{ __('messages.no_image_available') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Thumbnail Navigation --}}
                @if(count($images) > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ($images as $index => $img)
                            @if(!empty($img))
                                <div
                                    class="thumbnail-container relative h-20 cursor-pointer hover:opacity-80 transition border-2 rounded {{ $index === 0 ? 'border-blue-500' : 'border-transparent' }}"
                                    onclick="manualChangeImage('{{ Storage::url($img) }}', {{ $index }})">
                                    <img src="{{ Storage::url($img) }}"
                                         alt="Thumbnail {{ $index + 1 }}"
                                         class="w-full h-full object-cover">
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Auto-rotate controls --}}
                    <div class="mt-2 flex justify-center space-x-4">
                        <button id="pause-rotation"
                                class="text-gray-600 hover:text-gray-900"
                                onclick="toggleRotation()">
                            <i class="fas fa-pause"></i> {{ __('messages.pause') }}
                        </button>
                        <button id="play-rotation"
                                class="text-gray-600 hover:text-gray-900 hidden"
                                onclick="toggleRotation()">
                            <i class="fas fa-play"></i> {{ __('messages.play') }}
                        </button>
                    </div>
                @endif
            @else
                {{-- No images available --}}
                <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg">
                    <span class="text-gray-500 text-lg">{{ __('messages.no_images_available') }}</span>
                </div>
            @endif
        </div>

        {{-- Specifications --}}
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('messages.specifications') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8 text-center">
            @php
                $specs = [
                    __('messages.registration_from') => $car->year,
                    __('messages.type') => $car->type ?? __('messages.universal'),
                    __('messages.seats') => $car->seats,
                    __('messages.fuel_type') => $car->fuel_type,
                    __('messages.engine_capacity') => $car->engine_capacity ? $car->engine_capacity . ' cm³' : '-',
                    __('messages.transmission') => $car->transmission ?? '-',
                ];
            @endphp

            @foreach ($specs as $label => $value)
                <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                    <strong>{{ $label }}:</strong><br>{{ $value }}
                </div>
            @endforeach
        </div>

        {{-- Description --}}
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('messages.description') }}</h2>
        <p class="mb-8 text-gray-700">
            {{ $car->description ?? __('messages.no_description') }}
        </p>

        {{-- Rental Prices --}}
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

        {{-- Booking Form --}}
        <form method="POST" action="{{ route('orders.store') }}" class="space-y-6 bg-white p-6 rounded-lg shadow-md" id="booking-form">
            @csrf
            <input type="hidden" name="car_id" value="{{ $car->id }}">
            <input type="hidden" name="total_amount" id="total_amount" value="">

            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
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
                    <input type="date" name="rental_date" id="rental_date" required min="{{ date('Y-m-d') }}" value="{{ old('rental_date') }}"
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">{{ __('messages.return_date') }} <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="return_date" id="return_date" required min="{{ date('Y-m-d') }}" value="{{ old('return_date') }}"
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
                                value="{{ old('rental_time_hour') }}"
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
                                value="{{ old('rental_time_minute') }}"
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
                                value="{{ old('return_time_hour') }}"
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
                                value="{{ old('return_time_minute') }}"
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
                        <span>{{ __('messages.rental_cost') }} (<span id="rental-days">1</span> {{ __('messages.days') }}):</span>
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
                        {{ __('messages.i_accept_terms') }} <a href="#" class="text-blue-600 hover:underline">{{ __('messages.terms_of_service') }}</a> <span class="text-red-500">*</span>
                    </label>
                </div>

                <div class="flex items-start space-x-3">
                    <input type="checkbox" id="acceptance_privacy" name="acceptance_privacy" value="1" required
                           class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="acceptance_privacy" class="text-gray-700 text-sm">
                        {{ __('messages.i_accept_privacy_policy') }} <a href="#" class="text-blue-600 hover:underline">{{ __('messages.privacy_policy') }}</a> <span class="text-red-500">*</span>
                    </label>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-[#e3171e] text-white py-3 px-6 rounded hover:bg-red-700 transition duration-200 font-medium">
                {{ __('messages.submit_reservation') }} - <span id="reservation-button-text">5 €</span>
            </button>
        </form>
    </section>

    {{-- Image Modal --}}
    <div id="image-modal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden items-center justify-center">
        <div class="absolute top-4 right-4 z-50">
            <button onclick="closeModal()" class="text-white text-3xl hover:text-gray-300">&times;</button>
        </div>
        <div class="max-w-4xl w-full p-4">
            <img id="modal-image" src="" alt="" class="max-w-full max-h-screen mx-auto">
        </div>
    </div>

    <script>
        // Global variables
        let currentImageIndex = 0;
        let images = @json(array_values(array_filter($images)));
        let rotationInterval;
        let isRotating = images.length > 1;

        // Car pricing data
        const carPrices = {
            '1-2': {{ $price1 }},
            '3-6': {{ $price2 }},
            '7+': {{ $price3 }}
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            initializeForm();
            initializeImageGallery();
        });

        function initializeForm() {
            // Set default times
            const now = new Date();
            const hours = Math.max(6, Math.min(20, now.getHours())).toString().padStart(2, '0');
            const minutes = (Math.round(now.getMinutes() / 5) * 5).toString().padStart(2, '0');

            document.getElementById('rental_time_hour').value = hours;
            document.getElementById('rental_time_minute').value = minutes;

            const returnTime = new Date(now.getTime() + 60 * 60 * 1000);
            document.getElementById('return_time_hour').value = Math.max(6, Math.min(20, returnTime.getHours())).toString().padStart(2, '0');
            document.getElementById('return_time_minute').value = (Math.floor(returnTime.getMinutes() / 5) * 5).toString().padStart(2, '0');

            // Set default dates
            document.getElementById('rental_date').value = new Date().toISOString().split('T')[0];
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('return_date').value = tomorrow.toISOString().split('T')[0];

            // Add event listeners for all inputs that affect pricing
            document.getElementById('rental_date').addEventListener('change', calculateCosts);
            document.getElementById('return_date').addEventListener('change', calculateCosts);
            document.getElementById('additional_insurance').addEventListener('change', calculateCosts);

            // Add event listeners for delivery options
            document.querySelectorAll('input[name="delivery_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    toggleDeliveryAddress();
                    calculateCosts();
                });
            });

            // Initialize delivery address visibility and calculate costs
            toggleDeliveryAddress();
            calculateCosts();
        }

        function initializeImageGallery() {
            if (isRotating && images.length > 1) {
                startRotation();
            }
        }

        // Cost calculation function
        function calculateCosts() {
            const rentalDate = new Date(document.getElementById('rental_date').value);
            const returnDate = new Date(document.getElementById('return_date').value);

            // Validate dates
            if (isNaN(rentalDate) || isNaN(returnDate) || returnDate <= rentalDate) {
                updateCostDisplay(0, 0, 0, 0, 5);
                return;
            }

            // Calculate days
            const diffTime = returnDate - rentalDate;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // Calculate rental cost based on days
            let dailyRate = 0;
            if (diffDays <= 2) {
                dailyRate = carPrices['1-2'];
            } else if (diffDays <= 6) {
                dailyRate = carPrices['3-6'];
            } else {
                dailyRate = carPrices['7+'];
            }
            const rentalCost = dailyRate * diffDays;

            // Calculate insurance cost
            const insuranceSelected = document.getElementById('additional_insurance').checked;
            const insuranceCost = insuranceSelected ? 15 * diffDays : 0;

            // Calculate delivery cost
            const deliveryOption = document.querySelector('input[name="delivery_option"]:checked')?.value || 'pickup';
            let deliveryCost = 0;
            if (deliveryOption === 'airport') {
                deliveryCost = 50;
            } else if (deliveryOption === 'delivery') {
                deliveryCost = 75;
            }

            // In the reservation model, we only charge reservation fee upfront
            // The total rental cost is shown for information but paid on pickup
            const reservationFee = 5;

            updateCostDisplay(diffDays, rentalCost, insuranceCost, deliveryCost, reservationFee);
        }

        function updateCostDisplay(days, rental, insurance, delivery) {
            document.getElementById('rental-days').textContent = days;
            document.getElementById('rental-cost').textContent = rental.toFixed(2) + ' €';
            document.getElementById('insurance-cost').textContent = insurance.toFixed(2) + ' €';
            document.getElementById('delivery-cost').textContent = delivery.toFixed(2) + ' €';

            // Calculate total rental amount (what the customer will pay upon pickup)
            const totalRentalAmount = rental + insurance + delivery;
            document.getElementById('total-rental-amount').textContent = totalRentalAmount.toFixed(2) + ' €';

            // Reservation fee is always fixed at 5€ regardless of options
            const reservationFee = 5;
            document.getElementById('total-amount').textContent = reservationFee.toFixed(2) + ' €';
            document.getElementById('reservation-button-text').textContent = reservationFee.toFixed(2) + ' €';

            // Show/hide cost rows
            document.getElementById('insurance-cost-row').style.display = insurance > 0 ? 'flex' : 'none';
            document.getElementById('delivery-cost-row').style.display = delivery > 0 ? 'flex' : 'none';

            // Set hidden field to reservation fee only
            document.getElementById('total_amount').value = reservationFee.toFixed(2);
        }

        // Delivery address toggle
        function toggleDeliveryAddress() {
            const deliveryService = document.getElementById('delivery_service');
            const deliveryAddressField = document.getElementById('delivery-address-field');
            const deliveryAddressInput = document.getElementById('delivery_address');

            if (deliveryService && deliveryService.checked) {
                deliveryAddressField.classList.remove('hidden');
                deliveryAddressInput.setAttribute('required', 'required');
            } else {
                deliveryAddressField.classList.add('hidden');
                deliveryAddressInput.removeAttribute('required');
                deliveryAddressInput.value = '';
            }
        }

        // Time adjustment functions
        function adjustTime(type, unit, change) {
            const input = document.querySelector(`input[name="${type}_time_${unit}"]`);
            let value = parseInt(input.value) + change;

            if (unit === 'hour') {
                if (value > 20) value = 6;
                if (value < 6) value = 20;
                value = Math.max(6, Math.min(20, value));
            } else { // minute
                if (value >= 60) {
                    value = 0;
                    const hourInput = document.querySelector(`input[name="${type}_time_hour"]`);
                    let hourValue = parseInt(hourInput.value) + 1;
                    if (hourValue > 20) hourValue = 6;
                    hourInput.value = hourValue.toString().padStart(2, '0');
                }
                if (value < 0) {
                    value = 55;
                    const hourInput = document.querySelector(`input[name="${type}_time_hour"]`);
                    let hourValue = parseInt(hourInput.value) - 1;
                    if (hourValue < 6) hourValue = 20;
                    hourInput.value = hourValue.toString().padStart(2, '0');
                }
                value = Math.floor(value / 5) * 5;
            }

            input.value = value.toString().padStart(2, '0');
            calculateCosts();
        }

        // Image gallery functions
        function startRotation() {
            isRotating = true;
            rotationInterval = setInterval(() => {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                changeMainImage(images[currentImageIndex], currentImageIndex);
            }, 3500);
            updateRotationButtons();
        }

        function stopRotation() {
            isRotating = false;
            clearInterval(rotationInterval);
            updateRotationButtons();
        }

        function toggleRotation() {
            if (isRotating) {
                stopRotation();
            } else {
                startRotation();
            }
        }

        function updateRotationButtons() {
            document.getElementById('play-rotation').classList.toggle('hidden', isRotating);
            document.getElementById('pause-rotation').classList.toggle('hidden', !isRotating);
        }

        function manualChangeImage(src, index) {
            stopRotation();
            changeMainImage(src, index);
        }

        function changeMainImage(src, index) {
            document.getElementById('main-car-image').src = "{{ Storage::url('') }}" + src;
            currentImageIndex = index;

            document.querySelectorAll('.thumbnail-container').forEach((container, i) => {
                container.classList.toggle('border-blue-500', i === index);
                container.classList.toggle('border-transparent', i !== index);
            });
        }

        function openModal() {
            if (images.length === 0) return;
            stopRotation();
            const modal = document.getElementById('image-modal');
            document.getElementById('modal-image').src = "{{ Storage::url('') }}" + images[currentImageIndex];
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('image-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (images.length > 1) {
                startRotation();
            }
        }

        // Event listeners
        document.getElementById('image-modal').addEventListener('click', function (e) {
            if (e.target.id === 'image-modal') {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !document.getElementById('image-modal').classList.contains('hidden')) {
                closeModal();
            }
        });

        // Form validation
        document.getElementById('booking-form').addEventListener('submit', function(e) {
            const rentalHour = parseInt(document.getElementById('rental_time_hour').value);
            const returnHour = parseInt(document.getElementById('return_time_hour').value);

            if (rentalHour < 6 || rentalHour > 20) {
                e.preventDefault();
                alert('{{ __('messages.pickup_hours_alert') }}');
                return;
            }

            if (returnHour < 6 || returnHour > 20) {
                e.preventDefault();
                alert('{{ __('messages.return_hours_alert') }}');
                return;
            }

            // Validate delivery address
            const deliveryService = document.getElementById('delivery_service');
            const deliveryAddress = document.getElementById('delivery_address');

            if (deliveryService && deliveryService.checked && (!deliveryAddress.value || deliveryAddress.value.trim() === '')) {
                e.preventDefault();
                alert('{{ __('messages.delivery_address_required') }}');
                deliveryAddress.focus();
                return;
            }
        });
    </script>
</x-layout>
