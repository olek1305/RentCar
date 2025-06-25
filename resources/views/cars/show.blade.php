<x-layout>
    <x-slot:title>{{ $car->model }} - {{ __('messages.details') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-4">{{ $car->model }} ({{ $car->year }})</h1>
        <div class="relative w-full h-64 rounded overflow-hidden mb-6">
            @php
                $images = array_merge(
                    [$car->image],
                    $car->extra_images ? json_decode($car->extra_images, true) : []
                );
            @endphp

            @foreach ($images as $index => $img)
                <img
                    src="{{ $img }}"
                    alt="Car image {{ $index + 1 }}"
                    class="absolute inset-0 w-full h-64 object-cover rounded transition-opacity duration-1000"
                    style="opacity: {{ $index === 0 ? '1' : '0' }};"
                    data-slide-index="{{ $index }}"
                />
            @endforeach
        </div>

        <h2 class="text-xl font-semibold mb-2">{{ __('messages.specifications') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6 text-center">
            <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                <strong>{{ __('messages.registration_from') }}:</strong><br>
                {{ $car->year }}
            </div>
            <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                <strong>{{ __('messages.type') }}:</strong><br>
                {{ $car->type ?? __('messages.universal') }}
            </div>
            <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                <strong>{{ __('messages.seats') }}:</strong><br>
                {{ $car->seats }}
            </div>
            <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                <strong>{{ __('messages.fuel') }}:</strong><br>
                {{ $car->fuel_type }}
            </div>
            <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                <strong>{{ __('messages.engine') }}:</strong><br>
                {{ $car->engine_capacity }} cm³
            </div>
            <div class="bg-[#e3171e] text-white p-4 rounded shadow">
                <strong>{{ __('messages.transmission') }}:</strong><br>
                {{ $car->transmission }}
            </div>
        </div>


        <h2 class="text-xl font-semibold mb-2">{{ __('messages.description') }}</h2>
        <p class="mb-6 text-gray-700">
            {{ $car->description ?? __('messages.no_description') }}
        </p>

        <h2 class="text-xl font-semibold mb-2">{{ __('messages.rental_prices') }}</h2>
        @php
            $prices = $car->rental_prices ? json_decode($car->rental_prices, true) : [];
        @endphp
        <ul class="mb-6 space-y-1 text-gray-700">
            @foreach ($prices as $range => $price)
                <li>{{ $range }}: €{{ $price }}</li>
            @endforeach
        </ul>

        <form method="POST" action="" class="space-y-4">
            @csrf
            <input type="hidden" name="car_id" value="{{ $car->id }}">

            <label class="block">
                <span>{{ __('messages.rental_date') }} *</span>
                <input type="date" name="rental_date" required class="mt-1 block w-full rounded border-gray-300">
            </label>

            <label class="block">
                <span>{{ __('messages.rental_time') }} *</span>
                <input type="time" name="rental_time" required class="mt-1 block w-full rounded border-gray-300">
            </label>

            <label class="block">
                <span>{{ __('messages.return_time') }} *</span>
                <input type="time" name="return_time" required class="mt-1 block w-full rounded border-gray-300">
            </label>

            <label class="flex items-center space-x-2">
                <input type="checkbox" name="extra_delivery_fee" value="1" />
                <span>{{ __('messages.extra_delivery_fee') }}</span>
            </label>

            <label class="flex items-center space-x-2">
                <input type="checkbox" name="airport_delivery" value="1" />
                <span>{{ __('messages.airport_delivery_included') }}</span>
            </label>

            <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-700 transition">
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
                    slide.style.opacity = (i === index) ? '1' : '0';
                });
            }

            setInterval(() => {
                current = (current + 1) % total;
                showSlide(current);
            }, 3000);
        });
    </script>
</x-layout>
