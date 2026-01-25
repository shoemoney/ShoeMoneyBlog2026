<form wire:submit="submit" class="space-y-4">
    {{-- Honeypot spam trap (invisible) --}}
    <x-honeypot livewire-model="extraFields" />

    {{-- Name and Email row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="authorName-{{ $parentId ?? 'new' }}"
                   class="block text-sm font-medium text-gray-700">
                Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="authorName-{{ $parentId ?? 'new' }}"
                   wire:model.blur="authorName"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                          focus:border-blue-500 focus:ring-blue-500
                          @error('authorName') border-red-500 @enderror"
                   placeholder="Your name">
            @error('authorName')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="authorEmail-{{ $parentId ?? 'new' }}"
                   class="block text-sm font-medium text-gray-700">
                Email <span class="text-red-500">*</span>
                <span class="text-gray-400 font-normal">(not published)</span>
            </label>
            <input type="email"
                   id="authorEmail-{{ $parentId ?? 'new' }}"
                   wire:model.blur="authorEmail"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                          focus:border-blue-500 focus:ring-blue-500
                          @error('authorEmail') border-red-500 @enderror"
                   placeholder="you@example.com">
            @error('authorEmail')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Website (optional) --}}
    <div>
        <label for="authorUrl-{{ $parentId ?? 'new' }}"
               class="block text-sm font-medium text-gray-700">
            Website <span class="text-gray-400 font-normal">(optional)</span>
        </label>
        <input type="url"
               id="authorUrl-{{ $parentId ?? 'new' }}"
               wire:model.blur="authorUrl"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                      focus:border-blue-500 focus:ring-blue-500
                      @error('authorUrl') border-red-500 @enderror"
               placeholder="https://yourwebsite.com">
        @error('authorUrl')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Comment content --}}
    <div>
        <label for="content-{{ $parentId ?? 'new' }}"
               class="block text-sm font-medium text-gray-700">
            Comment <span class="text-red-500">*</span>
        </label>
        <textarea id="content-{{ $parentId ?? 'new' }}"
                  wire:model="content"
                  rows="4"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                         focus:border-blue-500 focus:ring-blue-500
                         @error('content') border-red-500 @enderror"
                  placeholder="Write your comment..."></textarea>
        @error('content')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit button --}}
    <div class="flex items-center justify-between">
        <button type="submit"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md
                       hover:bg-blue-700 focus:outline-none focus:ring-2
                       focus:ring-blue-500 focus:ring-offset-2
                       disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="submit">
                {{ $parentId ? 'Post Reply' : 'Post Comment' }}
            </span>
            <span wire:loading wire:target="submit">
                Posting...
            </span>
        </button>

        <p class="text-sm text-gray-500">
            <span class="text-red-500">*</span> Required fields
        </p>
    </div>
</form>
