<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Page</h1>
        <a href="{{ route('admin.pages.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Back to Pages
        </a>
    </div>

    {{-- Success Message --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Card --}}
    <form wire:submit="update" class="bg-white rounded-lg shadow">
        <div class="p-6 space-y-6">
            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" wire:model.blur="title"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                    placeholder="Enter page title">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                    Slug <span class="text-red-500">*</span>
                </label>
                <input type="text" id="slug" wire:model="slug"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                    placeholder="page-url-slug">
                <p class="mt-1 text-sm text-gray-500">Changing this will break existing links to this page</p>
                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Content --}}
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                    Content <span class="text-red-500">*</span>
                </label>
                <textarea id="content" wire:model="content" rows="12"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm @error('content') border-red-500 @enderror"
                    placeholder="Write your page content here. HTML is supported."></textarea>
                @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Menu Order --}}
            <div>
                <label for="menu_order" class="block text-sm font-medium text-gray-700 mb-1">Menu Order</label>
                <input type="number" id="menu_order" wire:model="menu_order" min="0"
                    class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-end space-x-4">
            <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</a>
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Update Page
            </button>
        </div>
    </form>
</div>
