<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    {{-- FOUC prevention: Set dark class BEFORE CSS loads --}}
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="preload" href="/fonts/inter-variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/space-grotesk-variable.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/jetbrains-mono-variable.woff2" as="font" type="font/woff2" crossorigin>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo::meta />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {!! \App\Models\Setting::getValue('custom_header_code', '') !!}

    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-body antialiased min-h-screen flex flex-col transition-colors duration-200">
    <x-navigation />

    <main class="flex-1 container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <x-footer />

    {!! \App\Models\Setting::getValue('analytics_code', '') !!}
    {!! \App\Models\Setting::getValue('custom_footer_code', '') !!}

    @livewireScripts
</body>
</html>
