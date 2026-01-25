<header class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-4 gap-4">
            {{-- Logo and Site Name --}}
            <a href="{{ route('home') }}" class="flex items-center space-x-2 group shrink-0">
                <span class="text-2xl font-bold text-brand-primary group-hover:text-brand-accent transition-colors">
                    ShoeMoney
                </span>
            </a>

            {{-- Center Section: Tagline + Search --}}
            <div class="hidden sm:flex items-center gap-4 flex-1 justify-center">
                {{-- Tagline (hidden on small screens) --}}
                <span class="hidden lg:block text-sm text-gray-500 italic whitespace-nowrap">
                    Making Money Online
                </span>

                {{-- Search Bar --}}
                <div class="w-64">
                    <livewire:search.search-bar />
                </div>
            </div>

            {{-- Navigation Links --}}
            <nav class="flex items-center gap-6 text-sm font-medium shrink-0">
                <a href="{{ route('home') }}"
                   class="text-gray-700 hover:text-brand-primary transition-colors {{ request()->routeIs('home') ? 'text-brand-primary' : '' }}">
                    Home
                </a>
                <a href="/about/"
                   class="text-gray-700 hover:text-brand-primary transition-colors {{ request()->is('about*') ? 'text-brand-primary' : '' }}">
                    About
                </a>
                <a href="/contact/"
                   class="text-gray-700 hover:text-brand-primary transition-colors {{ request()->is('contact*') ? 'text-brand-primary' : '' }}">
                    Contact
                </a>
            </nav>
        </div>
    </div>
</header>
