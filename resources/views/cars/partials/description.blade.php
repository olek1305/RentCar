<h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('messages.description') }}</h2>
<p class="mb-8 text-gray-700">
    {{ $car->description ?? __('messages.no_description') }}
</p>
