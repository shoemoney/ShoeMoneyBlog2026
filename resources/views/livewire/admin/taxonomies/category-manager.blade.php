<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Categories</h1>

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
            {{ $editingId ? 'Edit Category' : 'Add New Category' }}
        </h2>

        <form wire:submit="{{ $editingId ? 'update' : 'create' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model.live="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                        placeholder="Category name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input
                        type="text"
                        id="slug"
                        wire:model="slug"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                        placeholder="category-slug"
                    >
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    id="description"
                    wire:model="description"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Optional description"
                ></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    {{ $editingId ? 'Update Category' : 'Add Category' }}
                </button>

                @if ($editingId)
                    <button
                        type="button"
                        wire:click="cancelEdit"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                    >
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Slug
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Posts
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                            @if ($category->description)
                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ $category->description }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm text-gray-600 bg-gray-100 px-2 py-1 rounded">{{ $category->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $category->posts_count }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                wire:click="edit({{ $category->id }})"
                                class="text-blue-600 hover:text-blue-900 mr-3"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="delete({{ $category->id }})"
                                wire:confirm="Are you sure you want to delete this category?"
                                @if ($category->posts_count > 0) disabled @endif
                                class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:cursor-not-allowed"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No categories found. Create one above.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
