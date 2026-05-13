<x-layout>
    <x-slot:title>{{ __('messages.contact') }}</x-slot:title>

    <section class="container mx-auto p-6 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800 dark:text-white">
            {{ __('messages.contact') }}
        </h1>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-6">
            <div class="space-y-4">
                <p class="text-gray-700 dark:text-gray-300">
                    {{ __('messages.contact_description') }}
                </p>

                <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                    <li><strong>Email:</strong> contact@carshop.pl</li>
                    <li><strong>{{ __('messages.phone') }}:</strong> +48 123 456 789</li>
                    <li><strong>Instagram:</strong> @RentCar</li>
                </ul>
            </div>

            <!-- Contact form -->
            <div class="border-t dark:border-gray-700 pt-6">
                <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.name') }}</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded p-2 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800">
                    </div>
                    <div>
                        <label for="email" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded p-2 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800">
                    </div>
                    <div>
                        <label for="message" class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.message') }}</label>
                        <textarea id="message" name="message"
                                  class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded p-2 h-32 resize-none focus:ring focus:ring-blue-200 dark:focus:ring-blue-800">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        {{ __('messages.send') }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layout>
