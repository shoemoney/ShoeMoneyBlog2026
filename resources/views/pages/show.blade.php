<x-layout>
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1 min-w-0">
            <article class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-6 sm:p-8 shadow-sm">
                <header class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 font-display">{{ $page->title }}</h1>
                </header>

                <div class="prose prose-lg prose-slate max-w-none
                            prose-headings:font-display prose-headings:font-bold
                            prose-a:text-blue-600 hover:prose-a:underline
                            dark:prose-invert">
                    {!! $page->rendered_content !!}
                </div>
            </article>
        </div>

        <div class="w-full lg:w-80 shrink-0">
            <x-sidebar-widgets />
        </div>
    </div>
</x-layout>
