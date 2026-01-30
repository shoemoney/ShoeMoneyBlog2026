# Technology Stack: UI Overhaul

**Project:** ShoeMoney Blog UI Overhaul
**Researched:** 2026-01-29
**Scope:** Design tooling additions only (application framework already validated)
**Overall Confidence:** HIGH

## Current Stack (Already in Place -- DO NOT CHANGE)

| Technology | Version | Status |
|------------|---------|--------|
| Laravel | ^12.0 | Locked |
| Livewire | ^4.0 | Locked |
| Tailwind CSS | ^4.0.0 | Locked |
| @tailwindcss/typography | ^0.5.19 | Locked |
| Alpine.js | (bundled with Livewire 4) | Locked |
| Vite | ^7.0.7 | Locked |
| PHP | 8.5 (runtime) | Locked |

The existing `app.css` already uses Tailwind CSS 4's `@theme` directive with brand color tokens, `@custom-variant dark` for class-based dark mode, and `@plugin "@tailwindcss/typography"`. This is the correct CSS-first approach -- no `tailwind.config.js` needed.

---

## Recommended Additions

### 1. Icons: blade-ui-kit/blade-heroicons

| Detail | Value |
|--------|-------|
| Package | `blade-ui-kit/blade-heroicons` |
| Version | ^2.4.0 (ships Heroicons v2.2.0) |
| Install | `composer require blade-ui-kit/blade-heroicons` |
| Confidence | HIGH (verified via Packagist, actively maintained) |

**Why Heroicons:** They are the Tailwind ecosystem's native icon set, designed by the same team. They match Tailwind's design language perfectly. Usage is dead simple in Blade: `<x-heroicon-o-arrow-right class="w-5 h-5" />`. Three styles (outline, solid, mini) cover all UI needs -- navigation, admin actions, content indicators.

**Why NOT Lucide or Tabler:** Lucide has a broader set but adds a second design language. For a personal blog (not a SaaS dashboard), Heroicons' 300+ icons are more than sufficient. Consistency with Tailwind's visual DNA matters more than icon count.

### 2. Animations: @formkit/auto-animate (npm)

| Detail | Value |
|--------|-------|
| Package | `@formkit/auto-animate` |
| Version | ^0.9.0 |
| Size | 3.28 KB gzipped, zero dependencies |
| Install | `npm install @formkit/auto-animate` |
| Confidence | MEDIUM (community-proven with Livewire, not official integration) |

**Why AutoAnimate:** It provides smooth add/remove/reorder animations for DOM lists with a single function call -- exactly what Livewire list rendering needs. It works by observing a parent element and animating its direct children. This means post lists, admin tables, and sidebar widgets get fluid transitions without writing CSS keyframes per component.

**Why NOT Alpine.js transitions alone:** Alpine's `x-transition` handles show/hide (modals, dropdowns, flash messages) perfectly and is already available. But Alpine has no solution for list reordering or content swap animations. AutoAnimate fills that specific gap.

**Why NOT GSAP or Motion One:** Overkill. GSAP is 23KB+ and designed for complex timeline animations. This is a blog, not a marketing microsite. AutoAnimate's zero-config philosophy matches the project's simplicity goals.

**Integration pattern:**
```javascript
// In Alpine component or inline script
import autoAnimate from '@formkit/auto-animate';
// Apply to any parent element
autoAnimate(document.getElementById('post-list'));
```

For Livewire pages, wrap in an Alpine directive or apply after `Livewire.hook('morph.updated')`.

### 3. Typography: Self-Hosted Variable Fonts (no npm package -- CSS only)

| Detail | Value |
|--------|-------|
| Method | Self-hosted variable `.woff2` files in `/public/fonts/` |
| Display font | **Space Grotesk** (variable, 300-700 weight, ~30KB woff2) |
| Body font | **Inter** (variable, 100-900 weight, ~50KB woff2) |
| Mono font | **JetBrains Mono** (variable, 100-800 weight, ~45KB woff2) |
| Install | Download woff2 from Google Fonts / GitHub, place in `/public/fonts/` |
| Confidence | HIGH (proven fonts, well-documented self-hosting path) |

**Why Space Grotesk for headings:** It has geometric precision with personality -- slightly quirky letterforms that feel energetic without being gimmicky. The variable weight axis (300-700) lets you go ultra-bold for hero headings and medium for subheads from a single file. It reads "tech-savvy entrepreneur" not "generic blog." Based on Space Mono but proportional, it brings a futuristic feel to classic grotesque typography.

**Why Inter for body:** Highest readability scores for long-form content, designed specifically for screens, variable font with full weight range (100-900). The high x-height keeps body text crisp at 16-18px. Used by Linear, Vercel, and countless other tech platforms.

**Why JetBrains Mono for code blocks:** Purpose-built for developer content. Ligatures for common code operators (`=>`, `!==`, `>=`), excellent readability at small sizes, and it visually signals "code" immediately. The blog contains 20+ years of content likely including code snippets -- a dedicated mono font elevates that content.

**Why self-host over Google CDN:** Self-hosted fonts eliminate third-party DNS lookups (faster LCP), allow full cache control, comply with GDPR privacy regulations, and work offline. Variable woff2 files are the most efficient format -- one file per family covers all weights. Total additional weight: ~125KB for all three families.

**Why NOT Fontsource npm packages:** Fontsource (`@fontsource-variable/space-grotesk`, etc.) works well in React/Next.js bundler pipelines. For Laravel + Vite, directly placing woff2 files in `/public/fonts/` with manual `@font-face` declarations is simpler, avoids node_modules bloat, and gives explicit control. The Fontsource CSS import approach adds unnecessary abstraction when you are writing `@font-face` rules anyway.

**Why NOT keep Instrument Sans alone:** Instrument Sans (currently in `@theme`) is solid but generic. It lacks the boldness and personality needed for a "high-energy brand identity." Space Grotesk gives headings punch; Inter gives body text clarity; JetBrains Mono gives code blocks professionalism. Three fonts, one personality system.

**@font-face + @theme integration:**
```css
@font-face {
    font-family: 'Space Grotesk';
    src: url('/fonts/SpaceGrotesk-Variable.woff2') format('woff2');
    font-weight: 300 700;
    font-display: swap;
}

@font-face {
    font-family: 'Inter';
    src: url('/fonts/Inter-Variable.woff2') format('woff2');
    font-weight: 100 900;
    font-display: swap;
}

@font-face {
    font-family: 'JetBrains Mono';
    src: url('/fonts/JetBrainsMono-Variable.woff2') format('woff2');
    font-weight: 100 800;
    font-display: swap;
}

@theme {
    --font-display: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;
}
```

This generates `font-display`, `font-body`, and `font-mono` utility classes automatically in Tailwind v4.

### 4. Syntax Highlighting: highlight.js (client-side)

| Detail | Value |
|--------|-------|
| Package | `highlight.js` |
| Version | ^11.11.1 (latest as of Jan 2026) |
| Size | ~30KB core + theme CSS (configurable per-language) |
| Install | `npm install highlight.js` |
| Confidence | HIGH (24.8K GitHub stars, actively maintained, zero dependencies) |

**Why highlight.js (client-side):** For a WordPress-migrated blog with 2,500+ posts, code blocks exist as raw HTML `<pre><code>` in stored content. Client-side highlighting is the pragmatic choice because:
1. It auto-detects languages -- no need to retroactively tag 20 years of code blocks with language classes.
2. It works directly on existing content without modifying stored HTML.
3. It requires zero backend processing -- important when you have 2,500 posts that would need server-side re-rendering.
4. It has 189+ languages built-in, covering anything from the blog's history.

**Why NOT Tempest Highlight (server-side PHP):** Tempest Highlight (`tempest/highlight` v2.14.0, PHP ^8.4) is the modern PHP-native option and would be ideal for a new blog or one using Markdown. However, this blog stores content as raw HTML from WordPress. Server-side highlighting would require either:
- Processing every post's HTML at render time to find and highlight `<pre><code>` blocks (adds latency per page load), or
- A migration to re-process all 2,500 posts and store highlighted HTML (fragile, couples content to a specific highlighter).
Client-side highlight.js avoids both problems.

**Why NOT Torchlight (API):** Torchlight produces beautiful VS Code-quality highlighting via an API, but it introduces an external dependency for rendering. Every uncached page load would hit the Torchlight API. For a blog with high content volume and no revenue model, adding an API dependency for syntax highlighting is unnecessary risk. It also requires paid subscription for revenue-generating sites.

**Why NOT Prism.js:** Prism.js is effectively abandoned -- Prism v2 development stalled in 2022 and no updates have shipped. highlight.js is actively maintained (GitHub updated Jan 2026) and has better auto-detection, which matters for legacy content without language annotations.

**Why NOT Shiki (client-side):** Shiki produces the highest quality output (VS Code grammar engine) but weighs ~280KB and requires a WASM dependency. For a blog that may have occasional code blocks, that payload-to-value ratio is wrong. Shiki is best for documentation sites where every page has code.

**Integration pattern:**
```javascript
// resources/js/app.js
import hljs from 'highlight.js/lib/core';
// Import only needed languages to reduce bundle size
import javascript from 'highlight.js/lib/languages/javascript';
import php from 'highlight.js/lib/languages/php';
import xml from 'highlight.js/lib/languages/xml';
import css from 'highlight.js/lib/languages/css';
import bash from 'highlight.js/lib/languages/bash';
import sql from 'highlight.js/lib/languages/sql';

hljs.registerLanguage('javascript', javascript);
hljs.registerLanguage('php', php);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('css', css);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('sql', sql);

// Auto-highlight all code blocks on page load
hljs.highlightAll();

// Re-highlight after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    hljs.highlightAll();
});
```

**Theme integration:** highlight.js ships 40+ themes as CSS files. Import one that matches the royal blue + dark brand:
```javascript
// Use a dark theme for code blocks
import 'highlight.js/styles/atom-one-dark.css';
```

Alternatively, create a custom highlight.js theme using the `@theme` design tokens to ensure code blocks match the brand palette exactly.

### 5. Design Tokens: Expanded @theme Configuration (no package -- CSS only)

| Detail | Value |
|--------|-------|
| Method | Expand existing `@theme` block in `app.css` |
| Install | Nothing to install |
| Confidence | HIGH (this is how Tailwind CSS 4 is designed to work) |

The current `@theme` block has 6 tokens. A bold UI overhaul needs a complete design token system. This is not a library -- it is Tailwind CSS 4's native approach.

**Recommended token expansion:**
```css
@theme {
    /* Typography */
    --font-display: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;

    /* Brand Palette -- royal blue + black + white */
    --color-brand-primary: #1e3a8a;      /* royal blue (blue-900) */
    --color-brand-vivid: #2563eb;        /* vivid blue for CTAs (blue-600) */
    --color-brand-accent: #f59e0b;       /* amber energy for highlights */
    --color-brand-success: #10b981;
    --color-brand-danger: #ef4444;

    /* Surfaces */
    --color-surface-light: #ffffff;
    --color-surface-muted: #f8fafc;
    --color-surface-dark: #0f172a;
    --color-surface-dark-raised: #1e293b;

    /* Text */
    --color-text-primary: #0f172a;
    --color-text-secondary: #475569;
    --color-text-muted: #94a3b8;
    --color-text-inverse: #f8fafc;

    /* Spacing scale (content rhythm) */
    --spacing-content: 1.5rem;
    --spacing-section: 4rem;

    /* Border radius */
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;

    /* Shadows */
    --shadow-card: 0 1px 3px rgba(0,0,0,0.1);
    --shadow-elevated: 0 10px 25px rgba(0,0,0,0.1);

    /* Transitions */
    --ease-snappy: cubic-bezier(0.2, 0, 0, 1);
    --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
}
```

Every `--color-*` token auto-generates `bg-`, `text-`, `border-` utilities. Every `--font-*` generates `font-` utilities. This is zero-dependency, zero-overhead design system infrastructure.

**Note on brand palette:** The original `@theme` uses `#1e40af` (blue-800) as primary. For the royal blue + black + white scheme described in the project context, `#1e3a8a` (blue-900) is more authentically "royal blue" -- deeper and more regal. The vivid blue (`#2563eb`) serves as the interactive/CTA color for buttons and links where contrast against white is needed.

### 6. Dark Mode: @custom-variant Pattern (already in place -- document the strategy)

| Detail | Value |
|--------|-------|
| Method | `@custom-variant dark (&:where(.dark, .dark *));` already in `app.css` |
| Toggle | Alpine.js + localStorage (already implemented) |
| Install | Nothing -- already working |
| Confidence | HIGH (this is the official Tailwind v4 pattern) |

**Current state:** The project already has the correct Tailwind v4 dark mode setup. The `@custom-variant dark` directive in `app.css` replaces the old `darkMode: 'class'` from Tailwind v3/config-based approach. Alpine.js toggles the `.dark` class on the `<html>` element, and localStorage persists the preference.

**What needs to happen for the UI overhaul (design work, not stack additions):**
1. Define dark mode surface/text colors in the `@theme` block (already started with `--color-dark-bg`, `--color-dark-surface`, `--color-dark-text`).
2. Use `dark:` prefix consistently on every component: `dark:bg-surface-dark`, `dark:text-text-inverse`, etc.
3. Ensure highlight.js theme works in both modes -- either use a theme that works on both backgrounds, or swap themes via Alpine's dark mode state.
4. Ensure the `@tailwindcss/typography` prose styles have dark mode overrides: `dark:prose-invert` class on `<article>` elements handles this automatically.

**Why NOT use `prefers-color-scheme` media query (Tailwind v4 default):** The project uses manual toggle (class-based), which is correct for a blog where users may prefer different settings than their OS. The `@custom-variant` override is already in place.

**Why NOT use data attributes:** Some frameworks prefer `data-theme="dark"` over a `.dark` class. Both work with `@custom-variant`. The `.dark` class approach is already implemented and is the most common pattern in the Tailwind ecosystem, keeping examples and community resources directly applicable.

### 7. Image Handling: CSS-Only Approach (no package needed)

| Detail | Value |
|--------|-------|
| Method | CSS aspect-ratio + background placeholders + responsive images via `srcset` |
| Install | Nothing to install |
| Confidence | HIGH (native CSS/HTML, no library needed) |

**Why NOT Spatie Media Library:** `spatie/laravel-medialibrary` is the gold standard for Laravel image handling (responsive srcset generation, SVG placeholder generation, conversions). However, this project's images come from WordPress -- they are external URLs or already-migrated files, not Media Library-managed uploads. Adopting Media Library would require re-importing all post images into its system. That is a data migration, not a UI overhaul.

**What to do instead (CSS-only approach):**
1. **Missing featured images:** Use CSS to show a branded SVG placeholder or gradient background when no featured image exists. A simple `<div>` with `bg-brand-primary` and the blog logo/pattern is more on-brand than a generic placeholder service.
2. **Responsive images for new content:** Use standard HTML `<img>` with `loading="lazy"` and `srcset` if multiple sizes are available. The `@tailwindcss/typography` plugin already styles images within prose content.
3. **Aspect ratio consistency:** Use Tailwind's built-in `aspect-video` or `aspect-square` utilities on image containers to prevent layout shift (CLS). These are built into Tailwind v4 core.
4. **WordPress content images:** The existing `ShortcodeProcessor` already handles WordPress image markup. The UI overhaul should style `.wp-caption` and related classes (already started in `app.css`) with the new design tokens.

**Why NOT Intervention Image:** `intervention/image` is for server-side image manipulation (resize, crop, watermark). The UI overhaul is about CSS presentation, not image processing. If image resizing is needed later, it belongs in a separate operations milestone.

---

## What NOT to Add (and Why)

### Flux UI (livewire/flux) -- DO NOT ADD

Flux is the official Livewire UI component library ($99/year for Pro). It is excellent for new projects starting from scratch, but wrong for this project because:

1. **Existing admin UI is custom Blade/Livewire components.** Adopting Flux means rewriting every admin page to use `<flux:input>`, `<flux:table>`, `<flux:modal>` instead of existing components. That is a full rewrite, not an overhaul.
2. **Cost for limited value.** The free tier covers basics (buttons, dropdowns, modals) that are already built. The paid tier adds datepickers and charts that a blog does not need.
3. **Design lock-in.** Flux imposes its own design language. The goal is a custom brand identity, not a Flux-flavored one.

**Instead:** Improve existing Blade components with the new design tokens. The admin already works. It needs better colors, typography, and spacing -- not a component framework swap.

### daisyUI / maryUI -- DO NOT ADD

These are CSS component libraries that add predefined component classes. They conflict with the design token approach: you would fight their defaults instead of building from your tokens. Tailwind CSS 4's `@theme` is the component styling system.

### Motion / Framer Motion / GSAP -- DO NOT ADD

These are JavaScript animation powerhouses designed for marketing sites, complex page transitions, and scroll-driven storytelling. A blog needs:
- Show/hide transitions (Alpine.js `x-transition` -- already have it)
- List animations (AutoAnimate -- recommended above)
- Hover effects (CSS transitions -- already have it via Tailwind)

Adding a 20KB+ animation library for effects achievable with CSS and Alpine is pure bloat.

### Tailwind CSS Component Libraries (Headless UI, Radix, shadcn) -- DO NOT ADD

These are React-oriented or framework-agnostic headless component libraries. This project uses Livewire + Alpine.js for interactivity. Adding React-based headless UI components creates a second reactivity system. Alpine.js already provides the equivalent patterns (dropdowns, modals, tabs, accordions) natively within the Livewire ecosystem.

### @tailwindcss/container-queries plugin -- DO NOT ADD

Container queries are now built into Tailwind CSS v4 core. The `@container` directive and `@sm:`, `@md:`, `@lg:` variants work out of the box without any plugin. Use them freely for component-scoped responsive design (e.g., post cards that adapt to sidebar vs. main content width).

### Spatie Laravel Media Library -- DO NOT ADD (for this milestone)

As explained in section 7, adopting Media Library for image handling would require re-importing all WordPress-migrated images. That is a data migration effort that belongs in a future milestone, not a visual overhaul. The CSS-only approach handles the UI presentation needs.

### Flowbite / Preline / other component libraries -- DO NOT ADD

Pre-made component designs conflict with the bespoke brand identity we are building. Every pre-styled component would need to be restyled anyway, negating the value of using it.

---

## Installation Summary

### Composer (PHP)
```bash
composer require blade-ui-kit/blade-heroicons
```

### npm (JavaScript)
```bash
npm install highlight.js @formkit/auto-animate
```

### Fonts (manual download)
Download variable woff2 files and place in `/public/fonts/`:
- `SpaceGrotesk-Variable.woff2` -- from [Google Fonts](https://fonts.google.com/specimen/Space+Grotesk) or [GitHub](https://github.com/floriankarsten/space-grotesk/blob/master/fonts/woff2/)
- `Inter-Variable.woff2` -- from [Google Fonts](https://fonts.google.com/specimen/Inter)
- `JetBrainsMono-Variable.woff2` -- from [GitHub](https://github.com/JetBrains/JetBrainsMono/releases)

Add `@font-face` declarations in `app.css` before the `@theme` block (see section 3 for full code).

### CSS (no install -- edit app.css)
Expand the existing `@theme` block with the full design token set documented in section 5.

---

## Total Impact Assessment

| Metric | Before | After |
|--------|--------|-------|
| Composer packages | 9 production | 10 production (+1: blade-heroicons) |
| npm packages | 6 dev | 8 dev (+2: highlight.js, auto-animate) |
| CSS bundle impact | Minimal | +highlight.js theme CSS (~2KB) |
| JS bundle impact | 0 | ~33KB gzipped (highlight.js core+6 langs ~30KB + auto-animate ~3KB) |
| Font load | ~20KB (Instrument Sans via system) | ~125KB (3 variable families, self-hosted) |
| New concepts to learn | 0 | 2 (highlight.js config, AutoAnimate API) |

This is a deliberately minimal stack addition. The overhaul's visual impact comes from design decisions (color, typography, spacing, contrast) expressed through Tailwind CSS 4's native `@theme` system, not from adding libraries.

**Bundle optimization note:** highlight.js supports tree-shaking by importing individual languages. By importing only ~6 core languages (JS, PHP, HTML/XML, CSS, Bash, SQL) instead of the full 189+ language bundle, the JS addition stays at ~30KB gzipped rather than ~100KB+. If the blog turns out to have exotic languages in code blocks, add them incrementally.

---

## Alternatives Considered

| Category | Recommended | Alternative | Why Not |
|----------|-------------|-------------|---------|
| Icons | blade-heroicons | Lucide via blade-lucide-icons | Extra design language; Heroicons match Tailwind DNA |
| Icons | blade-heroicons | Flux icons | Requires Flux adoption; not standalone |
| Animations | @formkit/auto-animate | Alpine x-transition only | No list reorder animations |
| Animations | @formkit/auto-animate | GSAP | 7x larger, overkill for blog |
| Syntax highlighting | highlight.js (client) | Tempest Highlight (PHP) | Would need to process 2,500 posts of raw HTML server-side |
| Syntax highlighting | highlight.js (client) | Shiki (client) | 280KB + WASM, overkill for occasional code blocks |
| Syntax highlighting | highlight.js (client) | Torchlight (API) | External API dependency; paid for revenue sites |
| Syntax highlighting | highlight.js (client) | Prism.js (client) | Effectively abandoned since 2022 |
| Display font | Space Grotesk | Oswald | Condensed/news-like, not tech-personality |
| Display font | Space Grotesk | Poppins | Ubiquitous; lacks distinctiveness |
| Body font | Inter | Open Sans | Inter has better screen optimization and variable support |
| Mono font | JetBrains Mono | Fira Code | JetBrains Mono has better readability at small sizes |
| Mono font | JetBrains Mono | Source Code Pro | Lacks ligatures; less personality |
| Image handling | CSS-only | Spatie Media Library | Requires re-importing all WordPress images |
| Image handling | CSS-only | Intervention Image | Server-side processing not needed for UI overhaul |
| Dark mode | @custom-variant (existing) | prefers-color-scheme only | Users want manual toggle; already implemented |
| Container queries | Built-in (Tailwind v4) | @tailwindcss/container-queries | Plugin unnecessary; feature is in core |
| Component lib | None (custom) | Flux UI | Full rewrite of working admin; design lock-in |
| Component lib | None (custom) | daisyUI / maryUI | Fights Tailwind @theme approach |

---

## Admin UI Component Strategy (No Library Needed)

The admin panel uses custom Livewire full-page components with Blade views. Rather than introducing a component library, the overhaul should:

1. **Create reusable Blade components** for repeated admin patterns:
   - `<x-admin.card>` -- white card with consistent padding/shadow
   - `<x-admin.button>` -- primary/secondary/danger variants using brand tokens
   - `<x-admin.table>` -- consistent table styling with gray-50 headers
   - `<x-admin.stat-card>` -- dashboard metric cards
   - `<x-admin.form-group>` -- label + input + error message wrapper

2. **Apply design tokens consistently** via the expanded `@theme` variables, so all admin components inherit the brand palette.

3. **Use Alpine.js for interactions** (already available): dropdowns, confirmations, tabs, sidebar collapse. No additional JS library needed.

This approach costs zero dependencies and produces a perfectly branded admin experience.

---

## Sources

### Tailwind CSS 4
- [Tailwind CSS v4 @theme documentation](https://tailwindcss.com/docs/theme) -- HIGH confidence
- [Tailwind CSS v4 announcement](https://tailwindcss.com/blog/tailwindcss-v4) -- HIGH confidence
- [Tailwind CSS v4 dark mode docs](https://tailwindcss.com/docs/dark-mode) -- HIGH confidence
- [Tailwind CSS adding custom styles](https://tailwindcss.com/docs/adding-custom-styles) -- HIGH confidence
- [Tailwind CSS v4 container queries (built-in)](https://tailwindcss.com/blog/tailwindcss-v4) -- HIGH confidence
- [Custom font setup in Tailwind v4](https://github.com/tailwindlabs/tailwindcss/discussions/13890) -- HIGH confidence

### Syntax Highlighting
- [highlight.js npm](https://www.npmjs.com/package/highlight.js) -- v11.11.1, HIGH confidence
- [highlight.js GitHub](https://github.com/highlightjs/highlight.js) -- 24.8K stars, updated Jan 2026, HIGH confidence
- [Tempest Highlight Packagist](https://packagist.org/packages/tempest/highlight) -- v2.14.0, HIGH confidence (evaluated, not recommended for this use case)
- [Torchlight Laravel](https://torchlight.dev/docs/clients/laravel) -- v0.6.1, HIGH confidence (evaluated, not recommended)
- [Syntax highlighter comparison](https://chsm.dev/blog/2025/01/08/comparing-web-code-highlighters) -- MEDIUM confidence
- [Prism vs highlight.js vs Shiki (npm-compare)](https://npm-compare.com/highlight.js,prismjs,react-syntax-highlighter,shiki) -- MEDIUM confidence

### Typography / Fonts
- [Space Grotesk on Google Fonts](https://fonts.google.com/specimen/Space+Grotesk) -- HIGH confidence
- [Space Grotesk GitHub (variable woff2)](https://github.com/floriankarsten/space-grotesk) -- HIGH confidence
- [Inter on Google Fonts](https://fonts.google.com/specimen/Inter) -- HIGH confidence
- [JetBrains Mono GitHub](https://github.com/JetBrains/JetBrainsMono) -- HIGH confidence
- [Self-hosting web fonts (Google Fonts Knowledge)](https://fonts.google.com/knowledge/using_type/self_hosting_web_fonts) -- HIGH confidence
- [Fontsource Space Grotesk variable](https://www.npmjs.com/package/@fontsource-variable/space-grotesk) -- HIGH confidence (evaluated, not recommended for this setup)

### Icons
- [blade-ui-kit/blade-heroicons GitHub](https://github.com/blade-ui-kit/blade-heroicons) -- HIGH confidence
- [Blade Icons site](https://blade-ui-kit.com/blade-icons) -- HIGH confidence

### Animations
- [@formkit/auto-animate GitHub](https://github.com/formkit/auto-animate) -- HIGH confidence
- [@formkit/auto-animate npm](https://www.npmjs.com/package/@formkit/auto-animate) -- HIGH confidence

### Image Handling
- [Spatie Laravel Media Library responsive images](https://spatie.be/docs/laravel-medialibrary/v11/responsive-images/getting-started-with-responsive-images) -- HIGH confidence (evaluated, not recommended for this milestone)

### Evaluated but Not Recommended
- [Flux UI](https://fluxui.dev/) -- HIGH confidence (evaluated, not recommended)
- [Prism.js](https://prismjs.com/) -- HIGH confidence (evaluated, abandoned)
- [Shiki](https://shiki.style/) -- HIGH confidence (evaluated, too heavy)
