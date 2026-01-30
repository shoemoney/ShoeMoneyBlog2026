# Architecture Patterns: Tailwind CSS 4 Design System for ShoeMoneyVelle UI Overhaul

**Domain:** Blog platform UI overhaul (public + admin interfaces)
**Researched:** 2026-01-29
**Overall confidence:** HIGH

## Current State Assessment

The project already uses Tailwind CSS 4 with the CSS-first configuration approach. The existing `resources/css/app.css` has:

- `@import 'tailwindcss'` (v4 syntax)
- `@custom-variant dark (&:where(.dark, .dark *))` (class-based dark mode)
- `@plugin "@tailwindcss/typography"` (v4 plugin syntax)
- `@theme` block with 6 brand tokens (3 light colors, 3 dark colors, 1 font family)
- WordPress content compatibility styles using `@apply`

The build pipeline is v4-native: `@tailwindcss/vite` plugin in `vite.config.js`, no `tailwind.config.js` file.

### Problems to Solve

1. **Incomplete design token system.** Only 6 custom tokens defined. The rest of the UI uses raw Tailwind defaults (`gray-200`, `slate-800`, `blue-600`) scattered across ~27 templates. No semantic tokens for surfaces, borders, or text hierarchy.

2. **Inconsistent color palettes between interfaces.** Public site uses `slate-*` for dark mode surfaces (`dark:bg-slate-900`, `dark:bg-slate-800`). Admin uses `gray-*` exclusively (`bg-gray-800`, `bg-gray-100`). Footer uses `gray-100` with no dark mode support at all.

3. **No admin dark mode.** The admin layout (`admin/layouts/app.blade.php`) does not include the Alpine.js dark mode data binding or the FOUC prevention script. Admin is light-only with hardcoded `bg-gray-100` body and `bg-gray-800` sidebar.

4. **Duplicated styling patterns.** The admin sidebar has the identical `flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors` pattern repeated 10 times. Flash messages (success/error/info) are copy-pasted 3 times in the admin layout.

5. **Mixed color scale usage.** Public templates mix `gray-*` and `slate-*` inconsistently. Footer uses `gray-*`, sidebar-widgets uses `dark:bg-slate-800`, post-card uses `dark:border-gray-700`.

6. **No font loading strategy.** The `@theme` block references `'Instrument Sans'` but there is no `@font-face` declaration or font file in the project. The font either loads from a CDN implicitly or falls back to system fonts silently.

7. **No code syntax highlighting.** WordPress content includes code blocks (`<pre><code>`) from 20 years of technical posts, but these render as unstyled monospace text. The v2.0 context explicitly requires syntax highlighting with a dark background in both modes.

8. **No featured image support.** Post cards are text-only. The post model has no `featured_image` field or accessor. Many legacy posts will never have featured images, so the card design must degrade cleanly.

---

## Recommended Architecture: 3-Layer Semantic Token System

### How @theme Works in Tailwind CSS 4

The `@theme` directive defines design tokens as CSS custom properties that Tailwind uses to generate utility classes. For example, `--color-brand-primary: #1e40af` inside `@theme` creates utilities like `bg-brand-primary`, `text-brand-primary`, `border-brand-primary`, etc.

Key distinctions from Tailwind 3:
- **No `tailwind.config.js`** -- All configuration lives in CSS
- **`@theme` generates utilities** -- Unlike `:root`, `@theme` variables create corresponding utility classes
- **`@theme inline`** -- When referencing other CSS variables, use the `inline` keyword so Tailwind emits `var(--name)` literally rather than resolving the value at compile time
- **Namespaced** -- Variables like `--color-*`, `--font-*`, `--spacing-*`, `--radius-*` each map to their respective utility categories

### The 3-Layer Architecture

```
Layer 1: Primitives    (:root)       Raw hex/oklch values. Never used in templates.
Layer 2: Semantic       (:root + .dark) Intent-based tokens. Swap values in dark mode.
Layer 3: @theme inline               Bridges semantic tokens to Tailwind utilities.
```

#### Layer 1: Primitive Color Palette

Raw color values defined on `:root`. These are the source of truth for all colors but are never referenced directly in Blade templates.

```css
:root {
    /* Brand */
    --raw-brand-primary: #1e40af;
    --raw-brand-accent: #f59e0b;
    --raw-brand-primary-hover: #1e3a8a;
    --raw-brand-accent-hover: #d97706;

    /* Neutral scale (slate-based, resolving the gray/slate inconsistency) */
    --raw-neutral-50: #f8fafc;
    --raw-neutral-100: #f1f5f9;
    --raw-neutral-200: #e2e8f0;
    --raw-neutral-300: #cbd5e1;
    --raw-neutral-400: #94a3b8;
    --raw-neutral-500: #64748b;
    --raw-neutral-600: #475569;
    --raw-neutral-700: #334155;
    --raw-neutral-800: #1e293b;
    --raw-neutral-900: #0f172a;
    --raw-neutral-950: #020617;
    --raw-white: #ffffff;

    /* Feedback */
    --raw-success: #16a34a;
    --raw-error: #dc2626;
    --raw-info: #2563eb;
    --raw-warning: #f59e0b;
}
```

**Why a unified neutral scale:** The current codebase uses both `gray-*` and `slate-*` inconsistently. By defining one neutral scale (slate-based values, which match the existing dark mode choices), all templates converge to a single palette.

#### Layer 2: Semantic Tokens

These define what a color means, not what it looks like. Dark mode is handled entirely here -- templates do not need `dark:` prefixes for semantically-tokenized colors.

```css
:root {
    /* Surfaces */
    --surface-page: var(--raw-white);
    --surface-card: var(--raw-white);
    --surface-elevated: var(--raw-neutral-50);
    --surface-inset: var(--raw-neutral-100);

    /* Borders */
    --border-default: var(--raw-neutral-200);
    --border-muted: var(--raw-neutral-100);

    /* Text */
    --text-heading: var(--raw-neutral-900);
    --text-body: var(--raw-neutral-700);
    --text-secondary: var(--raw-neutral-600);
    --text-muted: var(--raw-neutral-500);
    --text-inverted: var(--raw-white);

    /* Interactive */
    --interactive-primary: var(--raw-brand-primary);
    --interactive-primary-hover: var(--raw-brand-primary-hover);
    --interactive-accent: var(--raw-brand-accent);
    --interactive-accent-hover: var(--raw-brand-accent-hover);

    /* Admin sidebar (stays dark in both modes) */
    --surface-admin-sidebar: var(--raw-neutral-800);
    --surface-admin-sidebar-active: var(--raw-neutral-900);
    --surface-admin-sidebar-hover: var(--raw-neutral-700);
    --text-admin-sidebar: var(--raw-neutral-300);
    --text-admin-sidebar-active: var(--raw-white);
    --border-admin-sidebar: var(--raw-neutral-700);
}

.dark {
    /* Surfaces */
    --surface-page: var(--raw-neutral-900);
    --surface-card: var(--raw-neutral-800);
    --surface-elevated: var(--raw-neutral-800);
    --surface-inset: var(--raw-neutral-950);

    /* Borders */
    --border-default: var(--raw-neutral-700);
    --border-muted: var(--raw-neutral-800);

    /* Text */
    --text-heading: var(--raw-neutral-100);
    --text-body: var(--raw-neutral-300);
    --text-secondary: var(--raw-neutral-400);
    --text-muted: var(--raw-neutral-500);
    --text-inverted: var(--raw-neutral-900);

    /* Admin sidebar (subtle shift in dark mode) */
    --surface-admin-sidebar: var(--raw-neutral-950);
    --surface-admin-sidebar-active: var(--raw-neutral-900);
    --surface-admin-sidebar-hover: var(--raw-neutral-800);
}
```

#### Layer 3: @theme inline

Bridges semantic tokens to Tailwind utility classes. The `inline` keyword is critical -- it tells Tailwind to emit `var(--surface-page)` rather than resolving the variable value at compile time. This means the utility `bg-surface-page` automatically adapts when `.dark` is toggled on the `<html>` element.

```css
@theme inline {
    /* Font */
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif,
        'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';

    /* Brand (static, same in both modes) */
    --color-brand-primary: var(--raw-brand-primary);
    --color-brand-accent: var(--raw-brand-accent);
    --color-brand-primary-hover: var(--raw-brand-primary-hover);
    --color-brand-accent-hover: var(--raw-brand-accent-hover);

    /* Semantic surfaces -> bg-surface-page, bg-surface-card, etc. */
    --color-surface-page: var(--surface-page);
    --color-surface-card: var(--surface-card);
    --color-surface-elevated: var(--surface-elevated);
    --color-surface-inset: var(--surface-inset);

    /* Semantic borders -> border-default, border-muted */
    --color-border-default: var(--border-default);
    --color-border-muted: var(--border-muted);

    /* Semantic text -> text-heading, text-body, text-secondary, text-muted */
    --color-text-heading: var(--text-heading);
    --color-text-body: var(--text-body);
    --color-text-secondary: var(--text-secondary);
    --color-text-muted: var(--text-muted);
    --color-text-inverted: var(--text-inverted);

    /* Interactive -> text-interactive-primary, bg-interactive-primary */
    --color-interactive-primary: var(--interactive-primary);
    --color-interactive-primary-hover: var(--interactive-primary-hover);
    --color-interactive-accent: var(--interactive-accent);
    --color-interactive-accent-hover: var(--interactive-accent-hover);

    /* Feedback */
    --color-feedback-success: var(--raw-success);
    --color-feedback-error: var(--raw-error);
    --color-feedback-info: var(--raw-info);
    --color-feedback-warning: var(--raw-warning);

    /* Admin sidebar */
    --color-admin-sidebar: var(--surface-admin-sidebar);
    --color-admin-sidebar-active: var(--surface-admin-sidebar-active);
    --color-admin-sidebar-hover: var(--surface-admin-sidebar-hover);
    --color-admin-sidebar-text: var(--text-admin-sidebar);
    --color-admin-sidebar-text-active: var(--text-admin-sidebar-active);
    --color-admin-sidebar-border: var(--border-admin-sidebar);
}
```

### Template Impact: Before and After

**Before** (current -- dual classes for every color):
```blade
<body class="bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100">
<article class="border-b border-gray-200 dark:border-gray-700 py-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
    <p class="mt-3 text-gray-600 dark:text-gray-400">
```

**After** (semantic tokens -- single class, automatic dark mode):
```blade
<body class="bg-surface-page text-text-body">
<article class="border-b border-default py-6">
    <h2 class="text-2xl font-bold text-heading">
    <p class="mt-3 text-secondary">
```

**Impact:** Eliminates approximately 60-70% of `dark:` prefixes across the codebase. The remaining `dark:` usage is limited to:
- `prose dark:prose-invert` (typography plugin variant)
- One-off brand color adjustments
- Opacity or state changes that differ between modes
- Third-party component overrides

---

## Font Loading Architecture

### Strategy: Self-Hosted Variable Fonts via @font-face

The v2.0 context calls for a geometric sans-serif display font and readable body font. Regardless of which fonts are chosen (Space Grotesk, DM Sans, Poppins for display; Inter for body), the loading architecture is the same.

**Why self-hosted over Google CDN:**
- Eliminates third-party DNS lookup (saves 50-100ms on first load)
- No privacy/GDPR concerns from Google Fonts tracking
- Full control over font-display strategy
- Works offline (service worker friendly)
- Variable fonts reduce total file count and size

### File Structure

```
public/
  fonts/
    DisplayFont-Variable.woff2    (~30-50KB)
    BodyFont-Variable.woff2       (~40-60KB)
```

Only `.woff2` format is needed -- it has 97%+ browser support. No need for `.woff`, `.ttf`, or `.eot` fallbacks.

### CSS Integration in app.css

The `@font-face` declarations must come BEFORE the `@theme` block so the font families are registered when `@theme` references them.

```css
/* === Font Loading === */
@font-face {
    font-family: 'Display Font';
    src: url('/fonts/DisplayFont-Variable.woff2') format('woff2');
    font-weight: 300 700;
    font-display: swap;
}

@font-face {
    font-family: 'Body Font';
    src: url('/fonts/BodyFont-Variable.woff2') format('woff2');
    font-weight: 100 900;
    font-display: swap;
}

/* === Tailwind === */
@import 'tailwindcss';

/* ... rest of config ... */

@theme inline {
    --font-display: 'Display Font', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Body Font', ui-sans-serif, system-ui, sans-serif;
    /* ... */
}
```

**Key details:**
- `font-display: swap` shows system font immediately, swaps when custom font loads. This prevents invisible text (FOIT) at the cost of a brief flash of unstyled text (FOUT). For a blog, FOUT is acceptable; FOIT is not.
- `font-weight: 300 700` (or `100 900`) declares the variable font weight range. A single file serves all weights.
- The `@font-face` block should appear before `@import 'tailwindcss'` to ensure the font is registered before any utility classes reference it.

### Usage in Templates

After `@theme` defines `--font-display` and `--font-body`, Tailwind generates `font-display` and `font-body` utility classes:

```blade
{{-- Headings use display font --}}
<h1 class="font-display text-4xl font-bold text-heading">{{ $post->title }}</h1>

{{-- Body text uses body font (set as default on <body>) --}}
<body class="font-body bg-surface-page text-text-body">
```

### Preloading Critical Fonts

Add preload hints in `layout.blade.php` `<head>` for the display font (used above the fold in headings). The body font can load normally since the system font fallback is barely noticeable.

```blade
<link rel="preload" href="/fonts/DisplayFont-Variable.woff2" as="font" type="font/woff2" crossorigin>
```

Only preload one font -- preloading both wastes bandwidth on the body font that the system fallback handles fine.

### Fallback Chain

The `@theme` font stacks include `ui-sans-serif, system-ui, sans-serif` as fallbacks. This means:
- On macOS: falls back to SF Pro (system-ui)
- On Windows: falls back to Segoe UI (system-ui)
- On Linux: falls back to whatever sans-serif is configured

These system fonts are close enough in metrics to most geometric sans-serifs that layout shift during `font-display: swap` is minimal.

---

## Code Syntax Highlighting Architecture

### Requirement

The v2.0 context states: "Code blocks: syntax highlighted with dark background, even in light mode." This applies to `<pre><code>` elements in WordPress post content rendered via the `rendered_content` accessor.

### Recommended Approach: Highlight.js (Client-Side)

**Why client-side, not server-side:**
- Post content is stored as raw HTML in the database. Server-side highlighting would require parsing HTML, extracting code blocks, highlighting them, and re-injecting -- fragile with 20 years of varied HTML structures.
- Client-side highlighting targets `<pre><code>` elements in the DOM after render, which is exactly how WordPress content is structured.
- No build step, no content processing changes, no model accessor modifications.

**Why Highlight.js over alternatives:**
- **Highlight.js**: Auto-detects language, 49 common languages bundled, 189 total. No configuration needed for basic use. Works by targeting `<pre><code>` elements. ~37KB gzipped for core + common languages.
- **Prism.js**: Requires language class on code blocks (`class="language-javascript"`). WordPress content from 20 years ago will NOT have these classes. Would require server-side preprocessing to add them.
- **Shiki**: Server-side (Node.js). Excellent for build-time highlighting in static sites. Wrong tool for database-driven content rendered at request time in PHP.

**Highlight.js wins because it auto-detects language without requiring class attributes on code blocks.** This is critical for legacy WordPress content.

### Integration Pattern

#### 1. Install via npm

```bash
npm install highlight.js
```

#### 2. Import in app.js

```javascript
// resources/js/app.js
import './bootstrap';
import hljs from 'highlight.js/lib/core';

// Register only the languages likely in a tech/money blog
import javascript from 'highlight.js/lib/languages/javascript';
import php from 'highlight.js/lib/languages/php';
import css from 'highlight.js/lib/languages/css';
import xml from 'highlight.js/lib/languages/xml'; // covers HTML
import bash from 'highlight.js/lib/languages/bash';
import json from 'highlight.js/lib/languages/json';
import sql from 'highlight.js/lib/languages/sql';
import python from 'highlight.js/lib/languages/python';

hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('php', php);
hljs.registerLanguage('css', css);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('json', json);
hljs.registerLanguage('sql', sql);
hljs.registerLanguage('python', python);

// Highlight all code blocks on page load
hljs.highlightAll();

// Re-highlight after Livewire navigates (wire:navigate)
document.addEventListener('livewire:navigated', () => {
    hljs.highlightAll();
});
```

Importing individual languages instead of the full bundle reduces JS payload from ~37KB to ~15KB gzipped.

#### 3. Theme CSS in app.css

Highlight.js themes are CSS files. Import the dark theme directly:

```css
/* Code syntax highlighting -- always dark background */
@import 'highlight.js/styles/github-dark.css';
```

Or for more control, define code block styling with semantic tokens:

```css
/* Code block container */
pre code.hljs {
    background: var(--raw-neutral-900);
    color: var(--raw-neutral-100);
    border-radius: var(--radius-lg, 0.75rem);
    padding: 1.5rem;
    font-size: 0.875rem;
    line-height: 1.7;
    overflow-x: auto;
}

/* Inline code (not highlighted by hljs) */
:not(pre) > code {
    background: var(--surface-inset);
    color: var(--text-heading);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}
```

The dark background for code blocks is constant in both light and dark mode -- matching the v2.0 context requirement. Only inline code adapts to the current theme.

#### 4. Livewire Navigation Compatibility

The `livewire:navigated` event listener in step 2 handles wire:navigate page transitions. Without this, code blocks on subsequent pages would not be highlighted after SPA-style navigation.

### Code Block Sizing Within Content Width

Code blocks inside `.prose` will respect `max-w-none` already set on the prose container. For long lines, `overflow-x: auto` adds a horizontal scrollbar within the code block without breaking the page layout. This is the standard pattern.

---

## Image Placeholder and Fallback Architecture

### The Problem

The v2.0 context states: "Featured images displayed on cards when available, clean fallback when not" and "20+ years of content means many posts won't have featured images -- card design must work cleanly without them."

The current `Post` model has no `featured_image` field. The database schema (from v1.0 migration) does not include a featured image column. This means:

1. A `featured_image` column or accessor needs to be added to the Post model
2. Most legacy posts (2,500+) will have `null` featured images
3. The card design must look intentional -- not broken -- without an image

### Strategy: Text-First Cards with Optional Image Enhancement

**Do NOT design image-first cards with a placeholder fallback.** When 80%+ of posts have no image, a placeholder (gray box, generic icon, gradient) appearing on most cards looks worse than no image at all. The card must look complete and intentional with text only.

**Card Structure:**

```
WITH IMAGE:                          WITHOUT IMAGE:
+----------------------------------+ +----------------------------------+
| [Featured Image - full width]    | |                                  |
| [aspect-ratio: 16/9]            | | Post Title in Bold               |
|                                  | | Display Font                     |
+----------------------------------+ |                                  |
| Post Title in Bold               | | Two lines of excerpt text that   |
| Display Font                     | | gives a taste of content...      |
|                                  | |                                  |
| Two lines of excerpt text that   | +----------------------------------+
| gives a taste of content...      |
|                                  |
+----------------------------------+
```

The text-only card is the DEFAULT design. The image is an enhancement when present, not the expected state.

### Blade Component Pattern

```blade
{{-- post-card.blade.php --}}
@props(['post'])

<article class="bg-surface-card border border-default rounded-xl overflow-hidden
               hover:shadow-lg transition-shadow">
    @if($post->featured_image)
        <div class="aspect-video overflow-hidden">
            <img src="{{ $post->featured_image }}"
                 alt="{{ $post->title }}"
                 class="w-full h-full object-cover"
                 loading="lazy"
                 onerror="this.parentElement.remove()">
        </div>
    @endif

    <div class="p-6">
        <a href="{{ $post->url }}" class="group">
            <h2 class="font-display text-xl font-bold text-heading
                       group-hover:text-interactive-primary transition-colors">
                {{ $post->title }}
            </h2>
        </a>

        <p class="mt-3 text-secondary line-clamp-2">
            {{ Str::limit(strip_tags($post->excerpt ?: $post->content), 160) }}
        </p>
    </div>
</article>
```

**Key details:**
- `@if($post->featured_image)` -- The image block only renders when an image exists. No placeholder, no empty space.
- `onerror="this.parentElement.remove()"` -- If the image URL is broken (common with migrated WordPress content where `/wp-content/uploads/` paths may be invalid), the entire image container is removed from the DOM. The card degrades to the text-only variant.
- `loading="lazy"` -- Images below the fold load on demand.
- `aspect-video` (16:9) -- Consistent image proportions prevent layout shift.
- `object-cover` -- Images crop to fill the container regardless of source dimensions.
- `line-clamp-2` -- Limits excerpt to 2 lines. Keeps cards uniform height in a grid.

### Grid Layout for Mixed Cards

When some cards have images and some do not, a CSS grid handles mixed heights:

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($posts as $post)
        <x-post-card :post="$post" />
    @endforeach
</div>
```

CSS Grid auto-rows handle variable card heights naturally. Cards with images are taller; cards without are shorter. This looks intentional in a grid layout -- the visual variety is a feature, not a bug.

If uniform height is preferred, add `class="h-full"` to the article and `class="flex-1"` to the text container, which stretches text-only cards to match image cards in the same row.

### Featured Image Data Strategy

Two approaches for getting featured image data:

**Option A: Extract from WordPress content (zero migration)**
Add an accessor to the Post model that extracts the first `<img>` from content:

```php
// app/Models/Post.php
public function getFeaturedImageAttribute(): ?string
{
    if ($this->attributes['featured_image'] ?? null) {
        return $this->attributes['featured_image'];
    }

    // Fallback: extract first image from content
    if (preg_match('/<img[^>]+src=["\']([^"\']+)/', $this->content, $matches)) {
        return $matches[1];
    }

    return null;
}
```

**Option B: Add featured_image column via migration**
```php
Schema::table('posts', function (Blueprint $table) {
    $table->string('featured_image')->nullable()->after('excerpt');
});
```

Then populate it from WordPress postmeta if the data exists, or leave null for manual curation.

**Recommendation: Option B (add column) with Option A (accessor fallback).** This gives clean database storage for new posts while gracefully handling legacy content. The accessor checks the column first, then falls back to content extraction.

---

## Component Boundaries

### Full File Inventory

| Component | File | Type | Has Dark Mode? | Refactor Work |
|-----------|------|------|----------------|---------------|
| Public Layout | `components/layout.blade.php` | Blade | Yes | Token swap on body, container; add font preload |
| Navigation | `components/navigation.blade.php` | Blade | Yes | Token swap on header, links, tagline; add mobile hamburger |
| Footer | `components/footer.blade.php` | Blade | **NO** | Add dark mode + token swap |
| Post Card | `components/post-card.blade.php` | Blade | Yes | Complete redesign: card-based with optional image |
| Sidebar Widgets | `components/sidebar-widgets.blade.php` | Blade | Yes | Token swap on cards, borders, text |
| Theme Toggle | `components/theme-toggle.blade.php` | Blade | Yes | Minor interactive state tokens |
| Admin Layout | `components/admin/layouts/app.blade.php` | Blade | **NO** | Add dark mode + tokens + extract flash messages |
| Admin Sidebar | `components/admin/sidebar.blade.php` | Blade | Partial | Already dark; needs token swap + extract link component |
| Search Bar | `livewire/search/search-bar.blade.php` | Livewire | Likely | Token swap |
| Post Index | `posts/index.blade.php` | Blade | Likely | Convert to card grid layout |
| Post Show | `posts/show.blade.php` | Blade | Likely | Token swap; content width constraint; code highlighting inherits from JS |
| Admin Dashboard | `livewire/admin/dashboard.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Admin Posts (3) | `livewire/admin/posts/*.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Admin Pages (3) | `livewire/admin/pages/*.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Admin Comments | `livewire/admin/comments/*.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Admin Taxonomies (2) | `livewire/admin/taxonomies/*.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Admin Users (2) | `livewire/admin/users/*.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Admin Settings (3) | `livewire/admin/settings/*.blade.php` | Livewire | **NO** | Add dark mode tokens |
| Comment System (3) | `livewire/comments/*.blade.php` | Livewire | Likely | Token swap |

**Total files to touch:** ~27 Blade/Livewire templates + 1 CSS file + 1 JS file.

### New Components Needed

| Component | Purpose | Why New (not refactor) |
|-----------|---------|----------------------|
| `components/admin/sidebar-link.blade.php` | Extracted repeated sidebar link pattern | DRY: same pattern repeated 10 times |
| `components/admin/flash-messages.blade.php` | Extracted flash message display | DRY: 3 identical blocks in admin layout |

No other new components needed. The v2.0 context is clear: "No new features or capabilities -- purely visual and UX improvements to existing functionality." The existing component structure is sound; it just needs new styling.

### Component Tree

```
resources/css/app.css  <-- @font-face + @theme token definitions (single source of truth)
resources/js/app.js    <-- highlight.js initialization
    |
    +--> components/layout.blade.php  (public shell)
    |       +--> components/navigation.blade.php
    |       +--> [page content via $slot]
    |       |       +--> components/post-card.blade.php  (reusable, redesigned)
    |       |       +--> livewire/comments/*  (Livewire)
    |       +--> components/sidebar-widgets.blade.php
    |       +--> components/footer.blade.php
    |
    +--> components/admin/layouts/app.blade.php  (admin shell)
            +--> components/admin/sidebar.blade.php
            |       +--> components/admin/sidebar-link.blade.php  (NEW, extracted)
            +--> components/admin/flash-messages.blade.php  (NEW, extracted)
            +--> [Livewire full-page component via $slot]
                    +--> 15 admin Livewire views
```

Both layouts reference the same CSS via `@vite(['resources/css/app.css', ...])`.

---

## Public vs Admin Design Systems

**Recommendation: One token system, two surface contexts.**

Do NOT create separate CSS files or separate `@theme` blocks. Both interfaces already share the same Vite entry point. The semantic token system handles the distinction:

- **Public site** uses: `bg-surface-page`, `bg-surface-card`, `text-heading`, `border-default`
- **Admin content area** uses: the same tokens (white cards on light background, dark cards on dark background)
- **Admin sidebar** uses: admin-specific tokens (`bg-admin-sidebar`, `text-admin-sidebar`) that stay dark in both modes

The admin sidebar's perpetually-dark appearance is an intentional design pattern shared by WordPress, GitHub, Laravel Nova, and most admin interfaces. The sidebar tokens shift subtly in dark mode (darker dark) rather than inverting.

---

## Patterns to Follow

### Pattern 1: Blade Component Extraction for Repeated Patterns

The admin sidebar repeats this pattern 10 times:
```blade
<a href="{{ route($route) }}"
   wire:navigate
   class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
          {{ request()->routeIs($route) ? 'bg-admin-sidebar-active text-admin-sidebar-text-active'
                                        : 'text-admin-sidebar-text hover:bg-admin-sidebar-hover hover:text-text-inverted' }}">
    <svg class="mr-3 h-5 w-5" ...>...</svg>
    Label
</a>
```

Extract to `components/admin/sidebar-link.blade.php`:
```blade
@props(['route', 'label', 'activePattern' => null])

@php
    $isActive = $activePattern
        ? request()->routeIs($activePattern)
        : request()->routeIs($route . '*');
@endphp

<a href="{{ route($route) }}"
   wire:navigate
   class="flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors
          {{ $isActive ? 'bg-admin-sidebar-active text-admin-sidebar-text-active'
                       : 'text-admin-sidebar-text hover:bg-admin-sidebar-hover hover:text-text-inverted' }}">
    <span class="mr-3 h-5 w-5">{{ $slot }}</span>
    {{ $label }}
</a>
```

Similarly extract: flash messages (3 blocks -> 1 component), widget card containers.

### Pattern 2: Admin Dark Mode Integration

The admin layout needs 3 additions to support dark mode:

1. **Alpine.js data binding on `<html>`** (same pattern as public layout)
2. **FOUC prevention script in `<head>`** (same pattern as public layout)
3. **Token-based body class** (`bg-surface-elevated` instead of `bg-gray-100`)

The admin layout currently lacks these. After adding them, semantic tokens handle the rest automatically.

### Pattern 3: CSS File Organization

Structure `app.css` with clear sections:

```css
/* 1. Font face declarations */
@font-face { ... }

/* 2. Tailwind import */
@import 'tailwindcss';

/* 3. Variants */
@custom-variant dark (&:where(.dark, .dark *));

/* 4. Plugins */
@plugin "@tailwindcss/typography";

/* 5. Source detection */
@source '...';

/* 6. Primitive palette (:root raw values) */
:root { --raw-*: ...; }

/* 7. Semantic tokens (:root light + .dark overrides) */
:root { --surface-*: ...; --text-*: ...; --border-*: ...; }
.dark { /* overrides */ }

/* 8. @theme inline -- utility generation */
@theme inline { --color-*: ...; --font-*: ...; }

/* 9. Code syntax highlighting theme override */
pre code.hljs { ... }

/* 10. WordPress content compatibility */
.wp-caption { ... }

/* 11. Alpine.js utilities */
[x-cloak] { display: none !important; }
```

### Pattern 4: Preserving Backward Compatibility During Migration

The existing `--color-brand-primary` and other tokens are already used in templates (`text-brand-primary`, `hover:text-brand-accent`). The new `@theme inline` block must include these same token names to avoid breaking existing usage. New semantic tokens are additive -- templates can be migrated incrementally.

```css
@theme inline {
    /* Keep existing tokens (backward compatible) */
    --color-brand-primary: var(--raw-brand-primary);
    --color-brand-accent: var(--raw-brand-accent);

    /* Add new semantic tokens (incremental adoption) */
    --color-surface-page: var(--surface-page);
    /* ... */
}
```

This means Phase 1 (token system) adds tokens without changing any templates. Templates are updated in subsequent phases.

---

## Anti-Patterns to Avoid

### Anti-Pattern 1: Using @apply to Create Component Classes

**What:** Creating `.btn-primary`, `.card`, `.nav-link` classes in CSS with `@apply`.

**Why bad:** Tailwind's creator explicitly discourages this. It creates a shadow abstraction layer that competes with Blade components. Styles become harder to trace -- you check the template, see a class name, then have to find the CSS definition.

**Instead:** Use Blade components with utility classes inline. Blade components ARE the abstraction layer in Laravel.

**Exception:** WordPress content styles (`.wp-caption`, `.wp-video`) genuinely need `@apply` because they target HTML rendered from database content that cannot have utility classes added. Code highlighting styles (`pre code.hljs`) fall into this same category.

### Anti-Pattern 2: Splitting CSS into Multiple Files

**What:** Creating `admin.css` and `public.css` as separate entry points.

**Why bad:** Tailwind CSS 4 expects a single entry point for content detection. Splitting creates potential duplicate utility generation and build complexity. Both layouts already `@vite` the same CSS file.

**Instead:** One `app.css` with namespaced tokens (`--color-admin-sidebar-*`).

### Anti-Pattern 3: Redundant dark: Prefixes with Semantic Tokens

**What:** Writing `bg-surface-page dark:bg-surface-page` or defining separate dark token names.

**Why bad:** The entire point of the 3-layer architecture is that `bg-surface-page` already outputs `var(--surface-page)`, which swaps automatically via the `.dark` CSS rule. Adding `dark:` on top is redundant.

**Instead:** Only use `dark:` for cases where semantic tokens cannot cover the need.

### Anti-Pattern 4: Over-Tokenizing

**What:** Creating 50+ semantic tokens for every possible color usage.

**Why bad:** Tokens add indirection. A color used once in one template does not need a token -- that is overhead, not abstraction.

**Instead:** Aim for 20-30 semantic tokens covering: surfaces (4), borders (2), text hierarchy (5), interactive states (4), feedback (4), admin sidebar (6). One-off colors stay as raw Tailwind utilities (e.g., `text-yellow-400` for the sun icon).

### Anti-Pattern 5: Making the Admin Sidebar Light

**What:** Making the sidebar follow the same light/dark scheme as the content area.

**Why bad:** Dark sidebars with light content areas are a well-established admin UI pattern. Changing this creates visual regression and user confusion.

**Instead:** Keep the sidebar perpetually dark. Define admin sidebar tokens that stay dark in both modes, with subtle darkening in dark mode.

### Anti-Pattern 6: Image Placeholders on Text-First Cards

**What:** Using generic placeholder images (gray boxes, gradient fills, SVG icons) when a post has no featured image.

**Why bad:** When 80%+ of posts have no featured image, the placeholder becomes the dominant visual element. A sea of identical gray boxes or generic icons looks worse than no images at all. It screams "template" and "incomplete."

**Instead:** Design cards that look complete and intentional without images. The image is an enhancement when present, not the expected state. The `@if($post->featured_image)` conditional renders the image block only when one exists.

### Anti-Pattern 7: Prism.js for Legacy WordPress Content

**What:** Using Prism.js for code syntax highlighting.

**Why bad:** Prism.js requires `class="language-xxx"` on code elements to detect the language. WordPress content from the past 20 years will not have these classes. This forces server-side preprocessing of every post's HTML to add language hints.

**Instead:** Use Highlight.js, which auto-detects language without class attributes. It works on raw `<pre><code>` blocks as-is.

---

## Recommended Build Order for the Overhaul

Each phase leaves the site fully functional. No phase breaks existing behavior.

### Phase 1: Foundation (CSS + JS Infrastructure Only)

**Files touched:** `resources/css/app.css`, `resources/js/app.js`, `public/fonts/*` (new)

1. Download and place font files in `public/fonts/`
2. Add `@font-face` declarations to `app.css`
3. Expand `@theme` with the full 3-layer token system
4. Keep ALL existing tokens to maintain backward compatibility
5. Add highlight.js imports and initialization to `app.js`
6. Import highlight.js dark theme in `app.css`
7. Update WordPress content styles to use semantic tokens where applicable
8. Verify: `npm run build` succeeds, site looks identical (no template changes yet)

**Risk:** LOW. CSS/JS infrastructure changes only. All existing Tailwind classes still work. Highlight.js silently initializes on code blocks that exist; no-ops on pages without them.

### Phase 2: Public Layout Shell

**Files touched:** `layout.blade.php`, `navigation.blade.php`, `footer.blade.php`, `theme-toggle.blade.php`

1. Replace raw color classes with semantic tokens in the 4 layout shell files
2. Add font preload hint to `layout.blade.php` head
3. Set `font-body` as default on `<body>`, `font-display` on headings
4. Add dark mode support to footer (currently missing)
5. Add mobile hamburger menu to navigation
6. Resolve gray/slate inconsistency (everything now uses semantic tokens)
7. Verify: Public site uses new fonts and tokens; looks correct in both modes

**Risk:** LOW-MEDIUM. These 4 files frame every public page. Visual regression is immediately visible and easy to catch.

### Phase 3: Public Content Components

**Files touched:** `post-card.blade.php`, `sidebar-widgets.blade.php`, `posts/index.blade.php`, `posts/show.blade.php`, `livewire/comments/*.blade.php`, `livewire/search/search-bar.blade.php`

1. Redesign `post-card.blade.php` as card-based with optional featured image
2. Convert `posts/index.blade.php` from divided list to card grid
3. Add content width constraint to `posts/show.blade.php`
4. Swap all raw color classes in public content templates
5. Code syntax highlighting works automatically (highlight.js from Phase 1)
6. Verify: All public pages render correctly in both light and dark modes

**Risk:** MEDIUM. Touches user-facing content display. Requires visual verification across multiple page types. Post card redesign is the most significant structural change.

### Phase 4: Admin Infrastructure

**Files touched:** `admin/layouts/app.blade.php`, `admin/sidebar.blade.php` + 2 new extracted components

1. Add dark mode support to admin layout (Alpine.js data, FOUC script)
2. Extract `admin/sidebar-link.blade.php` component
3. Extract `admin/flash-messages.blade.php` component
4. Swap admin layout and sidebar to semantic tokens
5. Verify: Admin works in both light and dark modes

**Risk:** MEDIUM. Admin-only (not public-facing), but the primary editing interface. Test all navigation links still work after component extraction.

### Phase 5: Admin Livewire Components

**Files touched:** All 15 admin Livewire view files

1. Swap raw color classes for semantic tokens in all admin views
2. Apply consistent card/table/form styling via tokens
3. Formulaic changes -- each file follows the same pattern

**Risk:** LOW per file. Highest volume phase but most repetitive. Admin-only.

### Phase 6: Verification and Cleanup

1. Search codebase for remaining hardcoded `gray-*`, `slate-*`, `blue-*` classes
2. Verify all remaining `dark:` prefixes are intentional (not redundant)
3. Cross-browser test both modes
4. Verify WordPress content rendering in both modes (especially code blocks)
5. Verify all Livewire interactions survive class changes
6. Remove deprecated tokens if old `--color-dark-bg`, `--color-dark-surface`, `--color-dark-text` are no longer referenced
7. Test image fallback behavior (cards with and without featured images)

---

## Scalability Considerations

| Concern | Current State | After Overhaul |
|---------|--------------|----------------|
| CSS bundle size | ~10KB (Tailwind tree-shakes) | Marginally larger (~12KB) from CSS variables; still tiny |
| JS bundle size | ~0KB custom | +15KB gzipped (highlight.js core + 8 languages) |
| Font load | Unknown (Instrument Sans, no @font-face found) | ~80KB (2 variable fonts, self-hosted, preloaded) |
| Token maintenance | 6 tokens, incomplete | 25-30 tokens, complete system; change primitives to rebrand entire site |
| Dark mode coverage | Public only, inconsistent | Full coverage, both interfaces, consistent |
| Brand changes | Requires finding/replacing across ~27 files | Change 3-5 primitive values in `:root`; entire site updates |
| Future themes | Not supported | Architecture supports adding `.theme-*` classes alongside `.dark` |
| New component creation | Copy-paste from existing, hope for consistency | Use semantic tokens from the start; automatic dark mode |
| Code highlighting | None | Automatic for any `<pre><code>` in post content |
| Featured images | Not supported | Graceful progressive enhancement; works with or without |

---

## Sources

- [Tailwind CSS v4.0 Release Blog](https://tailwindcss.com/blog/tailwindcss-v4) -- HIGH confidence (official)
- [Tailwind CSS Theme Variables Documentation](https://tailwindcss.com/docs/theme) -- HIGH confidence (official, fetched and verified; covers @theme, @theme inline, namespaces, overriding defaults)
- [Tailwind CSS Dark Mode Documentation](https://tailwindcss.com/docs/dark-mode) -- HIGH confidence (official, fetched and verified; covers @custom-variant, class-based toggling)
- [Tailwind CSS Functions and Directives](https://tailwindcss.com/docs/functions-and-directives) -- HIGH confidence (official)
- [Tailwind CSS Adding Custom Styles](https://tailwindcss.com/docs/adding-custom-styles) -- HIGH confidence (official)
- [Highlight.js Documentation](https://highlightjs.org/) -- HIGH confidence (official; auto-detection, language registration, DOM API)
- [Highlight.js npm package](https://www.npmjs.com/package/highlight.js) -- HIGH confidence (verified package exists, current version)
- [GitHub Discussion: Dark Mode CSS Variables in v4](https://github.com/tailwindlabs/tailwindcss/discussions/15083) -- MEDIUM confidence (official repo, community patterns for @theme inline with dark mode)
- [GitHub Discussion: Dark-Mode-Specific CSS Variables](https://github.com/tailwindlabs/tailwindcss/discussions/16730) -- MEDIUM confidence (official repo, confirms :root + .dark pattern as recommended workaround)
- [Building a Production Design System with Tailwind CSS v4](https://dev.to/saswatapal/building-a-production-design-system-with-tailwind-css-v4-1d9e) -- MEDIUM confidence (community, aligns with official docs)
- [Tailwind CSS Best Practices 2025-2026](https://www.frontendtools.tech/blog/tailwind-css-best-practices-design-system-patterns) -- MEDIUM confidence (community best practices)
- [Multi-Theme System with Tailwind CSS v4](https://medium.com/render-beyond/build-a-flawless-multi-theme-ui-using-new-tailwind-css-v4-react-dca2b3c95510) -- LOW confidence (community blog, React-focused but CSS patterns transfer)
