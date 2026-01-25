<div>
    {{-- Welcome Message --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->display_name }}!</h1>
        <p class="mt-1 text-sm text-gray-500">Here's an overview of your site.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Total Posts Card --}}
        <div class="bg-gray-50 overflow-hidden rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Posts</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ number_format($postCount) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-green-600">
                            <span class="font-medium">{{ number_format($publishedCount) }}</span> published
                        </span>
                        <span class="text-yellow-600">
                            <span class="font-medium">{{ number_format($draftCount) }}</span> drafts
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-100 px-5 py-3">
                <a href="{{ route('admin.posts.index') }}" wire:navigate class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    View all posts &rarr;
                </a>
            </div>
        </div>

        {{-- Pending Comments Card --}}
        <div class="bg-gray-50 overflow-hidden rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 {{ $pendingComments > 0 ? 'text-yellow-500' : 'text-green-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Comments</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ number_format($pendingComments) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <p class="text-sm text-gray-500">
                        @if($pendingComments > 0)
                            Comments awaiting moderation
                        @else
                            No comments need attention
                        @endif
                    </p>
                </div>
            </div>
            <div class="bg-gray-100 px-5 py-3">
                <a href="{{ route('admin.comments.index') }}" wire:navigate class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    Moderate comments &rarr;
                </a>
            </div>
        </div>

        {{-- Total Users Card --}}
        <div class="bg-gray-50 overflow-hidden rounded-lg border border-gray-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ number_format($userCount) }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4 border-t border-gray-200 pt-4">
                    <p class="text-sm text-gray-500">Registered user accounts</p>
                </div>
            </div>
            <div class="bg-gray-100 px-5 py-3">
                <a href="{{ route('admin.users.index') }}" wire:navigate class="text-sm font-medium text-blue-600 hover:text-blue-500">
                    Manage users &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
