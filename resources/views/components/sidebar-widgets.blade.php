@php
    $widgets = \App\Models\Widget::where('is_active', true)->orderBy('position')->get();
@endphp

@if ($widgets->isNotEmpty())
    <aside class="space-y-6">
        @foreach ($widgets as $widget)
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-3">{{ $widget->title }}</h3>

                @switch($widget->type)
                    @case('html')
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! $widget->content !!}
                        </div>
                        @break

                    @case('about')
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! $widget->content !!}
                        </div>
                        @break

                    @case('recent_posts')
                        @php
                            $count = $widget->settings['count'] ?? 5;
                            $recentPosts = \App\Models\Post::published()
                                ->orderByDesc('published_at')
                                ->limit($count)
                                ->get(['id', 'title', 'slug', 'published_at']);
                        @endphp
                        <ul class="space-y-2">
                            @foreach ($recentPosts as $post)
                                <li>
                                    <a href="{{ $post->url }}" class="text-sm text-gray-700 dark:text-slate-300 hover:text-brand-primary transition-colors">
                                        {{ $post->title }}
                                    </a>
                                    <div class="text-xs text-gray-400 dark:text-slate-500">
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
                                       class="flex items-center justify-between text-sm text-gray-700 dark:text-slate-300 hover:text-brand-primary transition-colors py-1">
                                        <span>{{ $category->name }}</span>
                                        <span class="text-xs text-gray-400 dark:text-slate-500 bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">{{ $category->posts_count }}</span>
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
                                   class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-slate-300 hover:bg-brand-primary hover:text-white transition-colors">
                                    {{ $tag->name }}
                                    <span class="ml-1 text-gray-400 dark:text-slate-500">({{ $tag->posts_count }})</span>
                                </a>
                            @endforeach
                        </div>
                        @break
                @endswitch
            </div>
        @endforeach
    </aside>
@endif
