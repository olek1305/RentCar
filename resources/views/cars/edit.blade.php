<x-layout>
    <x-slot:title>{{ __('messages.edit_car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">
            {{ __('messages.edit_car') }}
        </h1>

        <form method="POST" action="{{ route('cars.update', $car->id) }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-md space-y-6">
            @csrf
            @method('PUT')

            {{-- Model --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.model') }} <span class="text-red-500">*</span></label>
                <input type="text" name="model" value="{{ old('model', $car->model) }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Type --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.type') }} <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
                    <option value="">{{ __('messages.select_type') }}</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ old('type', $car->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Year --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.year') }} <span class="text-red-500">*</span></label>
                <input type="number" name="year" value="{{ old('year', $car->year) }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Seats --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.seats') }} <span class="text-red-500">*</span></label>
                <input type="number" name="seats" value="{{ old('seats', $car->seats) }}" min="1" max="9" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Fuel Type --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.fuel_type') }} <span class="text-red-500">*</span></label>
                <select name="fuel_type" required
                        class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
                    <option value="">{{ __('messages.select_fuel') }}</option>
                    @foreach($fuelTypes as $fuel)
                        <option value="{{ $fuel }}" {{ old('fuel_type', $car->fuel_type) == $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Engine Capacity --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.engine_capacity') }} <span class="text-red-500">*</span></label>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity', $car->engine_capacity) }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Transmission --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.transmission') }} <span class="text-red-500">*</span></label>
                <select name="transmission" required
                        class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
                    <option value="">{{ __('messages.select_transmission') }}</option>
                    @foreach($transmissions as $transmission)
                        <option value="{{ $transmission }}" {{ old('$transmission', $car->$transmission) == $transmission ? 'selected' : '' }}>{{ $transmission }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Description --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.description') }}</label>
                <textarea name="description"
                          class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2"
                          rows="4">{{ old('description', $car->description) }}</textarea>
            </div>

            {{-- Main Image --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.main_image') }}</label>
                @if($car->main_image)
                    <div class="mb-2 relative">
                        <img src="{{ Storage::url($car->main_image) }}" alt="Current main image"
                             class="h-32 rounded shadow-sm object-cover">
                        <p class="text-sm text-gray-500 mt-1">{{ __('messages.current_image') }}</p>
                        <label class="absolute top-2 right-2 bg-white p-1 rounded-full shadow">
                            <input type="checkbox" name="delete_main_image" value="1" class="sr-only peer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500 peer-checked:block hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 peer-checked:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="sr-only">{{ __('messages.delete_image') }}</span>
                        </label>
                    </div>
                @endif
                <input type="file" name="main_image" accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            {{-- Gallery Images --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">{{ __('messages.gallery_images') }}</label>
                @php
                    $galleryImages = is_array($car->gallery_images)
                        ? $car->gallery_images
                        : json_decode($car->gallery_images, true) ?? [];
                @endphp

                @if(!empty($galleryImages))
                    <div class="grid grid-cols-3 gap-3 mb-2">
                        @foreach($galleryImages as $index => $image)
                            <div class="relative group">
                                <img src="{{ Storage::url($image) }}" alt="Gallery image"
                                     class="h-24 w-full object-cover rounded shadow-sm">

                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{-- Delete button --}}
                                    <label class="cursor-pointer p-1 bg-red-500 rounded-full">
                                        <input type="checkbox" name="delete_gallery_images[]" value="{{ $image }}" class="sr-only peer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white peer-checked:block hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white peer-checked:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </label>

                                    {{-- Set as main button --}}
                                    <button type="button" onclick="setAsMainImage('{{ $image }}')"
                                            class="p-1 bg-blue-500 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-sm text-gray-500 mb-2">{{ __('messages.current_gallery') }}</p>
                @endif
                <input type="file" name="gallery_images[]" multiple accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                {{-- Hidden field for new main image --}}
                <input type="hidden" name="new_main_image" id="new_main_image" value="">
            </div>

            <!-- Rest of the form fields -->

            {{-- Submit --}}
            <div class="pt-4">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                    {{ __('messages.update') }}
                </button>
            </div>
        </form>
    </section>

    <script>
        function setAsMainImage(imagePath) {
            if(confirm('{{ __("messages.confirm_set_main_image") }}')) {
                document.getElementById('new_main_image').value = imagePath;
                // Optional: Show visual feedback
                alert('{{ __("messages.image_will_be_main") }}');
            }
        }
    </script>
</x-layout>
