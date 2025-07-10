<x-layout>
    <x-slot:title>{{ __('messages.welcome') }}</x-slot:title>

    <!-- Hero Section -->
    <section class="relative">
        <div class="h-96 w-full">
            <div class="absolute inset-0 bg-black flex items-center justify-center">
                <div class="text-center px-4">
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">{{ __('messages.hero_title') }}</h1>
                    <a href="{{ route('cars.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300">
                        {{ __('messages.browse_cars') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mx-auto py-12 px-4">
        <h2 class="text-3xl font-bold text-center mb-12">{{ __('messages.why_choose_us') }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                <div class="text-red-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ __('messages.feature1_title') }}</h3>
                <p class="text-gray-600">{{ __('messages.feature1_text') }}</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                <div class="text-red-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ __('messages.feature2_title') }}</h3>
                <p class="text-gray-600">{{ __('messages.feature2_text') }}</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                <div class="text-red-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ __('messages.feature3_title') }}</h3>
                <p class="text-gray-600">{{ __('messages.feature3_text') }}</p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
                <div class="text-red-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ __('messages.feature4_title') }}</h3>
                <p class="text-gray-600">{{ __('messages.feature4_text') }}</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-gray-100 py-16">
        <div class="container mx-auto text-center px-4">
            <h2 class="text-3xl font-bold mb-6">{{ __('messages.ready_to_rent') }}</h2>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">{{ __('messages.cta_text') }}</p>
            <a href="{{ route('cars.index') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300 inline-block">
                {{ __('messages.view_cars') }}
            </a>
        </div>
    </section>
</x-layout>
