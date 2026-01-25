@props(['comment', 'depth' => 0])

<div class="comment {{ $depth > 0 ? 'ml-8 border-l-2 border-gray-200 pl-4' : '' }}"
     id="comment-{{ $comment->id }}">
    <div class="flex space-x-4">
        {{-- Gravatar avatar --}}
        <img src="{{ $comment->gravatar_url }}"
             alt="{{ $comment->author_name }}"
             class="w-12 h-12 rounded-full flex-shrink-0"
             loading="lazy">

        <div class="flex-1 min-w-0">
            {{-- Comment header --}}
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="font-semibold text-gray-900">
                    @if($comment->author_url)
                        <a href="{{ $comment->author_url }}"
                           rel="nofollow noopener"
                           target="_blank"
                           class="hover:text-blue-600 hover:underline">
                            {{ $comment->author_name }}
                        </a>
                    @else
                        {{ $comment->author_name }}
                    @endif
                </span>
                <time class="text-sm text-gray-500"
                      datetime="{{ $comment->created_at->toIso8601String() }}">
                    {{ $comment->created_at->diffForHumans() }}
                </time>
            </div>

            {{-- Comment content --}}
            <div class="mt-2 prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($comment->content)) !!}
            </div>

            {{-- Reply button (only show if depth < 3) --}}
            @if($depth < 3)
                <button wire:click="startReply({{ $comment->id }})"
                        class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Reply
                </button>
            @endif

            {{-- Inline reply form when replying to this comment --}}
            @if($this->replyingTo === $comment->id)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-600">
                            Replying to {{ $comment->author_name }}
                        </span>
                        <button wire:click="cancelReply"
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Cancel
                        </button>
                    </div>
                    <livewire:comments.comment-form
                        :post="$post"
                        :parent-id="$comment->id"
                        :key="'reply-' . $comment->id" />
                </div>
            @endif
        </div>
    </div>

    {{-- Nested replies (recursive, max 3 levels) --}}
    @if($comment->replies->isNotEmpty() && $depth < 3)
        <div class="mt-4 space-y-4">
            @foreach($comment->replies as $reply)
                @include('livewire.comments.comment-item', ['comment' => $reply, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
