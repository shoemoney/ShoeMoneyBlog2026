<section class="space-y-8">
    <h2 class="text-2xl font-bold text-gray-900">
        {{ $commentCount }} {{ Str::plural('Comment', $commentCount) }}
    </h2>

    @if($showPendingMessage)
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-yellow-800">
                Your comment is awaiting moderation and will appear once approved.
            </p>
        </div>
    @endif

    {{-- New comment form at top --}}
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Leave a Comment</h3>
        <livewire:comments.comment-form :post="$post" :key="'new-comment'" />
    </div>

    {{-- Comment list --}}
    @if($comments->isNotEmpty())
        <div class="space-y-6">
            @foreach($comments as $comment)
                @include('livewire.comments.comment-item', ['comment' => $comment, 'depth' => 0])
            @endforeach
        </div>
    @else
        <p class="text-gray-500 italic">No comments yet. Be the first to comment!</p>
    @endif
</section>
