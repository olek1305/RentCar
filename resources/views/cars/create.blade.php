<x-layout>
    <x-slot:title>{{ __('Create Car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">
            {{ __('Create New Car') }}
        </h1>

        <form method="POST" action="{{ route('cars.store') }}" enctype="multipart/form-data">
            @csrf

            <label class="block mb-4">
                <span>Model *</span>
                <input type="text" name="model" value="{{ old('model') }}" required
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Type *</span>
                <select name="type" required class="block w-full rounded border-gray-300">
                    <option value="">Select type</option>
                    <option value="Sedan" {{ old('type') == 'Sedan' ? 'selected' : '' }}>Sedan</option>
                    <option value="SUV" {{ old('type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                    <option value="Hatchback" {{ old('type') == 'Hatchback' ? 'selected' : '' }}>Hatchback</option>
                    <option value="Coupe" {{ old('type') == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                </select>
            </label>

            <label class="block mb-4">
                <span>Year *</span>
                <input type="number" name="year" value="{{ old('year') }}" required
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Seats *</span>
                <input type="number" name="seats" value="{{ old('seats') }}" required
                       class="block w-full rounded border-gray-300" min="1" max="9">
            </label>

            <label class="block mb-4">
                <span>Fuel Type *</span>
                <select name="fuel_type" required class="block w-full rounded border-gray-300">
                    <option value="">Select fuel type</option>
                    <option value="Gasoline" {{ old('fuel_type') == 'Gasoline' ? 'selected' : '' }}>Gasoline</option>
                    <option value="Diesel" {{ old('fuel_type') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                    <option value="Hybrid" {{ old('fuel_type') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                    <option value="Electric" {{ old('fuel_type') == 'Electric' ? 'selected' : '' }}>Electric</option>
                </select>
            </label>

            <label class="block mb-4">
                <span>Engine Capacity *</span>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity') }}" required
                       class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Transmission *</span>
                <select name="transmission" required class="block w-full rounded border-gray-300">
                    <option value="">Select transmission</option>
                    <option value="Manual" {{ old('transmission') == 'Manual' ? 'selected' : '' }}>Manual</option>
                    <option value="Automatic" {{ old('transmission') == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                </select>
            </label>

            <label class="block mb-4">
                <span>Description</span>
                <textarea name="description" class="block w-full rounded border-gray-300">{{ old('description') }}</textarea>
            </label>

            <label class="block mb-4">
                <span>Main Image *</span>
                <input type="file" name="main_image" accept="image/*" required class="block w-full">
            </label>

            <label class="block mb-4">
                <span>Gallery Images</span>
                <input type="file" name="gallery_images[]" multiple accept="image/*" class="block w-full">
            </label>

            <label class="block mb-4">
                <span>Daily Price (USD) *</span>
                <input type="number" name="daily_price" value="{{ old('daily_price') }}" required
                       class="block w-full rounded border-gray-300" min="1" step="0.01">
            </label>

            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                {{ __('Create') }}
            </button>
        </form>
    </section>
</x-layout>
