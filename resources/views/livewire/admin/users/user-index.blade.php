<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <a href="{{ route('admin.users.create') }}"
           wire:navigate
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New User
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search users by name or email..."
                   class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="overflow-hidden border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Posts
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Joined
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-500">
                                        <span class="text-sm font-medium text-white">{{ strtoupper(substr($user->display_name, 0, 1)) }}</span>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->display_name }}
                                    </div>
                                    @if ($user->author_name && $user->author_name !== $user->name)
                                        <div class="text-xs text-gray-500">{{ $user->name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($user->is_admin)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Admin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    User
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->posts_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at?->format('M j, Y') ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            {{-- Edit --}}
                            <a href="{{ route('admin.users.edit', $user) }}"
                               wire:navigate
                               class="text-blue-600 hover:text-blue-900">
                                Edit
                            </a>

                            {{-- Toggle Admin (not on self) --}}
                            @if ($user->id !== auth()->id())
                                <button type="button"
                                        wire:click="toggleAdmin({{ $user->id }})"
                                        wire:confirm="Are you sure you want to {{ $user->is_admin ? 'remove admin access from' : 'grant admin access to' }} {{ $user->display_name }}?"
                                        class="text-purple-600 hover:text-purple-900">
                                    {{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}
                                </button>
                            @endif

                            {{-- Delete (not on self or other admins) --}}
                            @if ($user->id !== auth()->id() && !$user->is_admin)
                                <button type="button"
                                        wire:click="delete({{ $user->id }})"
                                        wire:confirm="Are you sure you want to delete {{ $user->display_name }}? This action cannot be undone."
                                        class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            @if ($search)
                                No users found matching "{{ $search }}".
                            @else
                                No users found.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>
