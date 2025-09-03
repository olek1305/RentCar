<div class="mb-8">
    @php
        $gallery = is_array($car->gallery_images)
            ? $car->gallery_images
            : json_decode($car->gallery_images, true) ?? [];

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
        <div class="bg-gray-200 h-64 flex items-center justify-center rounded-lg">
            <span class="text-gray-500 text-lg">{{ __('messages.no_images_available') }}</span>
        </div>
    @endif
</div>
