@php
    $siteName = \App\Models\Setting::getValue('site_name', 'ShoeMoney');
    $footerText = \App\Models\Setting::getValue('footer_text', '');
    $footerLinks = \App\Models\Setting::getValue('footer_links', []);
    $socialTwitter = \App\Models\Setting::getValue('social_twitter', '');
    $socialFacebook = \App\Models\Setting::getValue('social_facebook', '');
    $socialYoutube = \App\Models\Setting::getValue('social_youtube', '');
    $socialLinkedin = \App\Models\Setting::getValue('social_linkedin', '');
@endphp

<footer class="bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 mt-auto">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Left: Brand + optional text --}}
            <div class="text-center md:text-left">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    &copy; {{ date('Y') }} {{ $siteName }}
                </p>
                @if($footerText)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $footerText }}</p>
                @endif
            </div>

            {{-- Center: Editable links --}}
            @if(!empty($footerLinks))
                <nav class="flex items-center flex-wrap justify-center gap-x-6 gap-y-2 text-sm">
                    @foreach($footerLinks as $link)
                        <a href="{{ $link['url'] }}"
                           class="text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                           @if(!empty($link['new_tab'])) target="_blank" rel="noopener" @endif>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>
            @else
                {{-- Fallback links --}}
                <nav class="flex items-center space-x-6 text-sm">
                    <a href="/privacy-policy/" class="text-gray-500 dark:text-gray-400 hover:text-blue-600 transition-colors">Privacy</a>
                    <a href="/terms-of-service/" class="text-gray-500 dark:text-gray-400 hover:text-blue-600 transition-colors">Terms</a>
                </nav>
            @endif

            {{-- Right: Social icons --}}
            @if($socialTwitter || $socialFacebook || $socialYoutube || $socialLinkedin)
                <div class="flex items-center gap-4">
                    @if($socialTwitter)
                        <a href="{{ $socialTwitter }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                    @endif
                    @if($socialFacebook)
                        <a href="{{ $socialFacebook }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    @endif
                    @if($socialYoutube)
                        <a href="{{ $socialYoutube }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </a>
                    @endif
                    @if($socialLinkedin)
                        <a href="{{ $socialLinkedin }}" target="_blank" rel="noopener" class="text-gray-400 hover:text-blue-700 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</footer>
