@php use Carbon\Carbon; @endphp
<x-layout>
    <x-slot:title>Order #{{ $order->id }} - Panel Admin</x-slot:title>

    <section class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Zamówienie #{{ $order->id }}</h1>
            <a href="{{ route('admin.orders.index') }}" class="text-blue-600 hover:text-blue-900">← Powrót</a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Dane klienta</h2>
                    <p><strong>Imię i nazwisko:</strong> {{ $order->first_name }} {{ $order->last_name }}</p>
                    <p><strong>Email:</strong> {{ $order->email }}</p>
                    <p><strong>Telefon:</strong> {{ $order->phone }}</p>
                </div>
                <div>
                    <h2 class="text-xl font-semibold mb-4">Szczegóły wynajmu</h2>
                    <p><strong>Samochód:</strong> {{ $order->car->model }} ({{ $order->car->year }})</p>
                    <p><strong>Data wynajmu:</strong> {{ Carbon::parse($order->rental_date)->format('d.m.Y') }}</p>
                    <p><strong>Godziny:</strong>
                        {{ Carbon::parse($order->rental_time)->format('H:i') }} -
                        {{ Carbon::parse($order->return_time)->format('H:i') }}
                    </p>
                    <p><strong>Dostawa:</strong>
                        {{ $order->extra_delivery_fee ? 'Tak (+opłata)' : 'Nie' }}
                    </p>
                    <p><strong>Odbiór na lotnisku:</strong>
                        {{ $order->airport_delivery ? 'Tak' : 'Nie' }}
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-2">Dodatkowe informacje</h2>
                <p class="bg-gray-50 p-4 rounded">{{ $order->additional_info ?? 'Brak dodatkowych informacji' }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden p-6">
            <h2 class="text-xl font-semibold mb-4">Zmień status</h2>
            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="flex items-center space-x-4">
                    <select name="status" class="border rounded px-4 py-2">
                        @foreach(Order::statuses() as $key => $status)
                            <option
                                value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Zapisz
                    </button>
                </div>
            </form>
        </div>
    </section>
</x-layout>
