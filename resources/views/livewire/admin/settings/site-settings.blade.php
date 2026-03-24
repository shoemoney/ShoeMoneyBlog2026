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
                    <label for="site_logo_url" class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
                    <input type="url" id="site_logo_url" wire:model="site_logo_url" placeholder="https://cdn.example.com/logo.png"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('site_logo_url') border-red-500 @enderror">
                    @error('site_logo_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">Appears in the top-left of the navbar next to the site name.</p>
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

        {{-- Hero / Bio Section --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Hero / Bio Section</h2>
            <p class="text-sm text-gray-500 mb-4">Appears at the top of the homepage. Photo on the left, blurb on the right.</p>
            <div class="space-y-4">
                <div>
                    <label for="hero_photo_url" class="block text-sm font-medium text-gray-700 mb-1">Photo URL</label>
                    <input type="url" id="hero_photo_url" wire:model="hero_photo_url" placeholder="https://cdn.example.com/photo.jpg"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hero_photo_url') border-red-500 @enderror">
                    @error('hero_photo_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">Direct URL to your profile photo. Also used as a mini avatar in the navbar.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="hero_name" class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                        <input type="text" id="hero_name" wire:model="hero_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="hero_title" class="block text-sm font-medium text-gray-700 mb-1">Title / Tagline</label>
                        <input type="text" id="hero_title" wire:model="hero_title" placeholder="Entrepreneur & Blogger"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label for="hero_blurb" class="block text-sm font-medium text-gray-700 mb-1">Bio Blurb</label>
                    <textarea id="hero_blurb" wire:model="hero_blurb" rows="4" placeholder="A few sentences about yourself..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('hero_blurb') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Hero: "As Seen In" Press Mentions --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Hero: Press / "As Seen In"</h2>
            <p class="text-sm text-gray-500 mb-4">News outlets, podcasts, or publications you've been featured in. Displayed in the hero section.</p>
            <div class="space-y-3">
                @foreach($hero_press_items as $i => $item)
                    <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" wire:model="hero_press_items.{{ $i }}.name" placeholder="e.g. TechCrunch, Forbes, CNN..."
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <input type="url" wire:model="hero_press_items.{{ $i }}.url" placeholder="https://..."
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" wire:click="removePressItem({{ $i }})"
                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
                <button type="button" wire:click="addPressItem"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                    + Add Press Mention
                </button>
            </div>
        </div>

        {{-- Hero: Project & Social Links --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Hero: Links & Profiles</h2>
            <p class="text-sm text-gray-500 mb-4">Project links, social profiles, and other links shown with icons in the hero section.</p>
            <div class="space-y-3">
                @foreach($hero_links as $i => $link)
                    <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1 space-y-2">
                            <div class="grid grid-cols-3 gap-2">
                                <select wire:model="hero_links.{{ $i }}.icon"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="github">GitHub</option>
                                    <option value="twitter">Twitter / X</option>
                                    <option value="youtube">YouTube</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="mastodon">Mastodon</option>
                                    <option value="discord">Discord</option>
                                    <option value="twitch">Twitch</option>
                                    <option value="reddit">Reddit</option>
                                    <option value="website">Website</option>
                                    <option value="blog">Blog</option>
                                    <option value="email">Email</option>
                                    <option value="rss">RSS</option>
                                    <option value="link">Other Link</option>
                                </select>
                                <input type="text" wire:model="hero_links.{{ $i }}.label" placeholder="Label"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <input type="url" wire:model="hero_links.{{ $i }}.url" placeholder="https://..."
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <button type="button" wire:click="removeHeroLink({{ $i }})"
                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
                <button type="button" wire:click="addHeroLink"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                    + Add Link
                </button>
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

        {{-- Footer Settings --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Footer</h2>
            <p class="text-sm text-gray-500 mb-4">Customize the footer text and links.</p>
            <div class="space-y-4">
                <div>
                    <label for="footer_text" class="block text-sm font-medium text-gray-700 mb-1">Footer Text</label>
                    <input type="text" id="footer_text" wire:model="footer_text" placeholder="All rights reserved."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Footer Links</label>
                    <div class="space-y-3">
                        @foreach($footer_links as $i => $link)
                            <div class="flex items-start gap-2 p-3 bg-gray-50 rounded-lg"
                                 x-data="{
                                     searchQuery: '',
                                     results: [],
                                     showDropdown: false,
                                     loading: false,
                                     async search() {
                                         if (this.searchQuery.length < 2) { this.results = []; this.showDropdown = false; return; }
                                         this.loading = true;
                                         this.results = await $wire.searchContent(this.searchQuery);
                                         this.showDropdown = this.results.length > 0;
                                         this.loading = false;
                                     },
                                     select(item) {
                                         $wire.set('footer_links.{{ $i }}.url', item.url);
                                         if (!$wire.footer_links[{{ $i }}]?.label || $wire.footer_links[{{ $i }}].label.trim() === '') {
                                             $wire.set('footer_links.{{ $i }}.label', item.title);
                                         }
                                         this.searchQuery = '';
                                         this.showDropdown = false;
                                         this.results = [];
                                     }
                                 }">
                                <div class="flex-1 space-y-2">
                                    {{-- Search autocomplete --}}
                                    <div class="relative">
                                        <input type="text"
                                            x-model="searchQuery"
                                            @input.debounce.300ms="search()"
                                            @focus="if(results.length) showDropdown = true"
                                            @click.away="showDropdown = false"
                                            placeholder="Search posts & pages to auto-fill..."
                                            class="w-full px-3 py-2 pl-9 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-blue-50"
                                            autocomplete="off">
                                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <template x-if="loading">
                                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                                <svg class="animate-spin w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            </div>
                                        </template>
                                        <div x-show="showDropdown" x-cloak
                                             class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                            <template x-for="(result, ri) in results" :key="ri">
                                                <button type="button"
                                                    @click="select(result)"
                                                    class="w-full text-left px-3 py-2 hover:bg-blue-50 border-b border-gray-100 last:border-0 transition-colors">
                                                    <div class="flex items-center gap-2">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                                              :class="result.type === 'Page' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'"
                                                              x-text="result.type"></span>
                                                        <span class="font-medium text-sm text-gray-900" x-text="result.title"></span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 flex justify-between mt-0.5">
                                                        <span x-text="result.url"></span>
                                                        <span x-text="result.date" class="text-gray-400"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    {{-- Label and URL fields --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <input type="text" wire:model="footer_links.{{ $i }}.label" placeholder="Label"
                                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                        <input type="url" wire:model="footer_links.{{ $i }}.url" placeholder="https://..."
                                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </div>
                                <button type="button" wire:click="removeFooterLink({{ $i }})"
                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addFooterLink"
                        class="mt-2 px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                        + Add Footer Link
                    </button>
                </div>
            </div>
        </div>

        {{-- Sidebar: Popular Posts (manually curated) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Sidebar: Popular Posts</h2>
            <p class="text-sm text-gray-500 mb-4">Manually curate which posts appear in the Popular Posts sidebar.</p>
            <div class="space-y-4">
                <label class="flex items-center gap-3">
                    <input type="checkbox" wire:model="popular_posts_enabled"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Show Popular Posts in sidebar</span>
                </label>

                @foreach ($popular_posts_items as $index => $item)
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg"
                         x-data="{
                             searchQuery: '',
                             results: [],
                             showDropdown: false,
                             loading: false,
                             async search() {
                                 if (this.searchQuery.length < 2) { this.results = []; this.showDropdown = false; return; }
                                 this.loading = true;
                                 this.results = await $wire.searchPostsForPopular(this.searchQuery);
                                 this.showDropdown = this.results.length > 0;
                                 this.loading = false;
                             },
                             select(item) {
                                 $wire.set('popular_posts_items.{{ $index }}.title', item.title);
                                 $wire.set('popular_posts_items.{{ $index }}.url', item.url);
                                 this.searchQuery = '';
                                 this.showDropdown = false;
                                 this.results = [];
                             }
                         }">
                        <span class="shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center mt-1">{{ $index + 1 }}</span>
                        <div class="flex-1 space-y-2">
                            {{-- Search input --}}
                            <div class="relative">
                                <input type="text"
                                    x-model="searchQuery"
                                    @input.debounce.300ms="search()"
                                    @focus="if(results.length) showDropdown = true"
                                    @click.away="showDropdown = false"
                                    placeholder="Search posts to auto-fill..."
                                    class="w-full px-3 py-2 pl-9 border border-blue-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-blue-50"
                                    autocomplete="off">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <template x-if="loading">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    </div>
                                </template>

                                {{-- Dropdown results --}}
                                <div x-show="showDropdown" x-cloak
                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="(result, ri) in results" :key="ri">
                                        <button type="button"
                                            @click="select(result)"
                                            class="w-full text-left px-3 py-2 hover:bg-blue-50 border-b border-gray-100 last:border-0 transition-colors">
                                            <div class="font-medium text-sm text-gray-900" x-text="result.title"></div>
                                            <div class="text-xs text-gray-500 flex justify-between">
                                                <span x-text="result.url"></span>
                                                <span x-text="result.date" class="text-gray-400"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            {{-- Title and URL fields --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="text" wire:model="popular_posts_items.{{ $index }}.title" placeholder="Post title"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <input type="text" wire:model="popular_posts_items.{{ $index }}.url" placeholder="/2018/06/05/post-slug/"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <button type="button" wire:click="removePopularPost({{ $index }})"
                            class="shrink-0 text-red-500 hover:text-red-700 p-1 mt-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                @endforeach

                <button type="button" wire:click="addPopularPost"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Popular Post
                </button>
            </div>
        </div>

        {{-- Sidebar: The Shitlist --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Sidebar: The Shitlist</h2>
            <p class="text-sm text-gray-500 mb-4">Companies and people you publicly call out.</p>
            <div class="space-y-4">
                <label class="flex items-center gap-3">
                    <input type="checkbox" wire:model="shitlist_enabled"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Show The Shitlist in sidebar</span>
                </label>

                <div class="space-y-3">
                    @foreach($shitlist_items as $i => $item)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 space-y-2">
                                    <input type="text" wire:model="shitlist_items.{{ $i }}.name" placeholder="Name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <input type="url" wire:model="shitlist_items.{{ $i }}.url" placeholder="URL (optional)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <input type="text" wire:model="shitlist_items.{{ $i }}.description" placeholder="Why they're on the list (optional)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <button type="button" wire:click="removeShitlistItem({{ $i }})"
                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addShitlistItem"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                    + Add to Shitlist
                </button>
            </div>
        </div>

        {{-- Sidebar: Resources --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Sidebar: Resources</h2>
            <p class="text-sm text-gray-500 mb-4">Useful links and tools you recommend.</p>
            <div class="space-y-4">
                <label class="flex items-center gap-3">
                    <input type="checkbox" wire:model="resources_enabled"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Show Resources in sidebar</span>
                </label>

                <div class="space-y-3">
                    @foreach($resources_items as $i => $item)
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-start gap-2">
                                <div class="flex-1 space-y-2">
                                    <input type="text" wire:model="resources_items.{{ $i }}.name" placeholder="Name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <input type="url" wire:model="resources_items.{{ $i }}.url" placeholder="URL"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <input type="text" wire:model="resources_items.{{ $i }}.description" placeholder="Brief description (optional)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                                <button type="button" wire:click="removeResourceItem({{ $i }})"
                                    class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addResourceItem"
                    class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                    + Add Resource
                </button>
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
                class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors shadow-sm">
                Save Settings
            </button>
        </div>
    </form>
</div>
