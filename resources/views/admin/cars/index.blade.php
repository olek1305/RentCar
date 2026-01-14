<x-layout>
    <x-slot:title>{{ __('messages.cars') }} - {{ __('messages.admin_panel') }}</x-slot:title>

    <section class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">{{ __('messages.cars') }}</h1>
            <a href="{{ route('cars.create') }}"
               class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 transition-colors">
                + {{ __('messages.add_rental_car') }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.cars.index') }}"
              class="mb-6 bg-white p-6 rounded-lg shadow-md flex flex-col md:flex-row gap-6 items-end">

            <!-- Search Filter -->
            <div class="w-full md:flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.search') }}</label>
                <input id="search" type="text" name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="{{ __('messages.search_car_model') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>

            <!-- Type Filter -->
            <div class="w-full md:w-auto">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.type') }}</label>
                <select id="type" name="type"
                        class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— {{ __('messages.all') }} —</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>
                            {{ __('messages.car_type_'.$type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Visibility Filter -->
            <div class="w-full md:w-auto">
                <label for="hidden" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.visibility') }}</label>
                <select id="hidden" name="hidden"
                        class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="all" @selected(($filters['hidden'] ?? 'all') === 'all')>{{ __('messages.all') }}</option>
                    <option value="visible" @selected(($filters['hidden'] ?? '') === 'visible')>{{ __('messages.visible') }}</option>
                    <option value="hidden" @selected(($filters['hidden'] ?? '') === 'hidden')>{{ __('messages.hidden') }}</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="w-full md:w-auto flex gap-3">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ __('messages.filter') }}
                </button>
                <a href="{{ route('admin.cars.index') }}"
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.image') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.model') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.type') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.year') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.visibility') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($cars as $car)
                    <tr class="{{ $car->hidden ? 'bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">#{{ $car->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $images = is_array($car->images) ? $car->images : json_decode($car->images, true);
                                $mainImage = $images['main'] ?? null;
                            @endphp
                            @if($mainImage)
                                <img src="{{ asset('storage/'.$mainImage) }}" alt="{{ $car->model }}" class="h-12 w-20 object-cover rounded">
                            @else
                                <div class="h-12 w-20 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">
                                    {{ __('messages.no_image') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $car->model }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ __('messages.car_type_'.$car->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $car->year }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($car->hidden)
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    {{ __('messages.hidden') }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    {{ __('messages.visible') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                <a href="{{ route('cars.show', $car) }}"
                                   class="text-blue-600 hover:text-blue-900">{{ __('messages.view') }}</a>
                                <a href="{{ route('cars.edit', $car) }}"
                                   class="text-yellow-600 hover:text-yellow-900">{{ __('messages.edit') }}</a>
                                <form action="{{ route('cars.toggle-visibility', $car) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-purple-600 hover:text-purple-900">
                                        {{ $car->hidden ? __('messages.show') : __('messages.hide') }}
                                    </button>
                                </form>
                                <form action="{{ route('cars.destroy', $car) }}" method="POST" class="inline"
                                      onsubmit="return confirm('{{ __('messages.confirm_delete_car') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">{{ __('messages.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            {{ __('messages.no_cars_found') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $cars->appends(request()->query())->links() }}
            </div>
        </div>
    </section>
</x-layout>
