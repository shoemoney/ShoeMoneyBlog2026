<x-layout>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Latest Posts</h1>

        <div class="divide-y divide-gray-200">
            @forelse($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <p class="py-8 text-gray-500 text-center">No posts found.</p>
            @endforelse
        </div>

        <nav class="mt-8">
            {{ $posts->links() }}
        </nav>
    </div>
</x-layout>
