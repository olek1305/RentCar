@php
    use Carbon\Carbon;
    use App\Models\Order;
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
                            <strong>{{ __('messages.email') }}:</strong>
                            <span class="{{ $order->email_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                {{ $order->email_verified_at ? __('messages.yes') : __('messages.no') }}
                                @if($order->email_verified_at)
                                    ({{ $order->email_verified_at->format('d.m.Y H:i') }})
                                @endif
                            </span>
                        </p>
                        <p class="text-sm">
                            <strong>{{ __('messages.sms') }}:</strong>
                            <span class="{{ $order->sms_verified_at ? 'text-green-600' : 'text-red-600' }}">
                                {{ $order->sms_verified_at ? __('messages.yes') : __('messages.no') }}
                                @if($order->sms_verified_at)
                                    ({{ $order->sms_verified_at->format('d.m.Y H:i') }})
                                @endif
                            </span>
                        </p>
                    </div>

                    <!-- Payment Information -->
                    <div class="mt-4">
                        <h3 class="font-semibold">{{ __('messages.payment_info') }}</h3>
                        @if($order->payment_link_sent_at)
                            <p class="text-sm">
                                <strong>{{ __('messages.payment_link_sent') }}:</strong>
                                {{ $order->payment_link_sent_at->format('d.m.Y H:i') }}
                            </p>
                        @endif
                        @if($order->paid_at)
                            <p class="text-sm text-green-600">
                                <strong>{{ __('messages.paid_at') }}:</strong>
                                {{ $order->paid_at->format('d.m.Y H:i') }}
                            </p>
                        @endif
                        @if($order->payment_amount)
                            <p class="text-sm">
                                <strong>{{ __('messages.amount') }}:</strong>
                                {{ $currency->currency_symbol }}{{ number_format($order->payment_amount, 2) }} {{ $order->payment_currency }}
                            </p>
                        @endif
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-semibold mb-4">{{ __('messages.rental_details') }}</h2>
                    <p><strong>{{ __('messages.car') }}:</strong> {{ $order->car->model }} ({{ $order->car->year }})</p>
                    <p><strong>{{ __('messages.rental_time') }}:</strong> {{ Carbon::parse($order->rental_date)->format('d.m.Y') }}</p>
                    <p><strong>{{ __('messages.return_time') }}:</strong> {{ Carbon::parse($order->rental_return)->format('d.m.Y') }}</p>
                    <p><strong>{{ __('messages.hours') }}:</strong>
                        {{ Carbon::parse($order->rental_time)->format('H:i') }} -
                        {{ Carbon::parse($order->return_time)->format('H:i') }}
                    </p>
                    <p><strong>{{ __('messages.delivery') }}:</strong>
                        {{ $order->extra_delivery_fee ? __('messages.yes_with_fee') : __('messages.no') }}
                    </p>
                    <p><strong>{{ __('messages.airport_pickup') }}:</strong>
                        {{ $order->airport_delivery ? __('messages.yes') : __('messages.no') }}
                    </p>
                    <p><strong>{{ __('messages.current_status') }}:</strong>
                        <span class="px-2 py-1 rounded text-sm
                            @switch($order->status)
                                @case('awaiting_payment') bg-orange-100 text-orange-800 @break
                                @case('paid') bg-yellow-100 text-yellow-800 @break
                                @case('confirmed') bg-blue-100 text-blue-800 @break
                                @case('completed') bg-green-100 text-green-800 @break
                                @case('finished') bg-green-100 text-green-600 @break
                                @default bg-red-100 text-red-800
                            @endswitch
                        ">
                            {{ Order::statuses()[$order->status] }}
                        </span>
                    </p>

                    @if($order->returned_at)
                        <p class="text-sm text-purple-600 mt-2">
                            <strong>{{ __('messages.returned_at') }}:</strong>
                            {{ $order->returned_at->format('d.m.Y H:i') }}
                        </p>
                    @endif

                    <!-- Total Amount -->
                    <div class="mt-4 p-3 bg-blue-50 rounded">
                        <p class="font-semibold">
                            {{ __('messages.total_amount') }}:
                            <span class="text-blue-600">
                                {{ $currency->currency_symbol }}{{ number_format($order->calculateTotalAmount(), 2) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-2">{{ __('messages.additional_info') }}</h2>
                <p class="bg-gray-50 p-4 rounded">{{ $order->additional_info ?? __('messages.no_additional_info') }}</p>
            </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Status Change -->
            <div class="bg-white shadow rounded-lg overflow-hidden p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('messages.change_status') }}</h2>
                <form action="{{ route('admin.orders.update-status', ['order' => $order->id]) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex items-center space-x-4">
                        <select name="status" class="border rounded px-4 py-2 flex-1">
                            @foreach($statuses as $key => $status)
                                <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg overflow-hidden p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('messages.quick_actions') }}</h2>
                <div class="space-y-2">
                    <!-- Send Payment Link -->
                    @if($order->canSendPaymentLink())
                        <form action="{{ route('admin.orders.send-payment-link', $order->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-2">
                                {{ __('messages.send_payment_link') }}
                            </button>
                        </form>
                    @endif

                    <!-- Renew Email Token -->
                    @if($order->email_verification_sent_at && $order->email_verification_token && $order->status === 'pending')
                        <form action="{{ route('admin.orders.renew-email-token', $order->id) }}" method="POST"
                              class="inline">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 mb-2">
                                {{ __('messages.renew_email_token') }}
                            </button>
                        </form>
                    @endif

                    <!-- Renew SMS Token -->
                    @if($order->sms_verification_sent_at && $order->sms_verification_token && $order->status === 'pending')
                        <form action="{{ route('admin.orders.renew-sms-token', $order->id) }}" method="POST"
                              class="inline">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 mb-2">
                                {{ __('messages.renew_sms_token') }}
                            </button>
                        </form>
                    @endif

                    <!-- Mark as Finished -->
                    @if($order->canBeFinished())
                        <form action="{{ route('admin.orders.mark-finished', $order->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 mb-2"
                                    onclick="return confirm('{{ __('messages.confirm_mark_finished') }}')">
                                {{ __('messages.mark_as_finished') }}
                            </button>
                        </form>
                    @endif

                    <!-- Cancel Order -->
                    @if(!in_array($order->status, ['completed', 'finished', 'cancelled']))
                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                                    onclick="return confirm('{{ __('messages.confirm_cancel_order') }}')">
                                {{ __('messages.cancel_order') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- TODO unknown route --}}
            @if($order->status === 'finished' && ($order->email_verified_at || $order->sms_verified_at))
                <form action="{{ route('admin.orders.send-final-payment-link', $order->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 mb-2">
                        {{ __('messages.send_final_payment_link') }}
                    </button>
                </form>
            @endif
        </div>

        <!-- Order Timeline -->
        <div class="bg-white shadow rounded-lg overflow-hidden p-6">
            <h2 class="text-xl font-semibold mb-4">{{ __('messages.order_timeline') }}</h2>
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-sm">{{ __('messages.order_created') }}: {{ $order->created_at->format('d.m.Y H:i') }}</span>
                </div>

                @if($order->email_verified_at)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm">{{ __('messages.email_verified') }}: {{ $order->email_verified_at->format('d.m.Y H:i') }}</span>
                    </div>
                @endif

                @if($order->sms_verified_at)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm">{{ __('messages.sms_verified') }}: {{ $order->sms_verified_at->format('d.m.Y H:i') }}</span>
                    </div>
                @endif

                @if($order->payment_link_sent_at)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                        <span class="text-sm">{{ __('messages.payment_link_sent') }}: {{ $order->payment_link_sent_at->format('d.m.Y H:i') }}</span>
                    </div>
                @endif

                @if($order->paid_at)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm">{{ __('messages.payment_received') }}: {{ $order->paid_at->format('d.m.Y H:i') }}</span>
                    </div>
                @endif

                @if($order->returned_at)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm">{{ __('messages.car_returned') }}: {{ $order->returned_at->format('d.m.Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layout>
