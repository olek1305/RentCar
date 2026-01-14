<x-layout>
    <x-slot:title>{{ __('messages.currencies') }} - {{ __('messages.admin_panel') }}</x-slot:title>

    <section class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">{{ __('messages.currencies') }}</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add New Currency Form -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('messages.add_currency') }}</h2>
                <form action="{{ route('admin.currencies.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="currency_code" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.currency_code') }} (np. EUR, USD, PLN)
                        </label>
                        <input type="text" id="currency_code" name="currency_code"
                               maxlength="3" placeholder="EUR"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                               value="{{ old('currency_code') }}" required>
                        @error('currency_code')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.currency_symbol') }} (np. €, $, zł)
                        </label>
                        <input type="text" id="currency_symbol" name="currency_symbol"
                               maxlength="5" placeholder="€"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('currency_symbol') }}" required>
                        @error('currency_symbol')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="currency_name" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('messages.currency_name') }}
                        </label>
                        <input type="text" id="currency_name" name="currency_name"
                               maxlength="100" placeholder="Euro"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               value="{{ old('currency_name') }}" required>
                        @error('currency_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 transition-colors">
                        {{ __('messages.add_currency') }}
                    </button>
                </form>
            </div>

            <!-- Current Default Currency -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('messages.default_currency') }}</h2>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ $defaultCurrency->currency_symbol }} {{ $defaultCurrency->currency_code }}
                            </p>
                            <p class="text-gray-600">{{ $defaultCurrency->currency_name }}</p>
                        </div>
                        <div class="text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    {{ __('messages.default_currency_info') }}
                </p>
            </div>
        </div>

        <!-- Currencies List -->
        <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
            <h2 class="text-xl font-semibold p-6 border-b">{{ __('messages.all_currencies') }}</h2>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.currency_code') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.currency_symbol') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.currency_name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($currencies as $currency)
                    <tr class="{{ $currency->is_default ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap font-mono font-bold">{{ $currency->currency_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-xl">{{ $currency->currency_symbol }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $currency->currency_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($currency->is_default)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    {{ __('messages.default') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                @if(!$currency->is_default)
                                    <form action="{{ route('admin.currencies.set-default', $currency) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">
                                            {{ __('messages.set_default') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.currencies.destroy', $currency) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ __('messages.confirm_delete_currency') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">{{ __('messages.default_currency_protected') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            {{ __('messages.no_currencies_found') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layout>
