<h2 class="text-xl font-semibold mb-4 text-gray-800">{{ __('messages.specifications') }}</h2>
<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8 text-center">
    @php
        $specs = [
            __('messages.registration_from') => $car->year,
            __('messages.type') => $car->type ?? __('messages.universal'),
            __('messages.seats') => $car->seats,
            __('messages.fuel_type') => $car->fuel_type,
            __('messages.engine_capacity') => $car->engine_capacity ? $car->engine_capacity . ' cm³' : '-',
            __('messages.transmission') => $car->transmission ?? '-',
        ];
    @endphp

    @foreach ($specs as $label => $value)
        <div class="bg-[#e3171e] text-white p-4 rounded shadow">
            <strong>{{ $label }}:</strong><br>{{ $value }}
        </div>
    @endforeach
</div>
