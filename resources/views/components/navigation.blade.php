@php
    $menuItems = \App\Models\MenuItem::where('is_active', true)
        ->whereNull('parent_id')
        ->orderBy('position')
        ->get();

    $siteName = \App\Models\Setting::getValue('site_name', 'ShoeMoney');
    $siteTagline = \App\Models\Setting::getValue('site_tagline', 'Making Money Online');
@endphp

<header class="bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 transition-colors">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4 gap-4">
            {{-- Logo and Site Name --}}
            <a href="{{ route('home') }}" class="flex items-center space-x-2 group shrink-0">
                <span class="text-2xl font-bold text-brand-primary group-hover:text-brand-accent transition-colors">
                    {{ $siteName }}
                </span>
            </a>

            {{-- Center Section: Tagline + Search --}}
            <div class="flex items-center gap-4 flex-1 justify-center">
                {{-- Tagline (hidden on small screens) --}}
                <span class="hidden lg:block text-sm text-gray-500 dark:text-slate-400 italic whitespace-nowrap">
                    {{ $siteTagline }}
                </span>

                {{-- Search Bar --}}
                <div class="w-full max-w-xs sm:w-64">
                    <livewire:search.search-bar />
                </div>
            </div>

            {{-- Navigation Links + Theme Toggle --}}
            <nav class="flex items-center gap-6 text-sm font-medium shrink-0">
                @if ($menuItems->isNotEmpty())
                    @foreach ($menuItems as $item)
                        <a href="{{ $item->resolved_url }}"
                           class="text-gray-700 dark:text-slate-300 hover:text-brand-primary transition-colors {{ request()->is(ltrim($item->resolved_url, '/') . '*') ? 'text-brand-primary' : '' }}">
                            {{ $item->label }}
                        </a>
                    @endforeach
                @else
                    {{-- Fallback hardcoded links when no menu items exist --}}
                    <a href="{{ route('home') }}"
                       class="text-gray-700 dark:text-slate-300 hover:text-brand-primary transition-colors {{ request()->routeIs('home') ? 'text-brand-primary' : '' }}">
                        Home
                    </a>
                    <a href="/shoemoney-biography/"
                       class="text-gray-700 dark:text-slate-300 hover:text-brand-primary transition-colors {{ request()->is('shoemoney-biography*') ? 'text-brand-primary' : '' }}">
                        About
                    </a>
                    <a href="/contact-shoemoney/"
                       class="text-gray-700 dark:text-slate-300 hover:text-brand-primary transition-colors {{ request()->is('contact-shoemoney*') ? 'text-brand-primary' : '' }}">
                        Contact
                    </a>
                @endif

                {{-- Theme Toggle --}}
                <x-theme-toggle />
            </nav>
        </div>
    </div>
</header>
