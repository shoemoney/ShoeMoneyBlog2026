<div>
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Comment Moderation</h1>
        <p class="text-gray-600 mt-1">Manage and moderate user comments</p>
    </div>

    {{-- Status Tabs --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="$set('status', 'pending')"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Pending
                <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-900' }}">
                    {{ number_format($counts['pending']) }}
                </span>
            </button>

            <button wire:click="$set('status', 'approved')"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Approved
                <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium {{ $status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-900' }}">
                    {{ number_format($counts['approved']) }}
                </span>
            </button>

            <button wire:click="$set('status', 'spam')"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $status === 'spam' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Spam
                <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium {{ $status === 'spam' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-900' }}">
                    {{ number_format($counts['spam']) }}
                </span>
            </button>
        </nav>
    </div>

    {{-- Comments List --}}
    @if ($comments->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No {{ $status }} comments</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if ($status === 'pending')
                    All comments have been moderated.
                @elseif ($status === 'approved')
                    No comments have been approved yet.
                @else
                    No spam comments found.
                @endif
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($comments as $comment)
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        {{-- Author Info --}}
                        <div class="flex items-start space-x-3">
                            <img src="{{ $comment->gravatar_url }}"
                                 alt="{{ $comment->author_name }}"
                                 class="h-10 w-10 rounded-full bg-gray-200">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium text-gray-900">{{ $comment->author_name }}</span>
                                    <span class="text-gray-400">|</span>
                                    <span class="text-sm text-gray-500">{{ $comment->author_email }}</span>
                                </div>
                                @if ($comment->post)
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        On:
                                        <a href="{{ route('post.show', [
                                            'year' => $comment->post->published_at->format('Y'),
                                            'month' => $comment->post->published_at->format('m'),
                                            'day' => $comment->post->published_at->format('d'),
                                            'slug' => $comment->post->slug
                                        ]) }}"
                                           class="text-blue-600 hover:underline"
                                           target="_blank">
                                            {{ Str::limit($comment->post->title, 60) }}
                                        </a>
                                    </p>
                                @endif
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $comment->created_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center space-x-2">
                            @if ($status === 'pending')
                                <button wire:click="approve({{ $comment->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve
                                </button>
                                <button wire:click="reject({{ $comment->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    Spam
                                </button>
                            @elseif ($status === 'approved')
                                <button wire:click="reject({{ $comment->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    Move to Spam
                                </button>
                            @else
                                <button wire:click="approve({{ $comment->id }})"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve
                                </button>
                            @endif

                            <button wire:click="delete({{ $comment->id }})"
                                    wire:confirm="Are you sure you want to permanently delete this comment? This cannot be undone."
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>

                    {{-- Comment Content --}}
                    <div class="mt-3 text-gray-700 text-sm" x-data="{ expanded: false }">
                        @if (strlen($comment->content) > 300)
                            <div x-show="!expanded">
                                <p>{!! nl2br(e(Str::limit($comment->content, 300))) !!}</p>
                                <button @click="expanded = true" class="text-blue-600 hover:underline text-xs mt-1">
                                    Show more
                                </button>
                            </div>
                            <div x-show="expanded" x-cloak>
                                <p>{!! nl2br(e($comment->content)) !!}</p>
                                <button @click="expanded = false" class="text-blue-600 hover:underline text-xs mt-1">
                                    Show less
                                </button>
                            </div>
                        @else
                            <p>{!! nl2br(e($comment->content)) !!}</p>
                        @endif
                    </div>

                    @if ($comment->author_url)
                        <div class="mt-2 text-xs text-gray-400">
                            URL: <span class="text-gray-500">{{ $comment->author_url }}</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $comments->links() }}
        </div>
    @endif
</div>
