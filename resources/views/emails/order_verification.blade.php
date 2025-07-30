<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('Email Verification') }}</h1>

                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">{{ __('Order #') }}{{ $order->id }}</h2>
                    <p class="text-gray-600">{{ __('Please verify your email address to complete your rental order.') }}</p>
                </div>

                <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                    <p class="text-gray-600 mb-4">
                        {{ __('We just need to verify that') }} <strong>{{ $order->email }}</strong>
                        {{ __('is your email address. Click the button below to verify.') }}
                    </p>

                    <div class="text-center">
                        <a href="{{ $verificationUrl }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            {{ __('Verify Email Address') }}
                        </a>
                    </div>

                    <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-yellow-700 text-sm">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ __('This verification link will expire in 24 hours.') }}
                        </p>
                    </div>

                    <p class="text-gray-500 text-sm mt-4">
                        {{ __('If you did not request this email, you can safely ignore it.') }}
                    </p>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-gray-500 text-sm">
                        {{ __('Having trouble with the button? Copy and paste this link into your browser:') }}
                    </p>
                    <p class="text-blue-500 text-sm break-all mt-1">
                        {{ $verificationUrl }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layout>
