@php
    $menuItems = \App\Models\MenuItem::where('is_active', true)
        ->whereNull('parent_id')
        ->orderBy('position')
        ->get();

    $siteName = \App\Models\Setting::getValue('site_name', 'ShoeMoney');
@endphp

<header class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700" x-data="{ mobileOpen: false }">
    <div class="mx-auto px-2 sm:px-4 lg:px-6">
        <div class="flex items-center justify-between h-24">
            {{-- Logo Left --}}
            @php $logoUrl = \App\Models\Setting::getValue('site_logo_url', ''); @endphp
            <a href="{{ route('home') }}" class="shrink-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="object-contain" style="height: 5rem; max-height: 5rem;">
                @else
                    <span class="text-xl font-bold text-gray-900 tracking-tight hover:text-blue-600 transition-colors font-display">
                        {{ $siteName }}
                    </span>
                @endif
            </a>

            {{-- Desktop Navigation Right --}}
            <div class="hidden md:flex items-center space-x-2">
                @if ($menuItems->isNotEmpty())
                    @foreach ($menuItems as $item)
                        <a href="{{ $item->resolved_url }}"
                           class="px-5 py-2.5 text-lg font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg transition-all {{ request()->is(ltrim($item->resolved_url, '/') . '*') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-gray-700' : '' }}">
                            {{ $item->label }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('home') }}"
                       class="px-5 py-2.5 text-lg font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg transition-all {{ request()->routeIs('home') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-gray-700' : '' }}">
                        Home
                    </a>
                    <a href="/shoemoney-biography/"
                       class="px-5 py-2.5 text-lg font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg transition-all {{ request()->is('shoemoney-biography*') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-gray-700' : '' }}">
                        About
                    </a>
                    <a href="/contact-shoemoney/"
                       class="px-5 py-2.5 text-lg font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg transition-all {{ request()->is('contact-shoemoney*') ? 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-gray-700' : '' }}">
                        Contact
                    </a>
                @endif

                {{-- Search --}}
                <div class="ml-3" style="width: 22rem;">
                    <livewire:search.search-bar />
                </div>

                {{-- Theme Toggle --}}
                <x-theme-toggle />
            </div>

            {{-- Mobile hamburger --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="md:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                    :aria-expanded="mobileOpen">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="md:hidden border-t border-gray-100 py-4 space-y-1">

            @if ($menuItems->isNotEmpty())
                @foreach ($menuItems as $item)
                    <a href="{{ $item->resolved_url }}"
                       class="block px-4 py-3 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all {{ request()->is(ltrim($item->resolved_url, '/') . '*') ? 'text-blue-600 bg-blue-50' : '' }}">
                        {{ $item->label }}
                    </a>
                @endforeach
            @else
                <a href="{{ route('home') }}" class="block px-4 py-3 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Home</a>
                <a href="/shoemoney-biography/" class="block px-4 py-3 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">About</a>
                <a href="/contact-shoemoney/" class="block px-4 py-3 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-lg">Contact</a>
            @endif

            <div class="px-4 pt-2">
                <livewire:search.search-bar />
            </div>

            <div class="px-4 pt-2 flex items-center">
                <x-theme-toggle />
                <span class="ml-2 text-sm text-gray-500">Toggle theme</span>
            </div>
        </div>
    </div>
</header>
