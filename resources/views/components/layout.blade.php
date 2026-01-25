<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo::meta />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased min-h-screen flex flex-col">
    <x-navigation />

    <main class="flex-1 container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>
