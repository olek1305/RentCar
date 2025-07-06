<x-layout>
    @if (session('error'))
        <div class="mb-6 p-4 rounded bg-red-100 text-red-800 border border-red-300 container mx-auto text-center">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 p-4 rounded bg-green-100 text-green-800 border border-green-300 container mx-auto text-center">
            {{ session('success') }}
        </div>
    @endif
    <x-slot:title>{{ $car->model }} - {{ __('messages.details') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">{{ $car->model }} ({{ $car->year }})</h1>

        {{-- Image slider --}}
        <div class="relative w-full h-64 rounded overflow-hidden mb-8 shadow-lg">
            @php
                $gallery = is_array($car->gallery_images)
                    ? $car->gallery_images
                    : json_decode($car->gallery_images, true) ?? [];

                $images = array_filter(array_merge([$car->main_image], $gallery));
            @endphp

            @if(count($images) > 0)
                @foreach ($images as $index => $img)
                    <img
                        src="{{ Storage::url($img) }}"
                        alt="Car image {{ $index + 1 }}"
                        class="absolute inset-0 w-full h-64 object-cover rounded transition-opacity duration-700"
                        style="opacity: {{ $index === 0 ? '1' : '0' }};"
                        data-slide-index="{{ $index }}"
                    />
                @endforeach
            @else
                <div class="absolute inset-0 bg-gray-200 flex items-center justify-center rounded">
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
                    __('messages.fuel') => $car->fuel_type,
                    __('messages.engine') => $car->engine_capacity ? $car->engine_capacity . ' cm³' : '-',
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
            <li>1-2 {{ __('messages.days') }}: €{{ number_format($price1, 2) }}</li>
            <li>3-6 {{ __('messages.days') }}: €{{ number_format($price2, 2) }}</li>
            <li>7+ {{ __('messages.days') }}: €{{ number_format($price3, 2) }}</li>
        </ul>

        {{-- Booking Form --}}
        <form method="POST" action="{{ route('orders.store') }}" class="space-y-6 bg-white p-6 rounded-lg shadow-md">
            @csrf
            <input type="hidden" name="car_id" value="{{ $car->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700">{{ __('messages.first_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" required
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">{{ __('messages.last_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" required
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700">{{ __('messages.email') }} <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">{{ __('messages.phone') }} <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" required
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </div>
            </div>

            <label class="block">
                <span class="font-medium text-gray-700">{{ __('messages.rental_date') }} <span class="text-red-500">*</span></span>
                <input type="date" name="rental_date" required min="{{ date('Y-m-d') }}"
                       class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="font-medium text-gray-700">{{ __('messages.rental_time') }} <span class="text-red-500">*</span></span>
                    <input type="time" name="rental_time" required
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </label>

                <label class="block">
                    <span class="font-medium text-gray-700">{{ __('messages.return_time') }} <span class="text-red-500">*</span></span>
                    <input type="time" name="return_time" required
                           class="mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </label>
            </div>

            <div class="space-y-2">
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="extra_delivery_fee" value="1" class="form-checkbox text-blue-600" />
                    <span class="text-gray-700">{{ __('messages.extra_delivery_fee') }}</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="airport_delivery" value="1" class="form-checkbox text-blue-600" />
                    <span class="text-gray-700">{{ __('messages.airport_delivery_included') }}</span>
                </label>
            </div>

            <label class="block">
                <span class="font-medium text-gray-700">{{ __('messages.additional_info') }}</span>
                <textarea name="additional_info" rows="3"
                          class="mt-1 block w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </label>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">
                {{ __('messages.book_now') }}
            </button>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const slides = document.querySelectorAll('[data-slide-index]');
            let current = 0;
            const total = slides.length;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.style.opacity = i === index ? '1' : '0';
                });
            }

            if (total > 1) {
                setInterval(() => {
                    current = (current + 1) % total;
                    showSlide(current);
                }, 3500);
            }
        });
    </script>
</x-layout>
