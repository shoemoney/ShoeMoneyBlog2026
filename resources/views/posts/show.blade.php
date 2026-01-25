<x-layout>
    <article class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">{{ $post->title }}</h1>

            <div class="mt-4 flex items-center text-gray-600 space-x-4">
                <span>By {{ $post->author->display_name ?? $post->author->name }}</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->format('F j, Y') }}
                </time>
                <span>{{ $post->reading_time }} min read</span>
            </div>

            @if($post->categories->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($post->categories as $category)
                        <a href="{{ $category->url }}"
                           class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full hover:bg-blue-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        <div class="prose prose-lg prose-slate max-w-none
                    prose-headings:font-bold
                    prose-a:text-blue-600 hover:prose-a:underline
                    prose-img:rounded-lg prose-img:shadow-md
                    dark:prose-invert">
            {!! $post->rendered_content !!}
        </div>

        @if($post->tags->isNotEmpty())
            <footer class="mt-12 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Tags</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                        <a href="{{ $tag->url }}"
                           class="text-sm text-gray-600 hover:text-blue-600">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </footer>
        @endif
    </article>
</x-layout>
