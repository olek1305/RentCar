<x-layout>
    <x-slot:title>{{ __('messages.welcome') }}</x-slot:title>

    <section class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">{{ __('messages.welcome') }}</h1>
        <p class="text-gray-700 mb-8">{{ __('messages.subtitle') }}</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($cars as $car)
                <div class="border rounded-lg p-4 shadow hover:shadow-lg transition duration-300">
                    <img src="{{ $car->image_url }}" alt="{{ $car->model }}" class="w-full h-48 object-cover rounded mb-4">

                    <h2 class="text-xl font-semibold mb-1">{{ $car->model }} ({{ $car->year }})</h2>

                    <p class="text-green-700 font-bold mb-2">
                        {{ __('messages.from_per_day', ['price' => $car->rental_prices ? json_decode($car->rental_prices)->{"1-2 days"} : 'N/A']) }}
                    </p>

                    <ul class="text-gray-700 text-sm mb-4">
                        <li><strong>{{ __('messages.type') }}:</strong> {{ $car->model }}</li>
                        <li><strong>{{ __('messages.seats') }}:</strong> {{ $car->seats }}</li>
                        <li><strong>{{ __('messages.fuel') }}:</strong> {{ $car->fuel_type }}</li>
                        <li><strong>{{ __('messages.engine') }}:</strong> {{ $car->engine_capacity }} cm³</li>
                        <li><strong>{{ __('messages.transmission') }}:</strong> {{ $car->transmission }}</li>
                    </ul>

                    <div class="flex items-center space-x-4">
                        <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                            {{ __('messages.book') }}
                        </a>

                        <a href="https://wa.me/1234567890" target="_blank" rel="noopener noreferrer" title="{{ __('messages.contact_whatsapp') }}">
                            <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20.52 3.48A11.94 11.94 0 0012 0C5.373 0 0 5.372 0 12a11.963 11.963 0 001.657 6.03L0 24l6.088-1.588A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12 0-3.203-1.243-6.209-3.48-8.52zm-8.53 17.1a8.666 8.666 0 01-4.64-1.35l-.33-.2-3.615.947.96-3.525-.21-.35a8.652 8.652 0 1111.847 4.07l-3.202-1.63zM16.9 13.178c-.27-.13-1.59-.78-1.83-.87-.24-.09-.42-.13-.6.13-.18.27-.7.87-.86 1.05-.16.18-.32.2-.6.07a6.633 6.633 0 01-1.94-1.2 7.29 7.29 0 01-1.34-1.65c-.14-.25-.01-.38.1-.5.1-.1.24-.27.37-.41.13-.14.18-.25.27-.42.09-.18.05-.33-.02-.46-.07-.12-.6-1.44-.82-1.97-.22-.52-.45-.45-.6-.46-.15-.02-.32-.02-.5-.02a1.06 1.06 0 00-.77.36c-.26.27-1 1-1 2.5 0 1.48 1.03 2.92 1.18 3.13.15.2 2.04 3.1 4.95 4.34a18.45 18.45 0 002.34 1.06c.31.13.6.11.82.07.25-.05.77-.31.88-.62.11-.31.11-.58.08-.62-.02-.04-.25-.08-.53-.16z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-layout>
