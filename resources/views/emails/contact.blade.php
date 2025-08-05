<x-layout-email>
    <div class="max-w-2xl mx-auto my-8 bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-500 px-6 py-4">
            <h1 class="text-2xl font-bold text-white">New Contact Message</h1>
            <p class="text-sm text-red-100 mt-1">From your website contact form</p>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Sender Info -->
            <div class="mb-6">
                <div class="flex items-start mb-4">
                    <div class="w-24 flex-shrink-0 font-medium text-gray-600">Name:</div>
                    <div class="text-gray-800">{{ $data['name'] }}</div>
                </div>
                <div class="flex items-start mb-4">
                    <div class="w-24 flex-shrink-0 font-medium text-gray-600">Email:</div>
                    <div class="text-gray-800">
                        <a href="mailto:{{ $data['email'] }}" class="text-blue-600 hover:underline">
                            {{ $data['email'] }}
                        </a>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-24 flex-shrink-0 font-medium text-gray-600">Date:</div>
                    <div class="text-gray-800">{{ now()->format('F j, Y \a\t g:i a') }}</div>
                </div>
            </div>

            <!-- Message -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Message:</h3>
                <div class="bg-gray-50 border-l-4 border-blue-500 px-4 py-3 rounded-r">
                    <p class="text-gray-700 whitespace-pre-line">{{ $data['message'] }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <div>
                    Sent from {{ config('app.name') }}
                </div>
                <div>
                    <a href="{{ url('/admin/contacts') }}" class="text-blue-600 hover:underline">
                        View in dashboard →
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout-email>
