@php use App\Models\Order;use Carbon\Carbon; @endphp
<x-layout>
    <x-slot:title>{{ __('messages.order.title') }} - Admin Panel</x-slot:title>

    <section class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Customer Orders</h1>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Customer
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Car</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Delivery
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
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
                                <span class="text-green-600">✓ Delivery</span>
                            @elseif($order->delivery_option === 'airport')
                                <span class="text-blue-600">✓ Airport</span>
                            @else
                                <span class="text-gray-600">✓ Pickup at office</span>
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
                               class="text-blue-600 hover:text-blue-900 mr-2">Details</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $orders->links() }}
            </div>
        </div>
    </section>
</x-layout>
