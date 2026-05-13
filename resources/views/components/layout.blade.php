<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'RentCar' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.14.9/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 dark:bg-gray-900 transition-colors duration-200">
<!-- Top Bar -->
<nav class="bg-gray-900 dark:bg-gray-950 text-white text-sm font-semibold shadow-md">
    <div class="w-full px-4 py-4 flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-6">
        <a href="https://instagram.com/afdfawerawerawer" target="_blank" class="hover:underline flex items-center gap-1">
            <span>Instagram</span>
        </a>
        <a href="mailto:kontakt@carshop.pl" class="hover:underline flex items-center gap-1">
            <span>contact@carshop.pl</span>
        </a>
        <a href="tel:+48123456789" class="hover:underline flex items-center gap-1">
            <span>+48 123 456 789</span>
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

<!-- Main Navigation -->
<nav class="bg-white dark:bg-gray-800 shadow-md" x-data="{ mobileMenuOpen: false }">
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-800 dark:text-white">
                RentCar
            </a>

            <!-- Desktop Menu -->
            <ul class="hidden md:flex space-x-8 text-gray-700 dark:text-gray-200 font-medium">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('messages.home') }}</a></li>
                <li><a href="{{ route('cars.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('messages.cars_rent') }}</a></li>
                <li><a href="{{ route('condition') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('messages.condition') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-blue-600 dark:hover:text-blue-400">{{ __('messages.contact') }}</a></li>
                @auth
                    <li><a href="{{ route('admin.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Panel Admin</a></li>
                @endauth
            </ul>

            <!-- Right side: Language + Dark Mode + Hamburger -->
            <div class="flex items-center gap-4">
                <!-- Language switcher -->
                <div class="hidden sm:flex items-center space-x-2 text-gray-700 dark:text-gray-200 font-semibold text-sm">
                    <a href="?lang=en" class="hover:text-blue-600 dark:hover:text-blue-400 px-2 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-blue-100 dark:bg-blue-900' : '' }}">EN</a>
                    <a href="?lang=pl" class="hover:text-blue-600 dark:hover:text-blue-400 px-2 py-1 rounded {{ app()->getLocale() === 'pl' ? 'bg-blue-100 dark:bg-blue-900' : '' }}">PL</a>
                </div>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition" aria-label="Toggle dark mode">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>

                <!-- Hamburger Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition" aria-label="Toggle menu">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-collapse class="md:hidden mt-4 pb-4 border-t border-gray-200 dark:border-gray-700">
            <ul class="flex flex-col space-y-3 pt-4 text-gray-700 dark:text-gray-200 font-medium">
                <li><a href="{{ route('home') }}" class="block py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('messages.home') }}</a></li>
                <li><a href="{{ route('cars.index') }}" class="block py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('messages.cars_rent') }}</a></li>
                <li><a href="{{ route('condition') }}" class="block py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('messages.condition') }}</a></li>
                <li><a href="{{ route('contact') }}" class="block py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700">{{ __('messages.contact') }}</a></li>
                @auth
                    <li><a href="{{ route('admin.index') }}" class="block py-2 px-3 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Panel Admin</a></li>
                @endauth
            </ul>
            <!-- Mobile Language Switcher -->
            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 px-3">
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.language') }}:</span>
                <a href="?lang=en" class="text-sm font-medium px-3 py-1 rounded {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">EN</a>
                <a href="?lang=pl" class="text-sm font-medium px-3 py-1 rounded {{ app()->getLocale() === 'pl' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">PL</a>
            </div>
        </div>
    </div>
</nav>

<main class="dark:text-gray-100">
    {{-- Global Alert Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 text-green-700 dark:text-green-200">
            <div class="flex items-center justify-between">
                <div class="flex-1 text-center">{{ session('success') }}</div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 dark:text-green-200 hover:text-green-900 dark:hover:text-green-100">
                    &times;
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200">
            <div class="flex items-center justify-between">
                <div class="flex-1 text-center">{{ session('error') }}</div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 dark:text-red-200 hover:text-red-900 dark:hover:text-red-100">
                    &times;
                </button>
            </div>
        </div>
    @endif

    @isset($errors)
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200">
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

<footer class="bg-gray-900 dark:bg-gray-950 text-white py-8 mt-auto">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-gray-400 text-sm">
                © {{ date('Y') }} RentCar. {{ __('messages.all_rights_reserved') }}
            </div>
            <div class="flex items-center gap-6 text-sm">
                <a href="/privacy-policy" class="text-gray-400 hover:text-white transition">
                    {{ __('messages.privacy_policy') }}
                </a>
                <button id="manage-cookies-btn" class="text-gray-400 hover:text-white transition">
                    {{ __('messages.manage_cookies') }}
                </button>
            </div>
        </div>
    </div>
</footer>
<x-cookie-banner />
</body>
</html>
