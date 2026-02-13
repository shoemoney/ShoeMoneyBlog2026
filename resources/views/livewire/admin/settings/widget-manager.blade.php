<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Sidebar Widgets</h1>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Create/Edit Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ $editingId ? 'Edit Widget' : 'Add Widget' }}
        </h2>

        <form wire:submit="{{ $editingId ? 'update' : 'create' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" id="title" wire:model="title"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                        placeholder="Widget title">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="type" wire:model.live="type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="html">Custom HTML</option>
                        <option value="recent_posts">Recent Posts</option>
                        <option value="categories">Categories</option>
                        <option value="tags">Tags</option>
                        <option value="about">About</option>
                    </select>
                </div>
            </div>

            {{-- Type-specific fields --}}
            @if ($type === 'html' || $type === 'about')
                <div class="mb-4">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $type === 'html' ? 'HTML Content' : 'About Text' }}
                    </label>
                    <textarea id="content" wire:model="content" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('content') border-red-500 @enderror"
                        placeholder="{{ $type === 'html' ? 'Enter HTML content...' : 'Write a short about section...' }}"></textarea>
                    @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            @if ($type === 'recent_posts')
                <div class="mb-4">
                    <label for="recent_posts_count" class="block text-sm font-medium text-gray-700 mb-1">Number of Posts</label>
                    <input type="number" id="recent_posts_count" wire:model="recent_posts_count" min="1" max="20"
                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('recent_posts_count') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="flex items-center gap-4 mb-4">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_active"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    {{ $editingId ? 'Update Widget' : 'Add Widget' }}
                </button>

                @if ($editingId)
                    <button type="button" wire:click="cancelEdit"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- Widgets Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($widgets as $widget)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-1">
                                <button wire:click="moveUp({{ $widget->id }})" class="text-gray-400 hover:text-gray-600" title="Move up">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button wire:click="moveDown({{ $widget->id }})" class="text-gray-400 hover:text-gray-600" title="Move down">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $widget->title }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ str_replace('_', ' ', ucfirst($widget->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($widget->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $widget->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button wire:click="delete({{ $widget->id }})" wire:confirm="Are you sure you want to delete this widget?" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No widgets yet. Add one above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
