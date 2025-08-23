@php use App\Models\Order;use Carbon\Carbon; @endphp
<x-layout>
    <x-slot:title>{{ __('messages.orders') }} - {{ __('messages.admin_panel') }}</x-slot:title>

    <section class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">{{ __('messages.orders') }}</h1>
        <form method="GET" action="{{ route('admin.orders.index') }}"
              class="mb-6 bg-white p-6 rounded-lg shadow-md flex flex-col md:flex-row gap-6 items-end">

            <!-- Status Filter -->
            <div class="w-full md:w-auto">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.current_status') }}</label>
                <select id="status" name="status"
                        class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— {{ __('messages.all') }} —</option>
                    @php $allStatuses = $statuses ?? Order::statuses(); @endphp
                    @foreach($allStatuses as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? request('status')) === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Email Filter -->
            <div class="w-full md:flex-1">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.email') }}</label>
                <input id="email" type="text" name="email"
                       value="{{ $filters['email'] ?? request('email') }}"
                       placeholder="user@example.com"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>

            <!-- Phone Filter -->
            <div class="w-full md:flex-1">
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.phone') }}</label>
                <input id="phone" type="text" name="phone"
                       value="{{ $filters['phone'] ?? request('phone') }}"
                       placeholder="123456789"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>

            <!-- Buttons -->
            <div class="w-full md:w-auto flex gap-3">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ __('messages.filter') }}
                </button>
                <a href="{{ route('admin.orders.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow-sm hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    {{ __('messages.clear') }}
                </a>
            </div>
        </form>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('messages.customer_data') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.car') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.rental_date') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.hours') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('messages.delivery') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.current_status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">#{{ $order->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $order->first_name }} {{ $order->last_name }}<br>
                            <small class="text-gray-500">{{ $order->email }}</small><br>
                            <small class="text-gray-500">{{ $order->phone }}</small>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $order->car->model }} ({{ $order->car->year }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ Carbon::parse($order->rental_date)->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ Carbon::parse($order->rental_time)->format('H:i') }} -
                            {{ Carbon::parse($order->return_time)->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($order->delivery_option === 'delivery')
                                <span class="text-green-600">✓ {{ __('messages.delivery') }}</span>
                            @elseif($order->delivery_option === 'airport')
                                <span class="text-blue-600">✓ {{ __('messages.airport_pickup') }}</span>
                            @else
                                <span class="text-gray-600">✓ {{ __('messages.pickup_at_office') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($order->status === 'awaiting_payment') bg-orange-100 text-orange-800
                                @elseif($order->status === 'paid') bg-yellow-100 text-yellow-800
                                @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                                @elseif($order->status === 'completed') bg-green-100 text-green-800
                                @elseif($order->status === 'finished') bg-green-100 text-green-600
                                @else bg-red-100 text-red-800 @endif">
                                {{ Order::statuses()[$order->status] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="text-blue-600 hover:text-blue-900 mr-2">{{ __('messages.details') }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
</x-layout>
