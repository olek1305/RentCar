<x-layout>
    <x-slot:title>404 Not Found</x-slot:title>
    <div class="container mx-auto py-12 text-center">
        <h1 class="text-4xl font-bold text-red-600 mb-4">404</h1>
        <p class="text-lg text-gray-700">
            {{ __('messages.error') }}
        </p>
        <a href="{{ route('home') }}" class="text-blue-600 hover:underline mt-4 inline-block">
            {{ __('messages.back_to_home') }}
        </a>
    </div>
</x-layout>
