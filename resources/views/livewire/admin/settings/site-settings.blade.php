<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Site Settings</h1>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        {{-- General Settings --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">General</h2>
            <div class="space-y-4">
                <div>
                    <label for="site_name" class="block text-sm font-medium text-gray-700 mb-1">Site Name</label>
                    <input type="text" id="site_name" wire:model="site_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('site_name') border-red-500 @enderror">
                    @error('site_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="site_tagline" class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                    <input type="text" id="site_tagline" wire:model="site_tagline"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                    <textarea id="meta_description" wire:model="meta_description" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div>
                    <label for="posts_per_page" class="block text-sm font-medium text-gray-700 mb-1">Posts Per Page</label>
                    <input type="number" id="posts_per_page" wire:model="posts_per_page" min="1" max="100"
                        class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('posts_per_page') border-red-500 @enderror">
                    @error('posts_per_page') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Comments Settings --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Comments</h2>
            <div>
                <label for="comment_moderation" class="block text-sm font-medium text-gray-700 mb-1">Comment Moderation</label>
                <select id="comment_moderation" wire:model="comment_moderation"
                    class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="none">No moderation</option>
                    <option value="first_time">First-time commenters</option>
                    <option value="all">All comments</option>
                </select>
                <p class="mt-1 text-sm text-gray-500">Control when comments require manual approval.</p>
            </div>
        </div>

        {{-- Social Media --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Social Media</h2>
            <div class="space-y-4">
                <div>
                    <label for="social_twitter" class="block text-sm font-medium text-gray-700 mb-1">Twitter / X URL</label>
                    <input type="url" id="social_twitter" wire:model="social_twitter" placeholder="https://x.com/username"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_twitter') border-red-500 @enderror">
                    @error('social_twitter') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="social_facebook" class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                    <input type="url" id="social_facebook" wire:model="social_facebook" placeholder="https://facebook.com/page"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_facebook') border-red-500 @enderror">
                    @error('social_facebook') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="social_youtube" class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                    <input type="url" id="social_youtube" wire:model="social_youtube" placeholder="https://youtube.com/@channel"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_youtube') border-red-500 @enderror">
                    @error('social_youtube') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="social_linkedin" class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                    <input type="url" id="social_linkedin" wire:model="social_linkedin" placeholder="https://linkedin.com/in/username"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('social_linkedin') border-red-500 @enderror">
                    @error('social_linkedin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Code Snippets --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Code Snippets</h2>
            <div class="space-y-4">
                <div>
                    <label for="analytics_code" class="block text-sm font-medium text-gray-700 mb-1">Analytics Code</label>
                    <textarea id="analytics_code" wire:model="analytics_code" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                        placeholder="Google Analytics, Plausible, etc."></textarea>
                </div>

                <div>
                    <label for="custom_header_code" class="block text-sm font-medium text-gray-700 mb-1">Custom Header Code</label>
                    <textarea id="custom_header_code" wire:model="custom_header_code" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                        placeholder="Code injected before </head>"></textarea>
                </div>

                <div>
                    <label for="custom_footer_code" class="block text-sm font-medium text-gray-700 mb-1">Custom Footer Code</label>
                    <textarea id="custom_footer_code" wire:model="custom_footer_code" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                        placeholder="Code injected before </body>"></textarea>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end">
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>
