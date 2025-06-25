<x-layout>
    <x-slot:title>{{ __('messages.welcome') }}</x-slot:title>

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
                <div class="relative border rounded-lg p-4 shadow hover:shadow-lg transition flex flex-col text-center">
                    <a href="{{ route('cars.show', $car->id) }}" class="absolute inset-0 z-10"></a>

                    <img src="{{ $car->image }}" alt="{{ $car->model }}"
                         class="w-full h-48 object-cover rounded mb-4 group-hover:opacity-90 transition z-0" />

                    <h2 class="text-xl font-semibold mb-1 z-0">{{ $car->model }}</h2>

                    <p class="text-green-700 font-bold mb-4">
                        {{ __('messages.from_per_day', ['price' => $car->rental_prices ? json_decode($car->rental_prices)->{"1-2 days"} : 'N/A']) }}
                    </p>

                    <ul class="text-gray-700 text-sm mb-6 grid grid-flow-col grid-rows-3 gap-y-2 gap-x-4">
                        <li class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>{{ __('messages.registration_from') }}:</strong> {{ $car->year }}</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h1l2 6h12l2-6h1" />
                                <circle cx="7.5" cy="16.5" r="1.5" />
                                <circle cx="16.5" cy="16.5" r="1.5" />
                            </svg>
                            <span><strong>{{ __('messages.type') }}:</strong> {{ $car->model }}</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span><strong>{{ __('messages.seats') }}:</strong> {{ $car->seats }}</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 17l4-4 4 4" />
                            </svg>
                            <span><strong>{{ __('messages.fuel') }}:</strong> {{ $car->fuel_type }}</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            </svg>
                            <span><strong>{{ __('messages.engine') }}:</strong> {{ $car->engine_capacity }} cm³</span>
                        </li>
                        <li class="flex items-center space-x-2 col-span-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3" />
                            </svg>
                            <span><strong>{{ __('messages.transmission') }}:</strong> {{ $car->transmission }}</span>
                        </li>
                    </ul>

                    <div class="flex space-x-4 mt-auto z-0">
                        <a href=""
                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded text-center z-20">
                            {{ __('messages.book') }}
                        </a>

                        <a href="https://wa.me/1234567890" target="_blank" rel="noopener noreferrer"
                           class="flex-1 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold py-2 rounded transition flex items-center justify-center z-20">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"></svg>
                            {{ __('messages.contact_whatsapp') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-layout>
