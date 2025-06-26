<x-layout>
    <x-slot:title>{{ isset($car) ? __('Edit Car') : __('Create Car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">
            {{ isset($car) ? __('Edit Car') : __('Create New Car') }}
        </h1>

        <form method="POST" action="{{ isset($car) ? route('cars.update', $car->id) : route('cars.store') }}">
            @csrf
            @if (isset($car))
                @method('POST')
            @endif

            <label class="block mb-4">
                <span>Model *</span>
                <input type="text" name="model" value="{{ old('model', $car->model ?? '') }}" required class="block w-full rounded border-gray-300">
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
                <input type="number" name="year" value="{{ old('year', $car->year ?? '') }}" required class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Seats *</span>
                <input type="number" name="seats" value="{{ old('seats', $car->seats ?? '') }}" required class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Fuel Type</span>
                <input type="text" name="fuel_type" value="{{ old('fuel_type', $car->fuel_type ?? '') }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Engine Capacity</span>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity', $car->engine_capacity ?? '') }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Transmission</span>
                <input type="text" name="transmission" value="{{ old('transmission', $car->transmission ?? '') }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Description</span>
                <textarea name="description" class="block w-full rounded border-gray-300">{{ old('description', $car->description ?? '') }}</textarea>
            </label>

            <label class="block mb-4">
                <span>Image URL</span>
                <input type="url" name="image" value="{{ old('image', $car->image ?? '') }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Daily Price (USD) *</span>
                <input type="number" name="daily_price" value="{{ old('daily_price') }}" required
                       class="block w-full rounded border-gray-300" min="1" step="0.01">
            </label>

            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                {{ isset($car) ? __('Update') : __('Create') }}
            </button>
        </form>
    </section>
</x-layout>
