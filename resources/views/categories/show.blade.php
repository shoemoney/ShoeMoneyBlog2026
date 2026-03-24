<x-layout>
    <div class="flex flex-col lg:flex-row gap-8">
        {{-- Main Content --}}
        <div class="flex-1 min-w-0">
            <header class="mb-6">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Category</p>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 font-display">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="mt-2 text-gray-500 dark:text-gray-400">{{ $category->description }}</p>
                @endif
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($posts as $post)
                    <x-post-card :post="$post" />
                @empty
                    <p class="col-span-3 py-12 text-gray-400 text-center text-lg">No posts in this category.</p>
                @endforelse
            </div>

            <nav class="mt-10">
                {{ $posts->links() }}
            </nav>
        </div>

        {{-- Sidebar --}}
        <div class="w-full lg:w-80 shrink-0">
            <x-sidebar-widgets />
        </div>
    </div>
</x-layout>
