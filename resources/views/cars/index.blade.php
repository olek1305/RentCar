<x-layout>
    <x-slot:title>{{ __('messages.car_index') }}</x-slot:title>

    <section class="container mx-auto p-4">
        <section class="relative">
            <div style="background-image: url('{{ asset('images/Theme.webp') }}');"
                 class="bg-cover bg-center h-96 w-full">
                <div class="text-5xl p-4 text-center">
                    <p class="mb-8 text-black">{{ __('messages.subtitle') }}</p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pt-4">
            @foreach ($cars as $car)
                @php
                    $gallery = is_array($car->gallery_images)
                        ? $car->gallery_images
                        : json_decode($car->gallery_images, true) ?? [];

                    $images = array_merge([$car->main_image], $gallery);
                    $images = array_filter($images);
                @endphp

                <div class="relative border rounded-lg p-4 shadow hover:shadow-lg transition flex flex-col text-center group car-item
                    @if($car->hidden && auth()->check()) bg-[repeating-linear-gradient(45deg,_#e5e7eb_0px,_#e5e7eb_10px,_#d1d5db_10px,_#d1d5db_20px)] opacity-75 border-dashed border-gray-400 @endif"
                    id="car-{{ $car->id }}">

                    @auth
                        <div class="absolute top-2 right-2 z-30 space-y-1 text-center">
                            <form action="{{ route('cars.toggle-visibility', $car) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="toggle-visibility-button {{ $car->hidden ? 'bg-green-500' : 'bg-red-500' }} text-white px-2 py-1 rounded w-full">
                                    {{ $car->hidden ? 'Show' : 'Hide' }}
                                </button>
                            </form>
                            <a href="{{ route('cars.edit', $car->id) }}"
                               class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded inline-block w-full">
                                Edit
                            </a>
                            <form action="{{ route('cars.destroy', $car->id) }}" method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete(this)"
                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded w-full">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endauth

                    <a href="{{ route('cars.show', $car->id) }}" class="absolute top-0 left-0 right-0 bottom-20 z-10"></a>

                    <div class="relative w-full h-48 rounded overflow-hidden mb-4">

                        @foreach ($images as $index => $img)
                            <img src="{{ $index === 0 ? Storage::url($img) : '' }}"
                                 alt="Car image {{ $index + 1 }}"
                                 class="absolute inset-0 w-full h-48 object-cover rounded transition-opacity duration-500 ease-in-out"
                                 style="opacity: {{ $index === 0 ? '1' : '0' }};"
                                 data-slide-index="{{ $index }}"
                                 data-car-id="{{ $car->id }}"
                                 loading="lazy"
                                 @if($index > 0)
                                     data-src="{{ Storage::url($img) }}"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3C/svg%3E"
                                @endif
                            />
                        @endforeach

                        @if(empty($images))
                            <div class="absolute inset-0 bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-500">No images available</span>
                            </div>
                        @endif
                    </div>

                    <h2 class="text-xl font-semibold mb-1 z-0">{{ $car->model }}</h2>

                    <p class="text-green-700 font-bold mb-4">
                        {{ trans_currency('messages.from_per_day', $car->rental_prices['1-2'] ?? 0) }}
                    </p>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 mb-4 text-gray-700 text-sm">
                        {{-- Left --}}
                        <ul class="space-y-2">
                            <li class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span><strong>{{ __('messages.registration_from') }}:</strong> {{ $car->year }}</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h1l2 6h12l2-6h1" />
                                    <circle cx="7.5" cy="16.5" r="1.5" />
                                    <circle cx="16.5" cy="16.5" r="1.5" />
                                </svg>
                                <span><strong>{{ __('messages.type') }}:</strong> {{ $car->type }}</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span><strong>{{ __('messages.seats') }}:</strong> {{ $car->seats }}</span>
                            </li>
                        </ul>

                        {{-- Right --}}
                        <ul class="space-y-2">
                            <li class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4-4 4 4" />
                                </svg>
                                <span><strong>{{ __('messages.fuel_type') }}:</strong> {{ $car->fuel_type }}</span>
                            </li>
                            <li class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                </svg>
                                <span><strong>{{ __('messages.engine_capacity') }}:</strong> {{ $car->engine_capacity }} cm³</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3" />
                                </svg>
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ __('messages.transmission') }}:</span>
                                    <span class="break-words max-w-[160px]">{{ $car->transmission }}</span>
                                </div>
                            </li>

                        </ul>
                    </div>

                    <div class="flex space-x-4 mt-auto z-0">
                        <a href="{{ route('cars.show', $car->id) }}"
                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded text-center z-20">
                            {{ __('messages.book') }}
                        </a>

                        <a href="https://wa.me/1234567890" target="_blank" rel="noopener noreferrer"
                           class="flex-1 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-2 rounded transition flex items-center justify-center z-20">
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24"></svg>
                            {{ __('messages.contact_whatsapp') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-center mt-8">
            {{ $cars->links() }}
        </div>
    </section>

    <script>
        function saveScrollPosition() {
            sessionStorage.setItem('scrollPosition', window.scrollY);
        }

        function confirmDelete(button) {
            if (confirm('{{ __("messages.confirm_delete_car") }}')) {
                button.closest('form').submit();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const carItems = document.querySelectorAll('.car-item');
            const savedPosition = sessionStorage.getItem('scrollPosition');

            if (savedPosition) {
                window.scrollTo(0, savedPosition);
                sessionStorage.removeItem('scrollPosition');
            }

            // Handling the button click
            document.querySelectorAll('.car-item form button').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            });


            carItems.forEach(carItem => {
                const images = carItem.querySelectorAll('img[data-slide-index]');
                let currentIndex = 0;
                let hoverTimeout;
                let hasLoadedAllImages = false;

                if (images.length > 1) {
                    const loadImage = (img) => {
                        if (img.dataset.src && !img.src.includes('base64')) {
                            img.src = img.dataset.src;
                            return new Promise((resolve) => {
                                img.onload = resolve;
                                img.onerror = resolve; // Continue even if image fails to load
                            });
                        }
                        return Promise.resolve();
                    };

                    const showSlide = async (index) => {
                        const img = images[index];
                        await loadImage(img);

                        images.forEach((img, i) => {
                            img.style.opacity = i === index ? '1' : '0';
                        });
                    };

                    // On hover, load all images first, then cycle through them
                    carItem.addEventListener('mouseenter', async () => {
                        // Load all images first
                        if (!hasLoadedAllImages) {
                            const loadPromises = [];
                            for (let i = 0; i < images.length; i++) {
                                loadPromises.push(loadImage(images[i]));
                            }
                            await Promise.all(loadPromises);
                            hasLoadedAllImages = true;
                        }

                        // Then start cycling
                        let hoverIndex = 0;
                        const cycleImages = async () => {
                            await showSlide(hoverIndex);
                            hoverIndex = (hoverIndex + 1) % images.length;
                            hoverTimeout = setTimeout(cycleImages, 1500);
                        };
                        await cycleImages();
                    });

                    // On mouse leave, reset to first image
                    carItem.addEventListener('mouseleave', () => {
                        clearTimeout(hoverTimeout);
                        showSlide(0);
                        currentIndex = 0;
                    });
                }
            });

            document.querySelectorAll('.toggle-visibility-button').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
            });
        });
    </script>
</x-layout>
