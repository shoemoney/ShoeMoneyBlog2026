<x-layout>
    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Main Content --}}
        <div class="flex-1 min-w-0">
            <article class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                @if($post->featured_image_url)
                    <img src="{{ $post->featuredImage->getUrl('large') }}"
                         alt="{{ $post->title }}"
                         class="w-full max-h-96 object-cover">
                @endif

                <div class="p-6 sm:p-8">
                    <header class="mb-8">
                        @if($post->categories->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($post->categories as $category)
                                    <a href="{{ $category->url }}"
                                       class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400 hover:text-blue-700">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-start justify-between gap-4">
                            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 font-display leading-tight">
                                {{ $post->title }}
                            </h1>
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.posts.edit', $post) }}"
                                       class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg
                                              bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                @endif
                            @endauth
                        </div>

                        <div class="mt-4 flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $post->author->display_name ?? $post->author->name }}</span>
                            <span>&middot;</span>
                            <time datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->format('F j, Y') }}
                            </time>
                            <span>&middot;</span>
                            <span>{{ $post->reading_time }} min read</span>
                        </div>
                    </header>

                    <div class="prose prose-lg prose-slate max-w-none
                                prose-headings:font-display prose-headings:font-bold
                                prose-a:text-blue-600 hover:prose-a:underline
                                prose-img:rounded-lg prose-img:shadow-md
                                dark:prose-invert">
                        {!! $post->rendered_content !!}
                    </div>

                    @if($post->tags->isNotEmpty())
                        <footer class="mt-10 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex flex-wrap gap-2">
                                @foreach($post->tags as $tag)
                                    <a href="{{ $tag->url }}"
                                       class="px-3 py-1 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </footer>
                    @endif
                </div>
            </article>

        </div>

        {{-- Sidebar --}}
        <div class="w-full lg:w-80 shrink-0">
            <x-sidebar-widgets />
        </div>
    </div>
</x-layout>
