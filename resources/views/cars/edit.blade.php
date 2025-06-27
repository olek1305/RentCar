<x-layout>
    <x-slot:title>{{ __('Edit Car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">
            {{ __('Edit Car') }}
        </h1>

        <form method="POST" action="{{ route('cars.update', $car->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <label class="block mb-4">
                <span>Model *</span>
                <input type="text" name="model" value="{{ old('model', $car->model) }}" required
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Year *</span>
                <input type="number" name="year" value="{{ old('year', $car->year) }}" required
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Seats *</span>
                <input type="number" name="seats" value="{{ old('seats', $car->seats) }}" required
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Fuel Type</span>
                <input type="text" name="fuel_type" value="{{ old('fuel_type', $car->fuel_type) }}"
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Engine Capacity</span>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity', $car->engine_capacity) }}"
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Transmission</span>
                <input type="text" name="transmission" value="{{ old('transmission', $car->transmission) }}"
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Description</span>
                <textarea name="description" class="block w-full rounded border-gray-300">
                    {{ old('description', $car->description) }}
                </textarea>
            </label>

            <label class="block mb-4">
                <span>Main Image</span>
                @if($car->main_image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$car->main_image) }}" alt="Current main image" class="h-32">
                        <p class="text-sm text-gray-500 mt-1">Current image</p>
                    </div>
                @endif
                <input type="file" name="main_image" accept="image/*" class="block w-full">
            </label>

            <label class="block mb-4">
                <span>Gallery Images</span>
                @if($car->gallery_images && count($car->gallery_images) > 0)
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        @foreach($car->gallery_images as $image)
                            <div>
                                <img src="{{ asset('storage/'.$image) }}" alt="Gallery image" class="h-24 w-full object-cover">
                            </div>
                        @endforeach
                    </div>
                    <p class="text-sm text-gray-500 mb-2">Current gallery (new images will be added)</p>
                @endif
                <input type="file" name="gallery_images[]" multiple accept="image/*" class="block w-full">
            </label>

            <label class="block mb-4">
                <span>Daily Price (USD) *</span>
                @php
                    $currentDailyPrice = $car->rental_prices['1-2'] ?? '';
                @endphp
                <input type="number" name="daily_price"
                       value="{{ old('daily_price', $currentDailyPrice) }}"
                       required class="block w-full rounded border-gray-300"
                       min="1" step="0.01">
            </label>

            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                {{ __('Update') }}
            </button>
        </form>
    </section>
</x-layout>
