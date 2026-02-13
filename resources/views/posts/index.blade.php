<x-layout>
    <div>
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-8">Latest Posts</h1>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <p class="py-8 text-gray-500 dark:text-gray-400 text-center">No posts found.</p>
            @endforelse
        </div>

        <nav class="mt-8">
            {{ $posts->links() }}
        </nav>
    </div>
</x-layout>
