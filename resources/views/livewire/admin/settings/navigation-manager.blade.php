<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Navigation Menu</h1>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Create/Edit Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ $editingId ? 'Edit Menu Item' : 'Add Menu Item' }}
        </h2>

        <form wire:submit="{{ $editingId ? 'update' : 'create' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                    <input type="text" id="label" wire:model="label"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('label') border-red-500 @enderror"
                        placeholder="Menu item label">
                    @error('label') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select id="type" wire:model.live="type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="custom">Custom URL</option>
                        <option value="page">Page</option>
                        <option value="category">Category</option>
                    </select>
                </div>
            </div>

            {{-- Conditional fields based on type --}}
            <div class="mb-4">
                @if ($type === 'custom')
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-1">URL</label>
                    <input type="text" id="url" wire:model="url"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('url') border-red-500 @enderror"
                        placeholder="https://example.com or /relative-path">
                    @error('url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @elseif ($type === 'page')
                    <label for="linkable_id" class="block text-sm font-medium text-gray-700 mb-1">Page</label>
                    <select id="linkable_id" wire:model="linkable_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white @error('linkable_id') border-red-500 @enderror">
                        <option value="">Select a page...</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                        @endforeach
                    </select>
                    @error('linkable_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @elseif ($type === 'category')
                    <label for="linkable_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="linkable_id" wire:model="linkable_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white @error('linkable_id') border-red-500 @enderror">
                        <option value="">Select a category...</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('linkable_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                @endif
            </div>

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
                    {{ $editingId ? 'Update Item' : 'Add Item' }}
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

    {{-- Menu Items Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($menuItems as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-1">
                                <button wire:click="moveUp({{ $item->id }})" class="text-gray-400 hover:text-gray-600" title="Move up">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button wire:click="moveDown({{ $item->id }})" class="text-gray-400 hover:text-gray-600" title="Move down">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $item->label }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $item->type === 'custom' ? 'bg-gray-100 text-gray-800' : ($item->type === 'page' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ ucfirst($item->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm text-gray-600">{{ $item->resolved_url }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($item->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <button wire:click="delete({{ $item->id }})" wire:confirm="Are you sure you want to delete this menu item?" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No menu items yet. Add one above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
