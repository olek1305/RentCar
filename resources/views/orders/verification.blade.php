<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('Order Verification') }}</h1>

                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h2 class="text-lg font-semibold text-blue-800 mb-2">{{ __('Order #') }}{{ $order->id }}</h2>
                    <p class="text-gray-600">{{ __('Thank you for your order! Please verify your contact information.') }}</p>
                </div>

                @if(!$order->email_verified_at)
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                            <span class="mr-2">📧</span> {{ __('Email Verification') }}
                            @if($order->email_verification_token)
                                <span class="ml-auto text-yellow-600 text-sm">{{ __('Pending') }}</span>
                            @endif
                        </h3>

                        <p class="text-gray-600 mb-4">
                            {{ __('We sent a verification link to') }} <strong>{{ $order->email }}</strong>.
                            {{ __('Please check your inbox and click the verification link.') }}
                        </p>

                        <form action="{{ route('orders.resend', [$order->id, 'email']) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-blue-600 hover:text-blue-800">
                                {{ __('Resend Verification Email') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mb-6 p-4 bg-green-50 rounded-lg">
                        <h3 class="text-lg font-medium text-green-800 flex items-center">
                            <span class="mr-2">✓</span> {{ __('Email Verified') }}
                        </h3>
                        <p class="text-gray-600 mt-1">
                            {{ __('Verified at') }}: {{ $order->email_verified_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                @endif

                @if(!$order->phone_verified_at)
                    <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
                            <span class="mr-2">📱</span> {{ __('Phone Verification') }}
                            @if($order->sms_verification_code)
                                <span class="ml-auto text-yellow-600 text-sm">{{ __('Pending') }}</span>
                            @endif
                        </h3>

                        @if(session('success') && str_contains(session('success'), 'SMS'))
                            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                                {{ session('success') }}
                            </div>
                        @endif

                        <p class="text-gray-600 mb-4">
                            {{ __('Your phone number') }} <strong>{{ $order->phone }}</strong> {{ __('needs verification.') }}
                        </p>

                        <form action="{{ route('orders.verify-sms', $order->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('Verification Code') }}
                                </label>
                                <input type="text" name="code" id="code" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                {{ __('Verify Phone') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mb-6 p-4 bg-green-50 rounded-lg">
                        <h3 class="text-lg font-medium text-green-800 flex items-center">
                            <span class="mr-2">✓</span> {{ __('Phone Verified') }}
                        </h3>
                        <p class="text-gray-600 mt-1">
                            {{ __('Verified at') }}: {{ $order->phone_verified_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                @endif

                @if($order->email_verified_at && $order->phone_verified_at)
                    <div class="p-4 bg-green-100 rounded-lg">
                        <h3 class="text-lg font-medium text-green-800 mb-2">
                            {{ __('Verification Complete!') }}
                        </h3>
                        <p class="text-gray-700">
                            {{ __('Your order is now fully verified. We will contact you soon with further details.') }}
                        </p>
                        <a href="{{ route('home') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            {{ __('Back to Home') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
