# Phase 3: Public Content Display - Research

**Researched:** 2026-01-24
**Domain:** Laravel Blade templating, Livewire components, Tailwind CSS styling, WordPress content rendering
**Confidence:** HIGH

## Summary

This phase transforms the working URL routing from Phase 2 into a fully functional public-facing blog with styled content display. The research covers five interconnected domains: (1) Blade layouts and component architecture for reusable templates, (2) Livewire for interactive elements like pagination, (3) Tailwind CSS 4 with the Typography plugin for content styling, (4) shortcode handling for the 89 unique shortcodes (892 usages) identified in Phase 1, and (5) SEO meta tag management.

The standard approach uses Laravel Blade anonymous components for layouts with named slots, Tailwind CSS 4's `prose` class for typographic styling of WordPress content, and a custom shortcode processor to convert WordPress shortcodes to HTML during render time. Livewire is primarily needed for Phase 4 (comments) but can be introduced now for pagination if desired. For this content-focused phase, standard Laravel pagination with Blade views is simpler and sufficient.

The ShoeMoney brand identity will require custom CSS variables for colors since specific hex codes could not be retrieved from the live site. Research suggests a clean, white-focused design with Web 2.0 aesthetics based on historical references.

**Primary recommendation:** Use Blade anonymous components for layouts, `@tailwindcss/typography` prose classes for WordPress content styling, a custom ShortcodeProcessor service class for converting the top 4 shortcodes ([more], [caption], [video], [gravityform]) to HTML, and `archtechx/laravel-seo` for meta tags and OpenGraph.

## Standard Stack

The established libraries/tools for this domain:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel Blade | 12.x (bundled) | Templating engine | Native, zero overhead, component system |
| Tailwind CSS | 4.0.x | Utility-first CSS framework | Already installed, v4 stable (Jan 2025) |
| @tailwindcss/typography | latest | Prose content styling | Official Tailwind plugin, 100+ million installs |
| archtechx/laravel-seo | latest | Meta tags, OpenGraph, Twitter cards | Clean API, Laravel-native, maintained by ArchTech |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Livewire | 3.x | Reactive components | Phase 4 comments, optional for pagination |
| mtownsend/read-time | latest | Reading time estimation | Blog posts reading time display |
| Illuminate\Support\Str | bundled | Excerpt generation | `Str::limit()` for post excerpts |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Custom shortcode processor | vedmant/laravel-shortcodes | Package last updated 2023, uncertain Laravel 12 support. Custom is safer |
| archtechx/laravel-seo | butschster/LaravelMetaTags | More complex API, archtechx is simpler for basic needs |
| Standard pagination | Livewire pagination | Livewire adds complexity for static content pages |
| Blade layouts | Inertia/Vue | User decision: Livewire over Inertia/Vue for simpler mental model |

**Installation:**
```bash
# Required
npm install -D @tailwindcss/typography
composer require archtechx/laravel-seo

# Optional (if reading time needed)
composer require mtownsend/read-time

# Livewire (install now or defer to Phase 4)
composer require livewire/livewire
```

## Architecture Patterns

### Recommended Project Structure
```
resources/
├── views/
│   ├── components/
│   │   ├── layout.blade.php        # Main site layout
│   │   ├── navigation.blade.php    # Site header/nav
│   │   ├── footer.blade.php        # Site footer
│   │   ├── post-card.blade.php     # Post excerpt card for listings
│   │   ├── pagination.blade.php    # Custom pagination links
│   │   └── seo-meta.blade.php      # Meta tag component (wraps seo())
│   ├── posts/
│   │   ├── index.blade.php         # Homepage blog listing
│   │   └── show.blade.php          # Single post view
│   ├── pages/
│   │   └── show.blade.php          # Static page view
│   ├── categories/
│   │   └── show.blade.php          # Category archive
│   └── tags/
│       └── show.blade.php          # Tag archive
app/
├── Services/
│   └── ShortcodeProcessor.php      # WordPress shortcode to HTML converter
├── View/
│   └── Components/                 # Class-based components (if needed)
└── Providers/
    └── AppServiceProvider.php      # Register Blade directives
```

### Pattern 1: Blade Layout with Named Slots
**What:** Single layout component with slots for title, content, and optional sidebar
**When to use:** All public pages share the same overall structure
**Example:**
```php
// resources/views/components/layout.blade.php
// Source: Laravel 12.x Blade Documentation
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo::meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">
    <x-navigation />

    <main class="container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>
```

**Usage:**
```blade
{{-- resources/views/posts/show.blade.php --}}
<x-layout>
    @php
        seo()
            ->title($post->title . ' - ShoeMoney')
            ->description($post->excerpt)
            ->image($post->featured_image_url);
    @endphp

    <article class="prose lg:prose-xl dark:prose-invert mx-auto">
        <h1>{{ $post->title }}</h1>
        {!! $post->rendered_content !!}
    </article>
</x-layout>
```

### Pattern 2: Anonymous Components for Reusable UI
**What:** Simple Blade components without PHP class for common UI elements
**When to use:** Post cards, buttons, badges that don't need complex logic
**Example:**
```blade
{{-- resources/views/components/post-card.blade.php --}}
@props(['post'])

<article class="border-b border-gray-200 py-6">
    <a href="{{ $post->url }}" class="group">
        <h2 class="text-2xl font-bold text-gray-900 group-hover:text-blue-600">
            {{ $post->title }}
        </h2>
    </a>

    <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
        <time datetime="{{ $post->published_at->toIso8601String() }}">
            {{ $post->published_at->format('F j, Y') }}
        </time>
        <span>{{ $post->author->name }}</span>
        @if($post->reading_time)
            <span>{{ $post->reading_time }} min read</span>
        @endif
    </div>

    <p class="mt-3 text-gray-600">
        {{ Str::limit(strip_tags($post->excerpt ?: $post->content), 200) }}
    </p>

    <div class="mt-3 flex flex-wrap gap-2">
        @foreach($post->categories as $category)
            <a href="{{ $category->url }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-800">
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</article>
```

### Pattern 3: Tailwind Typography for WordPress Content
**What:** Use prose classes to style raw HTML content from WordPress
**When to use:** Single post/page content display
**Example:**
```blade
{{-- Content styling with prose --}}
<article class="prose prose-lg prose-slate max-w-none
                prose-headings:font-bold prose-headings:text-gray-900
                prose-a:text-blue-600 prose-a:no-underline hover:prose-a:underline
                prose-img:rounded-lg prose-img:shadow-md
                prose-pre:bg-gray-900
                dark:prose-invert">
    {!! $post->rendered_content !!}
</article>
```

**Tailwind CSS 4 Configuration:**
```css
/* resources/css/app.css */
@import "tailwindcss";
@plugin "@tailwindcss/typography";

/* Custom ShoeMoney brand colors */
@theme {
    --color-brand-primary: #1e3a8a;    /* Blue - placeholder, update with actual */
    --color-brand-accent: #f59e0b;     /* Amber - placeholder, update with actual */
    --color-brand-light: #f8fafc;      /* Near-white background */
}
```

### Pattern 4: ShortcodeProcessor Service
**What:** Convert WordPress shortcodes to HTML before rendering
**When to use:** Processing post/page content with shortcodes
**Example:**
```php
// app/Services/ShortcodeProcessor.php
namespace App\Services;

class ShortcodeProcessor
{
    /**
     * Process WordPress shortcodes in content.
     */
    public function process(string $content): string
    {
        // [more] - Convert to horizontal rule (read more break)
        $content = preg_replace('/\[more\]/', '<hr class="wp-more">', $content);

        // [caption] - Convert to figure/figcaption
        $content = preg_replace_callback(
            '/\[caption[^\]]*\](.*?)\[\/caption\]/s',
            fn($m) => $this->processCaption($m[0], $m[1]),
            $content
        );

        // [video] - Convert to HTML5 video element
        $content = preg_replace_callback(
            '/\[video([^\]]*)\]/s',
            fn($m) => $this->processVideo($m[1]),
            $content
        );

        // [gravityform] - Replace with placeholder or contact link
        $content = preg_replace(
            '/\[gravityform[^\]]*\]/',
            '<div class="bg-gray-100 p-4 rounded text-center">
                <p>Form temporarily unavailable. <a href="/contact/" class="text-blue-600">Contact us</a></p>
            </div>',
            $content
        );

        return $content;
    }

    private function processCaption(string $full, string $inner): string
    {
        // Extract attributes
        preg_match('/width="(\d+)"/', $full, $width);
        preg_match('/caption="([^"]*)"/', $full, $caption);

        $w = $width[1] ?? 'auto';
        $cap = $caption[1] ?? '';

        return sprintf(
            '<figure class="wp-caption" style="max-width:%spx">%s<figcaption>%s</figcaption></figure>',
            $w,
            trim($inner),
            $cap ?: trim(strip_tags($inner))
        );
    }

    private function processVideo(string $attrs): string
    {
        preg_match('/mp4="([^"]*)"/', $attrs, $mp4);
        preg_match('/webm="([^"]*)"/', $attrs, $webm);
        preg_match('/poster="([^"]*)"/', $attrs, $poster);

        $sources = '';
        if (!empty($mp4[1])) $sources .= sprintf('<source src="%s" type="video/mp4">', $mp4[1]);
        if (!empty($webm[1])) $sources .= sprintf('<source src="%s" type="video/webm">', $webm[1]);

        return sprintf(
            '<video class="wp-video" controls preload="metadata" %s>%s</video>',
            !empty($poster[1]) ? 'poster="'.$poster[1].'"' : '',
            $sources
        );
    }
}
```

### Pattern 5: Model Accessor for Rendered Content
**What:** Add accessor to Post model that processes content through ShortcodeProcessor
**When to use:** Clean separation of content processing from views
**Example:**
```php
// app/Models/Post.php
use App\Services\ShortcodeProcessor;
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function renderedContent(): Attribute
{
    return Attribute::make(
        get: function () {
            $processor = app(ShortcodeProcessor::php);
            return $processor->process($this->content);
        }
    )->shouldCache();
}

protected function readingTime(): Attribute
{
    return Attribute::make(
        get: fn () => max(1, (int) ceil(str_word_count(strip_tags($this->content)) / 200))
    )->shouldCache();
}
```

### Anti-Patterns to Avoid
- **Processing shortcodes in Blade:** Keep processing logic in service classes, not templates
- **Using `{!! !!}` without sanitization:** WordPress content should be processed but already contains trusted HTML
- **Over-engineering with Livewire:** For static content display, standard Blade is simpler and faster
- **Ignoring prose max-width:** Add `max-w-none` or `mx-auto max-w-prose` for proper content width
- **Missing dark mode:** Always pair `prose` with `dark:prose-invert` for dark mode support

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Meta tags/OpenGraph | Custom meta tag helper | archtechx/laravel-seo | Handles fallbacks, Twitter cards, canonical URLs |
| Content typography | Custom CSS for each element | @tailwindcss/typography | Tested defaults for 50+ HTML elements |
| Reading time estimation | Word count division | mtownsend/read-time | Handles edge cases, configurable WPM |
| Pagination styling | Custom pagination views | Tailwind UI pagination | Pre-built accessible pagination |
| Excerpt generation | Custom truncation | Str::limit() | Handles multibyte, word boundaries |
| Date formatting | Manual date strings | Carbon formatting | Localization, relative dates |

**Key insight:** WordPress content contains arbitrary HTML from 20 years of editing. Tailwind Typography's prose class handles tables, blockquotes, nested lists, code blocks, and other elements that custom CSS would miss.

## Common Pitfalls

### Pitfall 1: Unescaped HTML Causing XSS
**What goes wrong:** Using `{!! !!}` blindly on user content opens XSS vulnerabilities
**Why it happens:** WordPress content contains HTML that needs to render correctly
**How to avoid:** WordPress content is trusted (you migrated it). Process through ShortcodeProcessor but don't add user-submitted content without sanitization
**Warning signs:** Seeing raw HTML in output, or JavaScript executing on page

### Pitfall 2: Prose Class Width Overflow
**What goes wrong:** Content with prose class is constrained to ~65ch, images/tables overflow
**Why it happens:** prose has built-in max-width for optimal reading
**How to avoid:** Use `max-w-none` for full-width content areas, or keep default and use `not-prose` for wide elements
**Warning signs:** Images cut off, tables not fully visible, layout breaking on wide content

### Pitfall 3: Missing Shortcode Handler Causes Raw Text
**What goes wrong:** Unhandled shortcodes like `[gallery id="123"]` appear as plain text
**Why it happens:** Shortcode audit found 89 types but only top 4 are implemented
**How to avoid:** Create a fallback handler that strips unknown shortcodes: `preg_replace('/\[[^\]]+\]/', '', $content)`
**Warning signs:** Square brackets visible in rendered content

### Pitfall 4: Pagination Breaks with JavaScript-Dependent Views
**What goes wrong:** Standard Laravel pagination needs server render, doesn't work with SPA-style navigation
**Why it happens:** Mixing server-rendered pagination with client-side navigation
**How to avoid:** Stick to full page reloads for pagination (simplest), or use Livewire consistently
**Warning signs:** Back button not working, page state lost on pagination

### Pitfall 5: SEO Meta Tags Not Rendering
**What goes wrong:** OpenGraph images don't show on social shares
**Why it happens:** seo() helper called after `<head>` tag, or image URL is relative not absolute
**How to avoid:** Call seo() in view before layout renders, always use absolute URLs for images
**Warning signs:** Facebook/Twitter debugger shows no image, blank meta tags

### Pitfall 6: N+1 Queries on Post Listings
**What goes wrong:** Homepage with 10 posts makes 30+ queries for author, categories, tags
**Why it happens:** Not eager loading relationships in controller
**How to avoid:** Always use `->with('author', 'categories', 'tags')` in listing queries
**Warning signs:** Debug bar showing 50+ queries, slow page loads

### Pitfall 7: Tailwind CSS v4 Plugin Syntax Error
**What goes wrong:** Typography plugin not loading, prose classes don't work
**Why it happens:** Using v3 JavaScript config syntax instead of v4 CSS syntax
**How to avoid:** Use `@plugin "@tailwindcss/typography"` in CSS, not `plugins: [require(...)]` in JS
**Warning signs:** Prose class has no effect, elements unstyled

## Code Examples

Verified patterns from official sources:

### Homepage Blog Listing Controller
```php
// app/Http/Controllers/PostController.php
// Source: Pattern from Phase 2, updated for views

public function index(): View
{
    $posts = Post::published()
        ->with(['author', 'categories'])
        ->orderBy('published_at', 'desc')
        ->paginate(10);

    seo()
        ->title('ShoeMoney - Making Money Online')
        ->description('The original blog about making money online since 2003');

    return view('posts.index', compact('posts'));
}

public function show(string $year, string $month, string $day, string $slug): View
{
    $post = Post::query()
        ->where('slug', $slug)
        ->whereYear('published_at', $year)
        ->whereMonth('published_at', $month)
        ->whereDay('published_at', $day)
        ->where('status', 'published')
        ->with(['author', 'categories', 'tags'])
        ->firstOrFail();

    seo()
        ->title($post->title . ' - ShoeMoney')
        ->description($post->excerpt ?: Str::limit(strip_tags($post->content), 160))
        ->url(url($post->url));

    return view('posts.show', compact('post'));
}
```

### Blog Listing View
```blade
{{-- resources/views/posts/index.blade.php --}}
{{-- Source: Laravel 12.x Blade Documentation + Tailwind patterns --}}
<x-layout>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Latest Posts</h1>

        <div class="divide-y divide-gray-200">
            @forelse($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <p class="py-8 text-gray-500 text-center">No posts found.</p>
            @endforelse
        </div>

        <nav class="mt-8">
            {{ $posts->links() }}
        </nav>
    </div>
</x-layout>
```

### Single Post View
```blade
{{-- resources/views/posts/show.blade.php --}}
<x-layout>
    <article class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900">{{ $post->title }}</h1>

            <div class="mt-4 flex items-center text-gray-600 space-x-4">
                <span>By {{ $post->author->name }}</span>
                <time datetime="{{ $post->published_at->toIso8601String() }}">
                    {{ $post->published_at->format('F j, Y') }}
                </time>
                <span>{{ $post->reading_time }} min read</span>
            </div>

            @if($post->categories->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($post->categories as $category)
                        <a href="{{ $category->url }}"
                           class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full hover:bg-blue-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        <div class="prose prose-lg prose-slate max-w-none
                    prose-headings:font-bold
                    prose-a:text-blue-600 hover:prose-a:underline
                    prose-img:rounded-lg prose-img:shadow-md
                    dark:prose-invert">
            {!! $post->rendered_content !!}
        </div>

        @if($post->tags->isNotEmpty())
            <footer class="mt-12 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Tags</h3>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                        <a href="{{ $tag->url }}"
                           class="text-sm text-gray-600 hover:text-blue-600">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </footer>
        @endif
    </article>
</x-layout>
```

### Category Archive View
```blade
{{-- resources/views/categories/show.blade.php --}}
<x-layout>
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Category</p>
            <h1 class="text-4xl font-bold text-gray-900">{{ $category->name }}</h1>
            @if($category->description)
                <p class="mt-2 text-xl text-gray-600">{{ $category->description }}</p>
            @endif
        </header>

        <div class="divide-y divide-gray-200">
            @forelse($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <p class="py-8 text-gray-500 text-center">No posts in this category.</p>
            @endforelse
        </div>

        <nav class="mt-8">
            {{ $posts->links() }}
        </nav>
    </div>
</x-layout>
```

### Static Page View (Distinct from Posts)
```blade
{{-- resources/views/pages/show.blade.php --}}
<x-layout>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">{{ $page->title }}</h1>

        <div class="prose prose-lg prose-slate max-w-none dark:prose-invert">
            {!! $page->rendered_content !!}
        </div>
    </div>
</x-layout>
```

### CSS Configuration for Tailwind v4
```css
/* resources/css/app.css */
/* Source: Tailwind CSS v4 Documentation */
@import "tailwindcss";
@plugin "@tailwindcss/typography";

/* ShoeMoney brand customization - update with actual brand colors */
@theme {
    /* Primary palette - placeholder values */
    --color-brand-50: #eff6ff;
    --color-brand-100: #dbeafe;
    --color-brand-500: #3b82f6;
    --color-brand-600: #2563eb;
    --color-brand-700: #1d4ed8;
    --color-brand-900: #1e3a8a;
}

/* Custom styles for WordPress content */
.wp-caption {
    @apply my-6;
}

.wp-caption figcaption {
    @apply text-sm text-gray-500 mt-2 text-center italic;
}

.wp-video {
    @apply w-full rounded-lg shadow-md my-6;
}

.wp-more {
    @apply my-8 border-0 h-px bg-gray-200;
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| tailwind.config.js plugins | @plugin directive in CSS | Tailwind v4 (Jan 2025) | Simpler setup, CSS-native |
| prose-indigo/prose-pink colors | prose-gray/slate/zinc/neutral/stone | Typography v0.5 | Grayscale-focused defaults |
| Template inheritance @extends | Component layouts x-layout | Laravel 8+ | Better slot support, composition |
| Controller return view with compact | Named arguments + seo() | Laravel 12 | Cleaner syntax |
| Markdown packages for content | HTML with prose styling | n/a | WordPress stores HTML, not markdown |

**Deprecated/outdated:**
- `@extends`/`@yield`: Still works but component layouts (`<x-layout>`) preferred for new projects
- Tailwind JS config for plugins: v4 uses `@plugin` in CSS
- Livewire v2: v3 has different syntax (SFCs, `$wire`, new lifecycle)

## Open Questions

Things that couldn't be fully resolved:

1. **ShoeMoney Brand Colors**
   - What we know: Historical references mention "clean", "white", "web 2.0 feel", "elegant"
   - What's unclear: Exact hex codes for primary/accent colors
   - Recommendation: Start with neutral palette (slate/blue), allow easy customization via CSS variables. Check Wayback Machine for archived site designs or ask user for brand guidelines.

2. **Remaining 85 Shortcodes**
   - What we know: Top 4 ([more], [caption], [video], [gravityform]) account for majority of usages
   - What's unclear: How to handle remaining 85 unique shortcode types with 300+ combined usages
   - Recommendation: Implement fallback that strips unknown shortcodes. Create shortcode audit report showing which posts are affected. Handle edge cases as they're discovered.

3. **Gravity Forms Replacement**
   - What we know: [gravityform] shortcode references forms that no longer exist
   - What's unclear: Whether forms need to be recreated or just linked to contact page
   - Recommendation: Placeholder with contact link for Phase 3. If forms needed, add form handling in Phase 6 (Admin Panel) or separate phase.

4. **Featured Images/Thumbnails**
   - What we know: WordPress posts often have featured images stored in postmeta
   - What's unclear: Whether featured_image_id was migrated, image URL structure
   - Recommendation: Check database for featured image data. If present, add accessor to Post model. If not, use first image in content or placeholder.

5. **Livewire Installation Timing**
   - What we know: Phase 4 (Comments) requires Livewire for comment submission
   - What's unclear: Whether to install Livewire now or defer
   - Recommendation: Defer Livewire to Phase 4. Standard pagination is sufficient for Phase 3 content display.

## Sources

### Primary (HIGH confidence)
- [Laravel 12.x Blade Templates](https://laravel.com/docs/12.x/blade) - Components, layouts, slots
- [Tailwind CSS v4.0 Release](https://tailwindcss.com/blog/tailwindcss-v4) - v4 features, CSS-native config
- [Tailwind CSS Typography Plugin](https://github.com/tailwindlabs/tailwindcss-typography) - Prose classes, customization
- [Livewire 3.x Components](https://livewire.laravel.com/docs/components) - Component structure
- [Livewire 3.x Pagination](https://livewire.laravel.com/docs/pagination) - Pagination patterns

### Secondary (MEDIUM confidence)
- [ArchTechx Laravel SEO](https://github.com/archtechx/laravel-seo) - Meta tags API
- [Livewire Best Practices](https://github.com/michael-rubel/livewire-best-practices) - Community patterns
- [WordPress Caption Shortcode Codex](https://codex.wordpress.org/Caption_Shortcode) - Shortcode structure
- [mtownsend/read-time](https://github.com/mtownsend5512/read-time) - Reading time package

### Tertiary (LOW confidence)
- [ShoeMoney.com Design History](https://www.shoemoney.com/2007/09/04/new-design-and-new-features-on-shoemoneycom/) - Historical design references
- [vedmant/laravel-shortcodes](https://packagist.org/packages/vedmant/laravel-shortcodes) - Laravel 12 compatibility unverified

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Official documentation verified for Laravel 12, Tailwind v4
- Architecture patterns: HIGH - Based on Laravel 12.x official Blade documentation
- Shortcode handling: MEDIUM - Custom implementation, patterns based on WordPress Codex
- Brand identity: LOW - Could not access live site, using historical references
- Livewire patterns: HIGH - Official Livewire 3.x documentation

**Research date:** 2026-01-24
**Valid until:** 60 days (Laravel Blade stable, Tailwind v4 newly stable)
