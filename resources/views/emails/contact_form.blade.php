<x-layout-email>
    <div class="max-w-2xl mx-auto my-8 bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">{{ __('messages.contact_form_subject') }}</h1>
            <p class="text-sm text-blue-100 mt-1">{{ config('app.name') }}</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Sender Info -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.sender_info') }}</h3>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.name') }}:</span>
                        <span class="text-gray-800">{{ $name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">{{ __('messages.email') }}:</span>
                        <span class="text-gray-800">{{ $email }}</span>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('messages.message') }}</h3>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $messageContent }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 border-t border-gray-200">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('messages.email_rights_reserved') }}</p>
            </div>
        </div>
    </div>
</x-layout-email>
