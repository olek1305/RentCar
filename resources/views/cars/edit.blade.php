<x-layout>
    <x-slot:title>{{ __('Edit Car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">
            {{ __('Edit Car') }}
        </h1>

        <form method="POST" action="{{ route('cars.update', $car->id) }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-md space-y-6">
            @csrf
            @method('PUT')

            {{-- Model --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Model *</label>
                <input type="text" name="model" value="{{ old('model', $car->model) }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Year --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Year *</label>
                <input type="number" name="year" value="{{ old('year', $car->year) }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Seats --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Seats *</label>
                <input type="number" name="seats" value="{{ old('seats', $car->seats) }}" required min="1" max="9"
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Fuel Type --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Fuel Type</label>
                <input type="text" name="fuel_type" value="{{ old('fuel_type', $car->fuel_type) }}"
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Engine Capacity --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Engine Capacity</label>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity', $car->engine_capacity) }}"
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Transmission --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Transmission</label>
                <input type="text" name="transmission" value="{{ old('transmission', $car->transmission) }}"
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Description --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4"
                          class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">{{ old('description', $car->description) }}</textarea>
            </div>

            {{-- Main Image --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Main Image</label>
                @if($car->main_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$car->main_image) }}" alt="Current main image" class="h-32 rounded shadow-sm object-cover">
                        <p class="text-sm text-gray-500 mt-1">Current image</p>
                    </div>
                @endif
                <input type="file" name="main_image" accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            {{-- Gallery Images --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Gallery Images</label>
                @if($car->gallery_images && count($car->gallery_images) > 0)
                    <div class="grid grid-cols-3 gap-3 mb-2">
                        @foreach($car->gallery_images as $image)
                            <div>
                                <img src="{{ asset('storage/'.$image) }}" alt="Gallery image"
                                     class="h-24 w-full object-cover rounded shadow-sm">
                            </div>
                        @endforeach
                    </div>
                    <p class="text-sm text-gray-500 mb-2">Current gallery (new images will be added)</p>
                @endif
                <input type="file" name="gallery_images[]" multiple accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            {{-- Daily Price --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Daily Price (USD) *</label>
                @php
                    $currentDailyPrice = $car->rental_prices['1-2'] ?? '';
                @endphp
                <input type="number" name="daily_price" value="{{ old('daily_price', $currentDailyPrice) }}"
                       required min="1" step="0.01"
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-2">
            </div>

            {{-- Submit --}}
            <div class="pt-4">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                    {{ __('Update') }}
                </button>
            </div>
        </form>
    </section>
</x-layout>
