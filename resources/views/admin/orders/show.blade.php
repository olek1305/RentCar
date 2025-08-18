@php
    use Carbon\Carbon;
@endphp
<x-layout>
    <x-slot:title>{{ __('messages.order') }} #{{ $order->id }} - {{ __('messages.admin_panel') }}</x-slot:title>

    <section class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">{{ __('messages.order') }} #{{ $order->id }}</h1>
            <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-900">← {{ __('messages.back') }}</a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">{{ __('messages.customer_data') }}</h2>
                    <p><strong>{{ __('messages.full_name') }}:</strong> {{ $order->first_name }} {{ $order->last_name }}</p>
                    <p><strong>{{ __('messages.email') }}:</strong> {{ $order->email }}</p>
                    <p><strong>{{ __('messages.phone') }}:</strong> {{ $order->phone }}</p>

                    <div class="mt-4">
                        <h3 class="font-semibold">{{ __('messages.verification_status') }}</h3>
                        <p class="text-sm">
                            <strong>{{ __('messages.email_verified') }}:</strong>
                            <span class="{{ $order->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                {{ $order->email_verified_at ? __('messages.yes') : __('messages.no') }}
                                @if($order->email_verified_at)
                                    ({{ $order->email_verified_at->format('d.m.Y H:i') }})
                                @endif
                            </span>
                        </p>
                        <p class="text-sm">
                            <strong>{{ __('messages.sms_verified') }}:</strong>
                            <span class="{{ $order->sms_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                {{ $order->sms_verified_at ? __('messages.yes') : __('messages.no') }}
                                @if($order->sms_verified_at)
                                    ({{ $order->sms_verified_at->format('d.m.Y H:i') }})
                                @endif
                            </span>
                        </p>
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-semibold mb-4">{{ __('messages.rental_details') }}</h2>
                    <p><strong>{{ $order->car->model }} ({{ $order->car->year }})</strong></p>
                    <p><strong>{{ __('messages.rental_date') }}:</strong> {{ Carbon::parse($order->rental_date)->format('d.m.Y') }}</p>
                    <p><strong>{{ __('messages.hours') }}:</strong>
                        {{ Carbon::parse($order->rental_time)->format('H:i') }} -
                        {{ Carbon::parse($order->return_time)->format('H:i') }}
                    </p>
                    <p><strong>{{ __('messages.extra_delivery_fee') }}:</strong>
                        {{ $order->extra_delivery_fee ? __('messages.yes') : __('messages.no') }}
                    </p>
                    <p><strong>{{ __('messages.airport_delivery_included') }}:</strong>
                        {{ $order->airport_delivery ? __('messages.yes') : __('messages.no') }}
                    </p>
                    <p><strong>{{ __('messages.current_status') }}:</strong>
                        <span class="px-2 py-1 rounded text-sm
                            @switch($order->status)
                                @case('pending_verification') bg-yellow-100 text-yellow-800 @break
                                @case('pending') bg-blue-100 text-blue-800 @break
                                @case('confirmed') bg-green-100 text-green-800 @break
                                @case('completed') bg-gray-100 text-gray-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ $statuses[$order->status] ?? $order->status }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-2">{{ __('messages.additional_info') }}</h2>
                <p class="bg-gray-50 p-4 rounded">{{ $order->additional_info ?? __('messages.no_additional_info') }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden p-6">
            <h2 class="text-xl font-semibold mb-4">{{ __('messages.change_status') }}</h2>
            <form action="{{ route('admin.orders.update-status', ['order' => $order->id]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="flex items-center space-x-4">
                    <select name="status" class="border rounded px-4 py-2">
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">{{ __('messages.save') }}</button>
                </div>
            </form>
        </div>
    </section>
</x-layout>
