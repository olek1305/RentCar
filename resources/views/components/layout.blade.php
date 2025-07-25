<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'RentCar' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-100">
<nav class="bg-gray-900 text-white text-sm font-semibold shadow-md">
    <div class="w-full px-4 py-4 flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-6">
        <a href="https://instagram.com/afdfawerawerawer" target="_blank" class="hover:underline flex items-center gap-1">
            📷 <span>Instagram</span>
        </a>
        <a href="mailto:kontakt@carshop.pl" class="hover:underline flex items-center gap-1">
            ✉️ <span>contact@carshop.pl</span>
        </a>
        <a href="tel:+48123456789" class="hover:underline flex items-center gap-1">
            📞 <span>+48 123 456 789</span>
        </a>
        @auth
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="hover:text-blue-400">{{ __('messages.logout') }} ({{ auth()->user()->name ?? 'Null' }})</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:text-blue-400">{{ __('messages.login') }}</a>
        @endauth
    </div>
</nav>

<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-800">
            RentCar
        </a>

        <!-- Menu -->
        <ul class="flex space-x-8 text-gray-700 font-medium">
            <li><a href="{{ route('home') }}" class="hover:text-blue-600">{{ __('messages.home') }}</a></li>
            <li><a href="{{ route('cars.index') }}" class="hover:text-blue-600">{{ __('messages.cars_rent') }}</a></li>
            <li><a href="{{ route('condition') }}" class="hover:text-blue-600">{{ __('messages.condition') }}</a></li>
            <li><a href="{{ route('contact') }}" class="hover:text-blue-600">{{ __('messages.contact') }}</a></li>
            @auth
                <li><a href="{{ route('admin.index') }}" class="hover:text-blue-600">Panel Admin</a></li>
            @endauth
        </ul>

        <!-- Language switcher -->
        <div class="flex items-center space-x-4 text-gray-700 font-semibold">
            <a href="?lang=en" class="flex items-center gap-1 hover:text-blue-600">
                🇬🇧 <span>EN</span>
            </a>
            <a href="?lang=pl" class="flex items-center gap-1 hover:text-blue-600">
                🇵🇱 <span>PL</span>
            </a>
        </div>
    </div>
</nav>
<main>
    {{-- Global Alert Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
            <div class="flex items-center justify-between">
                <div class="flex-1 text-center">{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                    &times;
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
            <div class="flex items-center justify-between">
                <div class="flex-1 text-center">{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                    &times;
                </button>
            </div>
        </div>
    @endif

    @isset($errors)
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
                <div class="flex items-center justify-between">
                    <ul class="flex-1 text-center">
                        <div class="font-bold">Please fix the following errors:</div>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    @endisset

    {{-- Page Content --}}
    {{ $slot }}
</main>
</body>
</html>
