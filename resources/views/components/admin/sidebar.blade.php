<aside class="w-64 bg-gray-800 text-white flex flex-col min-h-screen">
    {{-- Site Title/Logo --}}
    <div class="p-4 border-b border-gray-700">
        <a href="{{ route('home') }}" class="text-xl font-bold text-white hover:text-gray-300 transition-colors">
            ShoeMoney
        </a>
        <p class="text-xs text-gray-400 mt-1">Admin Panel</p>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 p-4 space-y-1">
        <a href="{{ route('admin.dashboard') }}"
           wire:navigate
           class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                  {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <a href="{{ route('admin.posts.index') }}"
           wire:navigate
           class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                  {{ request()->routeIs('admin.posts.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            Posts
        </a>

        <a href="{{ route('admin.comments.index') }}"
           wire:navigate
           class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                  {{ request()->routeIs('admin.comments.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            Comments
        </a>

        <a href="{{ route('admin.categories.index') }}"
           wire:navigate
           class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                  {{ request()->routeIs('admin.categories.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            Categories
        </a>

        <a href="{{ route('admin.tags.index') }}"
           wire:navigate
           class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                  {{ request()->routeIs('admin.tags.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            Tags
        </a>

        <a href="{{ route('admin.users.index') }}"
           wire:navigate
           class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
                  {{ request()->routeIs('admin.users.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            Users
        </a>
    </nav>

    {{-- User Info & Logout --}}
    <div class="p-4 border-t border-gray-700">
        <div class="flex items-center mb-3">
            <div class="flex-shrink-0">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-600">
                    <span class="text-sm font-medium text-white">{{ substr(auth()->user()->display_name, 0, 1) }}</span>
                </span>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()->display_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>
