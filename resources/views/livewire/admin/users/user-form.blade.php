<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $this->isEditing() ? 'Edit User' : 'Create User' }}
        </h1>
    </div>

    {{-- Form --}}
    <form wire:submit="save" class="space-y-6">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
                Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="name"
                   wire:model="name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email"
                   id="email"
                   wire:model="email"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Author Name / Display Name --}}
        <div>
            <label for="author_name" class="block text-sm font-medium text-gray-700">
                Display Name
            </label>
            <input type="text"
                   id="author_name"
                   wire:model="author_name"
                   placeholder="Leave blank to use name"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('author_name') border-red-300 @enderror">
            <p class="mt-1 text-sm text-gray-500">This name will be displayed publicly on posts.</p>
            @error('author_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Password
                @if (!$this->isEditing())
                    <span class="text-red-500">*</span>
                @endif
            </label>
            <input type="password"
                   id="password"
                   wire:model="password"
                   autocomplete="new-password"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-300 @enderror">
            @if ($this->isEditing())
                <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password.</p>
            @endif
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Confirmation --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirm Password
            </label>
            <input type="password"
                   id="password_confirmation"
                   wire:model="password_confirmation"
                   autocomplete="new-password"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        {{-- Is Admin Checkbox --}}
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="checkbox"
                       id="is_admin"
                       wire:model="is_admin"
                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            </div>
            <div class="ml-3">
                <label for="is_admin" class="text-sm font-medium text-gray-700">
                    Administrator
                </label>
                <p class="text-sm text-gray-500">
                    Administrators have full access to manage all content and users.
                </p>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.users.index') }}"
               wire:navigate
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                {{ $this->isEditing() ? 'Update User' : 'Create User' }}
            </button>
        </div>
    </form>
</div>
