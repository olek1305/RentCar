<x-layout>
    <x-slot:title>{{ __('Create Car') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">
            {{ __('Create New Car') }}
        </h1>

        <form method="POST" action="{{ route('cars.store') }}" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-md space-y-6">
            @csrf

            {{-- Model --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Model *</label>
                <input type="text" name="model" value="{{ old('model') }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Type --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Type *</label>
                <select name="type" required
                        class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
                    <option value="">Select type</option>
                    @foreach(['Sedan', 'SUV', 'Hatchback', 'Coupe'] as $type)
                        <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Year --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Year *</label>
                <input type="number" name="year" value="{{ old('year') }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Seats --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Seats *</label>
                <input type="number" name="seats" value="{{ old('seats') }}" min="1" max="9" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Fuel Type --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Fuel Type *</label>
                <select name="fuel_type" required
                        class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
                    <option value="">Select fuel type</option>
                    @foreach(['Gasoline', 'Diesel', 'Hybrid', 'Electric'] as $fuel)
                        <option value="{{ $fuel }}" {{ old('fuel_type') == $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Engine Capacity --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Engine Capacity *</label>
                <input type="number" name="engine_capacity" value="{{ old('engine_capacity') }}" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Transmission --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Transmission *</label>
                <select name="transmission" required
                        class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
                    <option value="">Select transmission</option>
                    <option value="Manual" {{ old('transmission') == 'Manual' ? 'selected' : '' }}>Manual</option>
                    <option value="Automatic" {{ old('transmission') == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                </select>
            </div>

            {{-- Description --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Description</label>
                <textarea name="description"
                          class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2"
                          rows="4">{{ old('description') }}</textarea>
            </div>

            {{-- Main Image --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Main Image *</label>
                <input type="file" name="main_image" accept="image/*" required
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            {{-- Gallery Images --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Gallery Images</label>
                <input type="file" name="gallery_images[]" multiple accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border file:rounded-lg file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            {{-- Daily Price --}}
            <div>
                <label class="block mb-1 font-medium text-gray-700">Daily Price (USD) *</label>
                <input type="number" name="daily_price" value="{{ old('daily_price') }}" min="1" step="0.01" required
                       class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none px-4 py-2">
            </div>

            {{-- Submit --}}
            <div class="pt-4">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                    {{ __('Create') }}
                </button>
            </div>
        </form>
    </section>
</x-layout>
