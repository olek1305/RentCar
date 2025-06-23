<x-layout>
    <x-slot:title>{{ __('messages.rental_conditions') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6">{{ __('messages.rental_conditions') }}</h1>

        <p class="mb-4 text-gray-700">{{ __('messages.welcome_text') }}</p>

        <h2 class="text-xl font-semibold mt-6 mb-2">{{ __('messages.requirements_title') }}</h2>
        <ul class="list-disc list-inside mb-4 text-gray-700">
            @foreach(__('messages.requirements_list') as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">{{ __('messages.payment_title') }}</h2>
        <ul class="list-disc list-inside mb-4 text-gray-700">
            @foreach(__('messages.payment_list') as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">{{ __('messages.return_title') }}</h2>
        <ul class="list-disc list-inside mb-4 text-gray-700">
            @foreach(__('messages.return_list') as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">{{ __('messages.liability_title') }}</h2>
        <ul class="list-disc list-inside mb-4 text-gray-700">
            @foreach(__('messages.liability_list') as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>

        <h2 class="text-xl font-semibold mt-6 mb-2">{{ __('messages.final_title') }}</h2>
        <p class="mb-4 text-gray-700">{{ __('messages.final_text') }}</p>
    </section>
</x-layout>
