@props(['post'])

<article class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
    {{-- Large Thumbnail --}}
    <a href="{{ $post->url }}" class="block">
        @if($post->featured_image_url)
            <img src="{{ $post->featuredImage->getUrl('medium') }}"
                 alt="{{ $post->title }}"
                 class="w-full h-48 sm:h-52 object-cover group-hover:scale-[1.02] transition-transform duration-300"
                 loading="lazy">
        @else
            <div class="w-full h-48 sm:h-52 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 flex items-center justify-center">
                <svg class="w-12 h-12 text-blue-200 dark:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
        @endif
    </a>

    {{-- Content --}}
    <div class="p-5">
        {{-- Category --}}
        @if($post->categories->isNotEmpty())
            <div class="mb-2">
                <a href="{{ $post->categories->first()->url }}"
                   class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400 hover:text-blue-700">
                    {{ $post->categories->first()->name }}
                </a>
            </div>
        @endif

        {{-- Title - Large --}}
        <a href="{{ $post->url }}" class="block group/title">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 leading-snug group-hover/title:text-blue-600 transition-colors line-clamp-2">
                {{ $post->title }}
            </h3>
        </a>

        {{-- Excerpt --}}
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-3">
            {{ Str::limit(strip_tags($post->excerpt ?: $post->content), 140) }}
        </p>

        {{-- Meta --}}
        <div class="mt-4 flex items-center text-xs text-gray-400 dark:text-gray-500 space-x-3">
            <time datetime="{{ $post->published_at->toIso8601String() }}">
                {{ $post->published_at->format('M j, Y') }}
            </time>
            <span>&middot;</span>
            <span>{{ $post->reading_time }} min read</span>
        </div>
    </div>
</article>
