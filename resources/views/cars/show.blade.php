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
                $images = array_filter(array_merge([$car->main_image], $gallery));
            @endphp

            @if(count($images) > 0)
                {{-- Main Image Display --}}
                <div class="relative w-full h-64 md:h-80 rounded-lg overflow-hidden shadow-lg mb-4 cursor-zoom-in"
                     onclick="openModal('{{ Storage::url($images[0]) }}')">
                    <img id="main-car-image"
                         src="{{ Storage::url($images[0]) }}"
                         alt="{{ $car->model }}"
                         class="w-full h-full object-cover transition-opacity duration-500">
                </div>

                {{-- Thumbnail Navigation --}}
                <div class="grid grid-cols-4 gap-2">
                    @foreach ($images as $index => $img)
                        <div class="thumbnail-container relative h-20 cursor-pointer hover:opacity-80 transition border-2 rounded {{ $index === 0 ? 'border-blue-500' : 'border-transparent' }}"
                             onclick="manualChangeImage('{{ Storage::url($img) }}', {{ $index }})">
                            <img src="{{ Storage::url($img) }}"
                                 alt="Thumbnail {{ $index + 1 }}"
                                 class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>

                {{-- Auto-rotate controls --}}
                <div class="mt-2 flex justify-center space-x-4">
                    <button id="pause-rotation"
                            class="text-gray-600 hover:text-gray-900 {{ count($images) > 1 ? '' : 'hidden' }}"
                            onclick="toggleRotation()">
                        <i class="fas fa-pause"></i> {{ __('messages.pause') }}
                    </button>
                    <button id="play-rotation"
                            class="text-gray-600 hover:text-gray-900 hidden"
                            onclick="toggleRotation()">
                        <i class="fas fa-play"></i> {{ __('messages.play') }}
                    </button>
                </div>
            @else
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
                    <input type="text" name="rental_time" required
                           class="timepicker mt-1 block w-full rounded border border-gray-300 px-3 py-2">
                </label>

                <label class="block">
                    <span class="font-medium text-gray-700">{{ __('messages.return_time') }} <span class="text-red-500">*</span></span>
                    <input type="text" name="return_time" required
                           class="timepicker mt-1 block w-full rounded border border-gray-300 px-3 py-2">
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

    {{-- Image Modal --}}
    <div id="image-modal" class="fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center">
        <div class="absolute top-4 right-4 z-50">
            <button onclick="closeModal()" class="text-white text-3xl hover:text-gray-300">&times;</button>
        </div>
        <div class="max-w-4xl w-full p-4">
            <img id="modal-image" src="" alt="" class="max-w-full max-h-screen mx-auto">
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        let images = @json($images);
        let rotationInterval;
        let isRotating = images.length > 1;

        if (isRotating) {
            startRotation();
        }

        function startRotation() {
            isRotating = true;
            rotationInterval = setInterval(() => {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                changeMainImage(StorageUrl(images[currentImageIndex]), currentImageIndex);
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
            document.getElementById('main-car-image').src = src;
            currentImageIndex = index; // update the current index

            // Highlight the active thumbnail
            document.querySelectorAll('.thumbnail-container').forEach((container, i) => {
                container.classList.toggle('border-blue-500', i === index);
                container.classList.toggle('border-transparent', i !== index);
            });
        }

        function StorageUrl(path) {
            return "{{ Storage::url('') }}" + path;
        }

        // Fix: use the currently displayed image when opening modal
        function openModal() {
            stopRotation();
            document.getElementById('modal-image').src = StorageUrl(images[currentImageIndex]);
            document.getElementById('image-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('image-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            if (images.length > 1) {
                startRotation();
            }
        }

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
    </script>
</x-layout>
