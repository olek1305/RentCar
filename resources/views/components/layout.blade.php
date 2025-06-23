<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $lang ?? app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'CarShop' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
<nav class="bg-gray-900 text-white text-sm font-semibold shadow-md">
    <div class="container px-4 py-4 flex justify-end gap-6">
        <a href="https://instagram.com/afdfawerawerawer" target="_blank" class="hover:underline flex items-center gap-1">
            📷 <span>Instagram</span>
        </a>
        <a href="mailto:kontakt@carshop.pl" class="hover:underline flex items-center gap-1">
            ✉️ <span>contact@carshop.pl</span>
        </a>
        <a href="tel:+48123456789" class="hover:underline flex items-center gap-1">
            📞 <span>+48 123 456 789</span>
        </a>
    </div>
</nav>

<nav class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">

        <!-- Logo -->
        <a href="/" class="text-2xl font-bold text-gray-800">
            🚗 CarShop
        </a>

        <!-- Menu -->
        <ul class="flex space-x-8 text-gray-700 font-medium">
            <li><a href="/" class="hover:text-blue-600">Home</a></li>
            <li><a href="/condition" class="hover:text-blue-600">Condition</a></li>
            <li><a href="/contact" class="hover:text-blue-600">Contact</a></li>
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
    {{ $slot }}
</main>
</body>
</html>
