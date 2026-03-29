<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
        <a href="{{ route('admin.posts.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Back to Posts
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
                <p class="mt-1 text-sm text-gray-500">Changing this will break existing links to this post</p>
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

            {{-- Featured Image / Thumbnail --}}
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Featured Image (AI Generated)
                </label>

                @if ($featuredImage)
                    {{-- Current image --}}
                    @if ($featuredImage->isCompleted() && $featuredImage->medium_url)
                        <div class="mb-3">
                            <img src="{{ $featuredImage->medium_url }}" alt="Featured image" class="w-full max-w-md rounded-lg shadow-sm">
                        </div>
                    @endif

                    {{-- Status badge --}}
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-xs font-medium">Status:</span>
                        @if ($featuredImage->isCompleted())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                        @elseif ($featuredImage->status === 'generating')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Generating...</span>
                        @elseif ($featuredImage->isPending())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif ($featuredImage->isFailed())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
                            @if ($featuredImage->error_message)
                                <span class="text-xs text-red-500">{{ Str::limit($featuredImage->error_message, 80) }}</span>
                            @endif
                        @endif
                    </div>

                    {{-- Editable Prompt --}}
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">AI Prompt (editable):</label>
                        <textarea
                            wire:model="customPrompt"
                            rows="4"
                            class="w-full text-sm text-gray-700 bg-white border border-gray-200 rounded-lg p-3 leading-relaxed focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-y"
                            placeholder="Enter a custom prompt or leave blank to auto-generate from post content..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-400">Edit the prompt above and click "Regenerate" to use it, or clear it to auto-generate a new one.</p>
                    </div>
                @else
                    <div class="mb-3">
                        <p class="text-sm text-gray-500 mb-2">No featured image generated yet.</p>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Custom Prompt (optional):</label>
                        <textarea
                            wire:model="customPrompt"
                            rows="3"
                            class="w-full text-sm text-gray-700 bg-white border border-gray-200 rounded-lg p-3 leading-relaxed focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-y"
                            placeholder="Leave blank to auto-generate from post content..."
                        ></textarea>
                    </div>
                @endif

                {{-- Reference Images --}}
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Reference Images:</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach ($referenceImages as $refImg)
                            <div class="group flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-gray-700">{{ $refImg }}</span>
                                <button
                                    type="button"
                                    wire:click="removeReferenceImage('{{ $refImg }}')"
                                    wire:confirm="Remove this reference image?"
                                    class="ml-1 text-gray-400 hover:text-red-500 font-bold opacity-0 group-hover:opacity-100 transition-opacity"
                                >&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2">
                        <input
                            type="file"
                            wire:model="newReferenceImage"
                            accept="image/png,image/jpeg,image/webp"
                            class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                        >
                        @if ($newReferenceImage)
                            <button
                                type="button"
                                wire:click="uploadReferenceImage"
                                class="px-3 py-1 bg-gray-600 text-white text-xs font-medium rounded-lg hover:bg-gray-700"
                            >Upload</button>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-400">These face/style reference images guide the AI when generating thumbnails.</p>
                </div>

                <button
                    type="button"
                    wire:click="regenerateThumbnail"
                    wire:confirm="This will replace the current featured image. Continue?"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ $featuredImage ? 'Regenerate Thumbnail' : 'Generate Thumbnail' }}
                </button>
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
                @if ($post->status === 'published')
                    <p class="mt-1 text-sm text-gray-500">
                        Published on {{ $post->published_at->format('M j, Y \a\t g:i A') }}
                    </p>
                @endif
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
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex items-center justify-between">
            <button
                type="button"
                wire:click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
            >
                Delete Post
            </button>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    Update Post
                </button>
            </div>
        </div>
    </form>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="cancelDelete">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Post</h3>
                <p class="text-gray-600 mb-1">Are you sure you want to delete:</p>
                <p class="font-semibold text-gray-900 mb-4">"{{ $title }}"</p>
                <p class="text-sm text-red-600 mb-6">This action cannot be undone.</p>
                <div class="flex justify-end space-x-3">
                    <button
                        wire:click="cancelDelete"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="delete"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
