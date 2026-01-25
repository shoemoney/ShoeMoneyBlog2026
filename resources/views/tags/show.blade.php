<x-layout>
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Tag</p>
            <h1 class="text-4xl font-bold text-gray-900">{{ $tag->name }}</h1>
            @if($tag->description)
                <p class="mt-2 text-xl text-gray-600">{{ $tag->description }}</p>
            @endif
        </header>

        <div class="divide-y divide-gray-200">
            @forelse($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <p class="py-8 text-gray-500 text-center">No posts with this tag.</p>
            @endforelse
        </div>

        <nav class="mt-8">
            {{ $posts->links() }}
        </nav>
    </div>
</x-layout>
