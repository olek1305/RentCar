<x-layout>
    <x-slot:title>{{ __('Edit Car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-2xl font-bold mb-6">
            {{ __('Edit Car') }}
        </h1>

        <form method="POST" action="{{ route('cars.update', $car->id) }}">
            @csrf
            @method('PUT')

            <label class="block mb-4">
                <span>Model *</span>
                <input type="text" name="model" value="{{ old('model', $car->model) }}" required class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Year *</span>
                <input type="number" name="year" value="{{ old('year', $car->year) }}" required class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Seats *</span>
                <input type="number" name="seats" value="{{ old('seats', $car->seats) }}" required class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Fuel Type</span>
                <input type="text" name="fuel_type" value="{{ old('fuel_type', $car->fuel_type) }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Engine Capacity</span>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity', $car->engine_capacity) }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Transmission</span>
                <input type="text" name="transmission" value="{{ old('transmission', $car->transmission) }}" class="block w-full rounded border-gray-300">
            </label>

            <label class="block mb-4">
                <span>Description</span>
                <textarea name="description" class="block w-full rounded border-gray-300">{{ old('description', $car->description) }}</textarea>
            </label>

            <label class="block mb-4">
                <span>Image URL</span>
                <input type="url" name="image" value="{{ old('image', $car->image) }}" class="block w-full rounded border-gray-300">
            </label>

            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                {{ __('Update') }}
            </button>
        </form>
    </section>
</x-layout>
