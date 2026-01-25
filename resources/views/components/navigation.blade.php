<header class="bg-white border-b border-gray-200">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center justify-between py-4">
            {{-- Logo and Site Name --}}
            <a href="{{ route('home') }}" class="flex items-center space-x-2 group">
                <span class="text-2xl font-bold text-brand-primary group-hover:text-brand-accent transition-colors">
                    ShoeMoney
                </span>
            </a>

            {{-- Tagline (hidden on small screens) --}}
            <span class="hidden md:block text-sm text-gray-500 italic">
                Making Money Online
            </span>

            {{-- Search Bar (hidden on very small screens) --}}
            <div class="hidden sm:block flex-1 max-w-xs mx-4">
                <livewire:search.search-bar />
            </div>

            {{-- Navigation Links --}}
            <nav class="flex items-center space-x-6 text-sm font-medium">
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
