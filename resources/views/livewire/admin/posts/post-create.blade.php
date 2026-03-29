<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Back to Posts
        </a>
    </div>

    {{-- Form Card --}}
    <form wire:submit="save" class="bg-white rounded-lg shadow">
        <div class="p-6 space-y-6">
            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    wire:model.blur="title"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                    placeholder="Enter post title"
                >
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug --}}
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                    Slug <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="slug"
                    wire:model.blur="slug"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                    placeholder="post-url-slug"
                >
                <p class="mt-1 text-sm text-gray-500">Auto-generated from title if left empty</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Content --}}
            <div wire:ignore>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Content <span class="text-red-500">*</span>
                </label>
                <x-admin.tiptap-editor wire-model="content" :content="$content" />
            </div>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            {{-- Excerpt --}}
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">
                    Excerpt
                </label>
                <textarea
                    id="excerpt"
                    wire:model.blur="excerpt"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Brief summary for listings (optional)"
                ></textarea>
                @error('excerpt')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                    Status <span class="text-red-500">*</span>
                </label>
                <select
                    id="status"
                    wire:model="status"
                    class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Categories --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Categories
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 p-4 border border-gray-200 rounded-lg bg-gray-50 max-h-48 overflow-y-auto">
                    @forelse ($categories as $category)
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="selectedCategories"
                                value="{{ $category->id }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm text-gray-700">{{ $category->name }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500 col-span-full">No categories available</p>
                    @endforelse
                </div>
            </div>

            {{-- Tags --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tags
                </label>

                {{-- Selected tags as pills --}}
                @if (count($selectedTagNames))
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach ($selectedTagNames as $id => $name)
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                {{ $name }}
                                <button type="button" wire:click="removeTag({{ $id }})" class="text-blue-600 hover:text-blue-900 font-bold">&times;</button>
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Tag search input --}}
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="tagSearch"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search tags..."
                        autocomplete="off"
                    >

                    {{-- Search results dropdown --}}
                    @if (count($tagResults))
                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach ($tagResults as $id => $name)
                                <button
                                    type="button"
                                    wire:click="addTag({{ $id }})"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700"
                                >
                                    {{ $name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <p class="mt-1 text-sm text-gray-500">{{ count($selectedTags) }} tag(s) selected</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-end space-x-4">
            <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                Cancel
            </a>
            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
            >
                Create Post
            </button>
        </div>
    </form>
</div>
