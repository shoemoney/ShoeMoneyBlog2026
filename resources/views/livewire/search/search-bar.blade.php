<div
    class="relative"
    x-data="{ activeIndex: -1 }"
    @keydown.arrow-down.prevent="activeIndex = Math.min(activeIndex + 1, {{ count($results) }} - 1)"
    @keydown.arrow-up.prevent="activeIndex = Math.max(activeIndex - 1, 0)"
    @keydown.escape="$wire.showResults = false; activeIndex = -1"
>
    {{-- Search Input --}}
    <div class="relative">
        <input
            type="search"
            wire:model.live.debounce.300ms="query"
            @focus="if($wire.query.length >= 2) $wire.showResults = true"
            @click.away="$wire.showResults = false"
            placeholder="Search posts..."
            class="w-full px-4 py-2 pl-10 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary bg-white dark:bg-gray-800 dark:text-gray-100"
            autocomplete="off"
        >
        {{-- Search Icon --}}
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>

    {{-- Results Dropdown --}}
    @if($showResults)
        <div
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg overflow-hidden"
            x-show="$wire.showResults"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            @forelse($results as $index => $post)
                <a
                    href="{{ $post->url }}"
                    wire:click="selectResult"
                    class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors"
                    :class="{ 'bg-gray-100 dark:bg-gray-700': activeIndex === {{ $index }} }"
                    @keydown.enter.prevent="window.location = '{{ $post->url }}'"
                    @mouseenter="activeIndex = {{ $index }}"
                >
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $post->title }}</div>
                    @if($post->excerpt)
                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ Str::limit(strip_tags($post->excerpt), 80) }}</div>
                    @endif
                </a>
            @empty
                <div class="px-4 py-3 text-gray-500 dark:text-gray-400 text-center text-sm">
                    No posts found for "{{ $query }}"
                </div>
            @endforelse
        </div>
    @endif
</div>
