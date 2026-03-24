@php
    $popularPostsEnabled = \App\Models\Setting::getValue('popular_posts_enabled', true);
    $shitlistEnabled = \App\Models\Setting::getValue('shitlist_enabled', true);
    $resourcesEnabled = \App\Models\Setting::getValue('resources_enabled', true);

    // Also load any active custom widgets from the widget manager
    $customWidgets = \App\Models\Widget::where('is_active', true)->orderBy('position')->get();
@endphp

<aside class="space-y-6">

    {{-- Popular Posts (manually curated from settings) --}}
    @if($popularPostsEnabled)
        @php
            $popularPostItems = \App\Models\Setting::getValue('popular_posts_items', []);
            if (is_string($popularPostItems)) $popularPostItems = json_decode($popularPostItems, true) ?? [];
        @endphp
        @if(!empty($popularPostItems))
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 font-display mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>
                    </svg>
                    Popular Posts
                </h3>
                <ul class="space-y-3">
                    @foreach ($popularPostItems as $i => $item)
                        <li class="flex items-start gap-3">
                            <span class="shrink-0 w-6 h-6 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold flex items-center justify-center">
                                {{ $i + 1 }}
                            </span>
                            <a href="{{ $item['url'] ?? '#' }}" class="text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors leading-snug">
                                {{ $item['title'] ?? 'Untitled' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    {{-- The Shitlist --}}
    @if($shitlistEnabled)
        @php
            $shitlistItems = \App\Models\Setting::getValue('shitlist_items', []);
        @endphp
        @if(!empty($shitlistItems))
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 font-display mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    The Shitlist
                </h3>
                <ul class="space-y-3">
                    @foreach ($shitlistItems as $item)
                        <li class="border-l-2 border-red-300 dark:border-red-700 pl-3">
                            @if(!empty($item['url']))
                                <a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="text-sm font-semibold text-gray-800 dark:text-gray-200 hover:text-red-600 transition-colors">
                                    {{ $item['name'] }}
                                </a>
                            @else
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $item['name'] }}</span>
                            @endif
                            @if(!empty($item['description']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">{{ $item['description'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    {{-- Resources --}}
    @if($resourcesEnabled)
        @php
            $resourceItems = \App\Models\Setting::getValue('resources_items', []);
        @endphp
        @if(!empty($resourceItems))
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 font-display mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Resources
                </h3>
                <ul class="space-y-2">
                    @foreach ($resourceItems as $item)
                        <li>
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener"
                               class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors py-1">
                                <svg class="w-3.5 h-3.5 shrink-0 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                {{ $item['name'] }}
                            </a>
                            @if(!empty($item['description']))
                                <p class="text-xs text-gray-400 dark:text-gray-500 ml-5.5">{{ $item['description'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    {{-- Custom Widgets (from widget manager) --}}
    @foreach ($customWidgets as $widget)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
            <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 font-display mb-3">{{ $widget->title }}</h3>

            @switch($widget->type)
                @case('html')
                @case('about')
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! $widget->content !!}
                    </div>
                    @break

                @case('recent_posts')
                    @php
                        $count = $widget->settings['count'] ?? 5;
                        $recentPosts = \App\Models\Post::posts()->published()
                            ->orderByDesc('published_at')
                            ->limit($count)
                            ->get(['id', 'title', 'slug', 'published_at', 'post_type']);
                    @endphp
                    <ul class="space-y-2">
                        @foreach ($recentPosts as $post)
                            <li>
                                <a href="{{ $post->url }}" class="text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 transition-colors">
                                    {{ $post->title }}
                                </a>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $post->published_at->format('M j, Y') }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @break

                @case('categories')
                    @php
                        $categories = \App\Models\Category::withCount('posts')->orderBy('name')->get();
                    @endphp
                    <ul class="space-y-1">
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ route('category.show', $category->slug) }}"
                                   class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 transition-colors py-1">
                                    <span>{{ $category->name }}</span>
                                    <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $category->posts_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @break

                @case('tags')
                    @php
                        $tags = \App\Models\Tag::withCount('posts')->orderBy('name')->get();
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            <a href="{{ route('tag.show', $tag->slug) }}"
                               class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-blue-500 hover:text-white transition-colors">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                    @break
            @endswitch
        </div>
    @endforeach
</aside>
