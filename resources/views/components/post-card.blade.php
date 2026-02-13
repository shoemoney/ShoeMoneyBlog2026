@props(['post'])

<article class="border-b border-gray-200 dark:border-gray-700 py-6">
    <a href="{{ $post->url }}" class="group block">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 group-hover:text-brand-primary">
            {{ $post->title }}
        </h2>
    </a>

    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
        <time datetime="{{ $post->published_at->toIso8601String() }}">
            {{ $post->published_at->format('F j, Y') }}
        </time>
        <span>{{ $post->author->display_name ?? $post->author->name }}</span>
        <span>{{ $post->reading_time }} min read</span>
    </div>

    <p class="mt-3 text-gray-600 dark:text-gray-400">
        {{ Str::limit(strip_tags($post->excerpt ?: $post->content), 200) }}
    </p>

    @if($post->categories->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($post->categories as $category)
                <a href="{{ $category->url }}"
                   class="text-xs font-medium text-brand-primary hover:text-brand-accent">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif
</article>
