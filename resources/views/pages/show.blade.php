<x-layout>
    <article class="max-w-3xl mx-auto">
        {{-- Page Header - Simple title only, no metadata --}}
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">{{ $page->title }}</h1>
        </header>

        {{-- Page Content --}}
        <div class="prose prose-lg prose-slate max-w-none dark:prose-invert">
            {!! $page->rendered_content !!}
        </div>
    </article>
</x-layout>
