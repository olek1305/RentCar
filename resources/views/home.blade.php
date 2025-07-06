<x-layout>
    <x-slot:title>{{ __('messages.welcome') }}</x-slot:title>

    <section class="container mx-auto p-4">
        <section class="relative">
            <div style="background-image: url('{{ asset('images/Theme.webp') }}');"
                 class="bg-cover bg-center h-96 w-full">
                <div class="text-5xl p-4 text-center">
                    <p class="mb-8 text-black">{{ __('messages.subtitle') }}</p>


                    ITS HOMEPAGE!
                </div>
            </div>
        </section>
</x-layout>
