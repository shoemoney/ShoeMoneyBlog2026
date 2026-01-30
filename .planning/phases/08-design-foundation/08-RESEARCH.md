# Phase 8: Design Foundation - Research

**Researched:** 2026-01-30
**Domain:** Design Systems, CSS Architecture, Tailwind CSS v4, Typography
**Confidence:** HIGH

## Summary

This research covers implementing a semantic design token system in Tailwind CSS v4 for a Laravel + Livewire application. The standard approach uses Tailwind v4's new `@theme` directive to define design tokens as CSS custom properties, which automatically generates utility classes and makes tokens available at runtime. For typography, modern best practice dictates self-hosting variable fonts with `font-display: swap` and `size-adjust` properties to prevent layout shifts.

The project is already using Tailwind CSS v4.0.0 with the `@tailwindcss/vite` plugin and has a basic `@theme` block in place. The research identifies that Tailwind v4 represents a fundamental shift from JavaScript configuration to CSS-first theming, with native cascade layers, registered custom properties, and oklch color space support.

**Key architectural decision:** Design tokens should follow a two-layer system: primitive tokens (raw values) and semantic tokens (purpose-driven abstractions like `surface-page`, `text-primary`, `shadow-card`) that enable theming and dark mode without touching component code.

**Primary recommendation:** Use Tailwind v4's `@theme` directive with semantic color naming (background/text/border scoped), self-host variable fonts with proper fallback metrics, and define a complete token system for colors, typography, spacing, shadows, radii, and transitions before implementing any UI components.

## Standard Stack

The established libraries/tools for this domain:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Tailwind CSS | 4.0.0 | CSS framework with design token system | Already in use; v4 is 3.78x faster, CSS-first configuration via `@theme` directive |
| @tailwindcss/vite | 4.0.0 | Vite integration plugin | Official Tailwind v4 integration for Laravel Vite setups |
| @tailwindcss/typography | 0.5.19 | Prose styling for blog content | Already in use; handles WordPress-migrated content styling |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Google Fonts (download) | Latest | Source for variable fonts (Space Grotesk, Inter, JetBrains Mono) | Download via github.com/google/fonts or Google Fonts UI, self-host in project |
| fontsource.org | N/A (reference) | Alternative font distribution with variable font packages | If Google Fonts doesn't provide variable font files easily |
| next/font or @capsizecss/core | N/A | Automated size-adjust calculation | Optional - can calculate manually or use online tools |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Self-hosted fonts | Google Fonts CDN | CDN adds external dependency, GDPR concerns, cache partitioning removed performance benefit (Web Almanac 2022) |
| CSS custom properties | Tailwind v3 JS config | v3 config doesn't provide runtime access to tokens, requires rebuild for changes |
| oklch color space | rgb/hsl | oklch provides wider gamut and perceptually uniform colors, but Tailwind v4's default palette is already oklch-based |

**Installation:**
```bash
# Already installed in project
npm install tailwindcss @tailwindcss/vite @tailwindcss/typography

# No additional packages needed for design tokens
# Font files will be downloaded and placed in public/fonts/
```

## Architecture Patterns

### Recommended Project Structure
```
public/
├── fonts/                          # Self-hosted variable fonts
│   ├── space-grotesk-variable.woff2
│   ├── inter-variable.woff2
│   └── jetbrains-mono-variable.woff2
resources/
├── css/
│   ├── app.css                     # Main CSS entry point
│   │   ├── @import 'tailwindcss'
│   │   ├── @theme { tokens }       # Design tokens
│   │   ├── @font-face { fonts }
│   │   └── Custom styles
│   └── tokens/                     # Optional: split tokens for organization
│       ├── colors.css              # Color tokens only
│       ├── typography.css          # Font tokens only
│       └── spacing.css             # Spacing/shadow/radius tokens
```

### Pattern 1: Two-Layer Semantic Token System
**What:** Separate primitive tokens (raw values) from semantic tokens (purpose-driven names)

**When to use:** All design systems that need theming, dark mode, or brand flexibility

**Structure:**
```css
@theme {
  /* Primitive tokens - raw color values */
  --color-slate-50: oklch(0.99 0 0);
  --color-slate-900: oklch(0.26 0.015 256.85);
  --color-blue-500: oklch(0.55 0.22 264.05);
  --color-amber-500: oklch(0.75 0.15 75.69);

  /* Semantic tokens - purpose-driven (light mode defaults) */
  --color-surface-page: var(--color-slate-50);
  --color-surface-card: oklch(1 0 0);
  --color-text-primary: var(--color-slate-900);
  --color-text-subtle: oklch(0.45 0.015 256.85);
  --color-accent-primary: var(--color-blue-500);
  --color-accent-emphasis: var(--color-amber-500);
}

/* Dark mode overrides - only semantic tokens change */
@media (prefers-color-scheme: dark) {
  :root {
    --color-surface-page: oklch(0.15 0.015 256.85);
    --color-surface-card: oklch(0.20 0.015 256.85);
    --color-text-primary: oklch(0.95 0 0);
    --color-text-subtle: oklch(0.65 0.015 256.85);
    /* Primitive tokens referenced stay the same */
  }
}
```

**Why this works:** Components use semantic tokens (`bg-surface-card`, `text-primary`). Theming changes only update semantic token values, never component code.

### Pattern 2: Variable Font Declaration with Fallback Optimization
**What:** Self-hosted variable fonts with size-adjust to prevent layout shift

**When to use:** All modern web projects prioritizing performance and Core Web Vitals

**Example:**
```css
/* Variable font with range support */
@font-face {
  font-family: 'Inter Variable';
  src: url('/fonts/inter-variable.woff2') format('woff2 supports variations'),
       url('/fonts/inter-variable.woff2') format('woff2-variations'),
       url('/fonts/inter-variable.woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}

/* Optimized fallback to reduce layout shift */
@font-face {
  font-family: 'Inter Fallback';
  src: local('Arial');
  size-adjust: 106.5%;  /* Calculated to match Inter metrics */
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}

@theme {
  --font-body: 'Inter Variable', 'Inter Fallback', system-ui, sans-serif;
}
```

**Source:** Web.dev font best practices, Chrome for Developers improved font fallbacks

### Pattern 3: Tailwind v4 @theme Block Structure
**What:** Complete design token definition using Tailwind v4's `@theme` directive

**When to use:** All Tailwind v4 projects (required for design tokens)

**Example:**
```css
@import 'tailwindcss';
@custom-variant dark (&:where(.dark, .dark *));

@theme {
  /* Typography */
  --font-display: 'Space Grotesk Variable', system-ui, sans-serif;
  --font-body: 'Inter Variable', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono Variable', 'Courier New', monospace;

  /* Spacing scale (8px base) */
  --spacing-0: 0;
  --spacing-1: 0.25rem;  /* 4px */
  --spacing-2: 0.5rem;   /* 8px */
  --spacing-3: 0.75rem;  /* 12px */
  --spacing-4: 1rem;     /* 16px */
  --spacing-6: 1.5rem;   /* 24px */
  --spacing-8: 2rem;     /* 32px */
  --spacing-12: 3rem;    /* 48px */

  /* Shadows (elevation system) */
  --shadow-sm: 0 1px 2px 0 oklch(0 0 0 / 0.05);
  --shadow-DEFAULT: 0 1px 3px 0 oklch(0 0 0 / 0.1), 0 1px 2px -1px oklch(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px oklch(0 0 0 / 0.1), 0 2px 4px -2px oklch(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px oklch(0 0 0 / 0.1), 0 4px 6px -4px oklch(0 0 0 / 0.1);
  --shadow-card: var(--shadow-md);

  /* Border radius */
  --radius-sm: 0.25rem;
  --radius-DEFAULT: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-card: var(--radius-lg);

  /* Transitions */
  --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-DEFAULT: 250ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
}
```

**Source:** Tailwind CSS v4 official documentation

### Pattern 4: Dark Mode Implementation (Manual Toggle)
**What:** Class-based dark mode with localStorage persistence

**When to use:** Projects needing user-controlled dark mode (already implemented in this project)

**CSS:**
```css
@custom-variant dark (&:where(.dark, .dark *));
```

**JavaScript (inline in <head>):**
```javascript
// Prevent FOUC - run before page renders
document.documentElement.classList.toggle(
  'dark',
  localStorage.theme === 'dark' ||
    (!('theme' in localStorage) &&
     window.matchMedia('(prefers-color-scheme: dark)').matches)
);
```

**Source:** Tailwind CSS v4 dark mode documentation

### Anti-Patterns to Avoid

- **Hardcoded colors in components:** Don't use `bg-blue-500` directly. Always use semantic tokens like `bg-accent-primary` that can be themed.
- **Mixing token layers:** Don't reference primitive tokens in components. Components should only use semantic tokens.
- **Using @apply for design tokens:** In Tailwind v4, use `@theme` for tokens, not `@apply` or CSS variables in `:root`.
- **Skipping font fallbacks:** Don't use `font-display: swap` without optimized fallbacks (size-adjust). This causes layout shift.
- **Dynamic class names:** Don't try `className={bg-${color}-500}`. Tailwind can't detect dynamic classes. Use semantic tokens instead.
- **Not purging unused styles:** Ensure content paths in Vite config `@source` directives are correct. The project already has this configured correctly.
- **Overusing CSS custom properties at runtime:** Don't animate CSS variables at the `:root` level. This causes style recalculation for all children. Scope to the animated element.

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Font fallback metrics calculation | Manual math to match font dimensions | size-adjust calculator tools or Next.js next/font | Font metrics are complex (ascent, descent, line-gap, x-height). Tools auto-calculate to prevent layout shift. |
| Dark mode color palette generation | Manually picking dark variants | Tools like Huemint, Coolors, or oklch() with lightness adjustments | Perceptual color uniformity requires oklch color space. Manual picking often results in unbalanced contrast. |
| Design token naming | Custom naming scheme | Established conventions (surface/text/border scoping, primitive/semantic layers) | Naming affects maintainability. Industry patterns are battle-tested across thousands of projects. |
| FOUC prevention | Custom JavaScript timing logic | Inline script in `<head>` with localStorage check before render | Race conditions between theme detection and paint are complex. Standard pattern prevents flashing. |
| Font subsetting | Manual unicode-range definition | Google Fonts subsetting or fontsource packages | Incorrect subsetting breaks international characters. Established ranges cover edge cases. |
| Elevation/shadow system | Random shadow values | Tiered elevation system (sm/DEFAULT/md/lg/xl) with consistent blur/spread ratios | Consistent depth perception requires mathematical progression in shadows. Ad-hoc values look disjointed. |

**Key insight:** Design systems codify decisions made across thousands of projects. The "simple" parts (naming, scales, calculations) contain hidden complexity that manifests as maintenance burden and visual inconsistency. Use proven patterns.

## Common Pitfalls

### Pitfall 1: Layout Shift from Font Swapping (CLS Impact)
**What goes wrong:** Using `font-display: swap` without optimized fallbacks causes visible text reflow when the web font loads, harming Cumulative Layout Shift scores.

**Why it happens:** Fallback fonts (Arial, system-ui) have different character widths, line heights, and spacing than custom fonts. Without size-adjust, the swap is jarring.

**How to avoid:**
1. Use `size-adjust`, `ascent-override`, `descent-override`, and `line-gap-override` on fallback @font-face declarations
2. Calculate values using tools (Fontaine for Nuxt, next/font for Next, or web-based calculators)
3. Test with slow 3G throttling to see the swap clearly

**Warning signs:**
- CLS score > 0.1 in Lighthouse
- Visible text "jump" on page load
- Layout shift warnings in DevTools Performance panel

**Source:** Web.dev optimize CLS, DebugBear web font layout shift guide

### Pitfall 2: CSS Custom Property Performance Issues
**What goes wrong:** Animating or frequently changing CSS variables defined at `:root` causes browser to recalculate styles for the entire document tree, causing jank.

**Why it happens:** CSS custom properties inherit. Changing a value at `:root` triggers style recalculation for all descendants.

**How to avoid:**
1. Scope CSS variables to the smallest possible element when animating
2. Use registered custom properties with `@property` for better performance
3. Avoid nested variable fallbacks like `var(--a, var(--b, var(--c)))`

**Warning signs:**
- Frame drops during theme transitions
- Long "Recalculate Style" entries in Performance panel
- Janky animations on theme toggle

**Source:** Lisi Linhart CSS variables performance analysis, Web.dev @property performance

### Pitfall 3: FOUC (Flash of Unstyled Content) on Theme Load
**What goes wrong:** Page renders with wrong theme (light when user prefers dark, or vice versa) for a brief moment before JavaScript applies the correct theme class.

**Why it happens:** Theme detection JavaScript runs after HTML parsing, during or after first paint.

**How to avoid:**
1. Inline the theme detection script in `<head>` before any visible content
2. Read from localStorage synchronously (not async)
3. Apply the `.dark` class before browser paints
4. Consider adding `<html hidden>` with removal after theme set (nuclear option)

**Warning signs:**
- Visible "flash" of wrong theme on page load
- Theme class appears in DevTools but not on initial render
- User reports seeing wrong colors briefly

**Source:** CSS-Tricks dark mode guide, Tailwind CSS dark mode docs

### Pitfall 4: Hardcoded Colors Breaking Dark Mode
**What goes wrong:** Components use Tailwind utilities like `bg-white` or `text-gray-900` directly instead of semantic tokens. Dark mode doesn't affect these, causing broken contrast.

**Why it happens:** Developers forget to use dark mode variants (`dark:bg-gray-900`) or don't establish semantic tokens upfront.

**How to avoid:**
1. Define semantic color tokens in `@theme` (surface-page, text-primary, etc.)
2. Never use base color utilities in components
3. Audit existing code with grep for hardcoded color classes
4. Establish linting rules or PR review checklist

**Warning signs:**
- White text on white background in dark mode
- Components look fine in light mode but broken in dark
- Grep for `bg-white`, `text-black` returns many results in components

**Source:** Epic Web Dev Tailwind color tokens tutorial

### Pitfall 5: Variable Font Range Not Declared
**What goes wrong:** Variable font loads but only shows as one weight. Browser can't access the weight range.

**Why it happens:** `@font-face` declaration uses single value like `font-weight: 400` instead of range like `font-weight: 100 900`.

**How to avoid:**
1. Always declare ranges for variable fonts: `font-weight: [min] [max]`
2. Check font file documentation for supported ranges
3. Use `format('woff2 supports variations')` and `format('woff2-variations')` for compatibility
4. Test with font-weight utilities in different browsers

**Warning signs:**
- `font-semibold` doesn't look different from `font-normal`
- DevTools computed styles show font-weight changes but visual doesn't change
- Variable font file is loaded but acts like static font

**Source:** Web.dev variable fonts guide, CSS-Tricks variable fonts newsletter

### Pitfall 6: Spacing Inconsistency (No Scale Discipline)
**What goes wrong:** Spacing values are arbitrary (13px, 17px, 23px) instead of following a scale, making the UI feel visually inconsistent.

**Why it happens:** Developers use arbitrary values without understanding spacing scale discipline or not defining spacing tokens upfront.

**How to avoid:**
1. Establish spacing scale in `@theme` (4px or 8px base)
2. Use only scale values: 4, 8, 12, 16, 24, 32, 48, 64
3. Avoid arbitrary values unless absolutely necessary
4. Document why arbitrary values are used when needed

**Warning signs:**
- Spacing feels "off" but hard to articulate why
- Similar components have slightly different padding
- Arbitrary values `[17px]` appear frequently in code

**Source:** Design Systems spacing fundamentals, Atlassian spacing foundation

### Pitfall 7: Not Testing Dark Mode Shadows
**What goes wrong:** Shadows defined for light mode are invisible or too harsh in dark mode.

**Why it happens:** Black shadows (oklch(0 0 0 / 0.1)) don't provide depth on dark backgrounds. Need lighter or inverted shadows.

**How to avoid:**
1. Test shadow tokens in both light and dark themes
2. Consider separate shadow tokens for dark mode or adjust opacity
3. Use colored shadows in dark mode (subtle blue/purple tints)
4. Reference design systems like Shopify Polaris or Atlassian elevation

**Warning signs:**
- Cards look flat in dark mode despite having shadows
- Shadows look too heavy/harsh in dark mode
- Elevation hierarchy breaks in dark theme

**Source:** Atlassian elevation documentation, Shopify Polaris shadow tokens

## Code Examples

Verified patterns from official sources:

### Complete Tailwind v4 CSS Entry Point
```css
/* resources/css/app.css */
@import 'tailwindcss';

/* Dark mode variant - manual toggle with class */
@custom-variant dark (&:where(.dark, .dark *));

/* Plugin for blog content typography */
@plugin "@tailwindcss/typography";

/* Content scanning - already configured correctly */
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

/* Variable font declarations */
@font-face {
  font-family: 'Space Grotesk Variable';
  src: url('/fonts/space-grotesk-variable.woff2') format('woff2 supports variations'),
       url('/fonts/space-grotesk-variable.woff2') format('woff2-variations'),
       url('/fonts/space-grotesk-variable.woff2') format('woff2');
  font-weight: 300 700;
  font-style: normal;
  font-display: swap;
}

@font-face {
  font-family: 'Inter Variable';
  src: url('/fonts/inter-variable.woff2') format('woff2 supports variations'),
       url('/fonts/inter-variable.woff2') format('woff2-variations'),
       url('/fonts/inter-variable.woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}

@font-face {
  font-family: 'JetBrains Mono Variable';
  src: url('/fonts/jetbrains-mono-variable.woff2') format('woff2 supports variations'),
       url('/fonts/jetbrains-mono-variable.woff2') format('woff2-variations'),
       url('/fonts/jetbrains-mono-variable.woff2') format('woff2');
  font-weight: 100 800;
  font-style: normal;
  font-display: swap;
}

/* Optimized fallback fonts with size-adjust */
@font-face {
  font-family: 'Space Grotesk Fallback';
  src: local('Arial');
  size-adjust: 107%;
  ascent-override: 91%;
  descent-override: 23%;
  line-gap-override: 0%;
}

@font-face {
  font-family: 'Inter Fallback';
  src: local('Arial');
  size-adjust: 106.5%;
  ascent-override: 90%;
  descent-override: 22%;
  line-gap-override: 0%;
}

@font-face {
  font-family: 'JetBrains Mono Fallback';
  src: local('Courier New');
  size-adjust: 92%;
  ascent-override: 82%;
  descent-override: 20%;
  line-gap-override: 0%;
}

/* Design token system */
@theme {
  /* Typography tokens */
  --font-display: 'Space Grotesk Variable', 'Space Grotesk Fallback', system-ui, sans-serif;
  --font-body: 'Inter Variable', 'Inter Fallback', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono Variable', 'JetBrains Mono Fallback', 'Courier New', monospace;

  /* Primitive color tokens - Light palette */
  --color-slate-50: oklch(0.99 0.002 264.5);
  --color-slate-100: oklch(0.97 0.005 264.5);
  --color-slate-200: oklch(0.93 0.01 264.5);
  --color-slate-300: oklch(0.87 0.015 264.5);
  --color-slate-400: oklch(0.68 0.02 264.5);
  --color-slate-500: oklch(0.54 0.03 264.5);
  --color-slate-600: oklch(0.43 0.03 264.5);
  --color-slate-700: oklch(0.36 0.03 264.5);
  --color-slate-800: oklch(0.26 0.025 264.5);
  --color-slate-900: oklch(0.19 0.02 264.5);

  --color-blue-400: oklch(0.65 0.18 264.05);
  --color-blue-500: oklch(0.55 0.22 264.05);
  --color-blue-600: oklch(0.47 0.22 264.05);
  --color-blue-700: oklch(0.40 0.21 264.05);

  --color-amber-400: oklch(0.82 0.13 75.69);
  --color-amber-500: oklch(0.75 0.15 75.69);
  --color-amber-600: oklch(0.65 0.15 75.69);

  /* Semantic color tokens - Light mode defaults */
  --color-surface-page: var(--color-slate-50);
  --color-surface-card: oklch(1 0 0);
  --color-surface-raised: oklch(1 0 0);
  --color-surface-overlay: oklch(1 0 0);

  --color-text-primary: var(--color-slate-900);
  --color-text-secondary: var(--color-slate-600);
  --color-text-subtle: var(--color-slate-500);
  --color-text-inverted: oklch(1 0 0);

  --color-border-DEFAULT: var(--color-slate-200);
  --color-border-subtle: var(--color-slate-100);
  --color-border-strong: var(--color-slate-300);

  --color-accent-primary: var(--color-blue-500);
  --color-accent-primary-hover: var(--color-blue-600);
  --color-accent-emphasis: var(--color-amber-500);
  --color-accent-emphasis-hover: var(--color-amber-600);

  /* Spacing scale - 8px base */
  --spacing-0: 0;
  --spacing-px: 1px;
  --spacing-0\.5: 0.125rem;
  --spacing-1: 0.25rem;
  --spacing-1\.5: 0.375rem;
  --spacing-2: 0.5rem;
  --spacing-2\.5: 0.625rem;
  --spacing-3: 0.75rem;
  --spacing-3\.5: 0.875rem;
  --spacing-4: 1rem;
  --spacing-5: 1.25rem;
  --spacing-6: 1.5rem;
  --spacing-7: 1.75rem;
  --spacing-8: 2rem;
  --spacing-9: 2.25rem;
  --spacing-10: 2.5rem;
  --spacing-11: 2.75rem;
  --spacing-12: 3rem;
  --spacing-14: 3.5rem;
  --spacing-16: 4rem;
  --spacing-20: 5rem;
  --spacing-24: 6rem;
  --spacing-28: 7rem;
  --spacing-32: 8rem;

  /* Shadow tokens - elevation system */
  --shadow-sm: 0 1px 2px 0 oklch(0 0 0 / 0.05);
  --shadow-DEFAULT: 0 1px 3px 0 oklch(0 0 0 / 0.1), 0 1px 2px -1px oklch(0 0 0 / 0.1);
  --shadow-md: 0 4px 6px -1px oklch(0 0 0 / 0.1), 0 2px 4px -2px oklch(0 0 0 / 0.1);
  --shadow-lg: 0 10px 15px -3px oklch(0 0 0 / 0.1), 0 4px 6px -4px oklch(0 0 0 / 0.1);
  --shadow-xl: 0 20px 25px -5px oklch(0 0 0 / 0.1), 0 8px 10px -6px oklch(0 0 0 / 0.1);

  /* Semantic shadow tokens */
  --shadow-card: var(--shadow-md);
  --shadow-dropdown: var(--shadow-lg);
  --shadow-modal: var(--shadow-xl);

  /* Border radius tokens */
  --radius-none: 0;
  --radius-sm: 0.25rem;
  --radius-DEFAULT: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --radius-xl: 1rem;
  --radius-2xl: 1.5rem;
  --radius-full: 9999px;

  /* Semantic radius tokens */
  --radius-card: var(--radius-lg);
  --radius-button: var(--radius-md);
  --radius-input: var(--radius-md);

  /* Transition tokens */
  --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-DEFAULT: 250ms cubic-bezier(0.4, 0, 0.2, 1);
  --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Dark mode overrides - only semantic tokens change */
@media (prefers-color-scheme: dark) {
  :root {
    --color-surface-page: oklch(0.15 0.015 264.5);
    --color-surface-card: oklch(0.20 0.02 264.5);
    --color-surface-raised: oklch(0.23 0.02 264.5);
    --color-surface-overlay: oklch(0.26 0.025 264.5);

    --color-text-primary: oklch(0.95 0.002 264.5);
    --color-text-secondary: var(--color-slate-300);
    --color-text-subtle: var(--color-slate-400);
    --color-text-inverted: var(--color-slate-900);

    --color-border-DEFAULT: var(--color-slate-700);
    --color-border-subtle: var(--color-slate-800);
    --color-border-strong: var(--color-slate-600);

    --color-accent-primary: var(--color-blue-400);
    --color-accent-primary-hover: var(--color-blue-500);
    --color-accent-emphasis: var(--color-amber-400);
    --color-accent-emphasis-hover: var(--color-amber-500);

    /* Adjusted shadows for dark mode - lighter for visibility */
    --shadow-sm: 0 1px 2px 0 oklch(0 0 0 / 0.3);
    --shadow-DEFAULT: 0 1px 3px 0 oklch(0 0 0 / 0.4), 0 1px 2px -1px oklch(0 0 0 / 0.4);
    --shadow-md: 0 4px 6px -1px oklch(0 0 0 / 0.5), 0 2px 4px -2px oklch(0 0 0 / 0.5);
    --shadow-lg: 0 10px 15px -3px oklch(0 0 0 / 0.5), 0 4px 6px -4px oklch(0 0 0 / 0.5);
    --shadow-xl: 0 20px 25px -5px oklch(0 0 0 / 0.6), 0 8px 10px -6px oklch(0 0 0 / 0.6);
  }
}

/* Manual dark mode override (when .dark class is present) */
.dark {
  --color-surface-page: oklch(0.15 0.015 264.5);
  --color-surface-card: oklch(0.20 0.02 264.5);
  --color-surface-raised: oklch(0.23 0.02 264.5);
  --color-surface-overlay: oklch(0.26 0.025 264.5);

  --color-text-primary: oklch(0.95 0.002 264.5);
  --color-text-secondary: var(--color-slate-300);
  --color-text-subtle: var(--color-slate-400);
  --color-text-inverted: var(--color-slate-900);

  --color-border-DEFAULT: var(--color-slate-700);
  --color-border-subtle: var(--color-slate-800);
  --color-border-strong: var(--color-slate-600);

  --color-accent-primary: var(--color-blue-400);
  --color-accent-primary-hover: var(--color-blue-500);
  --color-accent-emphasis: var(--color-amber-400);
  --color-accent-emphasis-hover: var(--color-amber-500);

  --shadow-sm: 0 1px 2px 0 oklch(0 0 0 / 0.3);
  --shadow-DEFAULT: 0 1px 3px 0 oklch(0 0 0 / 0.4), 0 1px 2px -1px oklch(0 0 0 / 0.4);
  --shadow-md: 0 4px 6px -1px oklch(0 0 0 / 0.5), 0 2px 4px -2px oklch(0 0 0 / 0.5);
  --shadow-lg: 0 10px 15px -3px oklch(0 0 0 / 0.5), 0 4px 6px -4px oklch(0 0 0 / 0.5);
  --shadow-xl: 0 20px 25px -5px oklch(0 0 0 / 0.6), 0 8px 10px -6px oklch(0 0 0 / 0.6);
}

/* Existing WordPress content styles preserved */
.wp-caption {
  @apply my-6 mx-auto;
}

.wp-caption img {
  @apply rounded-lg shadow-md;
}

.wp-caption figcaption {
  @apply text-sm text-subtle mt-2 text-center italic;
}

.wp-video {
  @apply w-full rounded-lg shadow-md my-6;
}

.wp-more {
  @apply my-8 border-0 h-px bg-border-DEFAULT;
}

/* Alpine.js cloak */
[x-cloak] {
  display: none !important;
}
```
**Source:** Tailwind CSS v4 official documentation, project's existing app.css

### Dark Mode Toggle Script (Prevent FOUC)
```html
<!-- In resources/views/components/layout.blade.php <head> section -->
<script>
// Run before page renders to prevent flash
(function() {
  // Check localStorage or system preference
  const isDark = localStorage.theme === 'dark' ||
    (!('theme' in localStorage) &&
     window.matchMedia('(prefers-color-scheme: dark)').matches);

  // Apply immediately
  if (isDark) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
})();
</script>
```

```javascript
// Dark mode toggle component (Alpine.js)
// Example for a toggle button
Alpine.data('darkModeToggle', () => ({
  toggle() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.theme = isDark ? 'dark' : 'light';
  },

  setLight() {
    document.documentElement.classList.remove('dark');
    localStorage.theme = 'light';
  },

  setDark() {
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
  },

  setSystem() {
    localStorage.removeItem('theme');
    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.classList.toggle('dark', isDark);
  }
}));
```
**Source:** Tailwind CSS dark mode documentation

### Component Example Using Semantic Tokens
```blade
{{-- resources/views/components/post-card.blade.php --}}
<article class="bg-surface-card border border-border-DEFAULT rounded-card shadow-card hover:shadow-lg transition-DEFAULT">
  <a href="{{ $post->url }}" class="block p-6">
    <h3 class="font-display text-xl font-semibold text-primary mb-2">
      {{ $post->title }}
    </h3>
    <p class="text-secondary line-clamp-3 mb-4">
      {{ $post->excerpt }}
    </p>
    <div class="flex items-center gap-4 text-sm text-subtle">
      <time datetime="{{ $post->published_at->toIso8601String() }}">
        {{ $post->published_at->format('M j, Y') }}
      </time>
      <span>{{ $post->reading_time }} min read</span>
    </div>
  </a>
</article>
```
**Note:** Component only references semantic tokens. Theme changes require no component updates.

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Tailwind v3 JavaScript config file | Tailwind v4 CSS-first `@theme` directive | v4.0 (Nov 2024) | Tokens available at runtime as CSS variables, no rebuild needed for theme changes, 3.78x faster builds |
| Google Fonts CDN | Self-hosted variable fonts | Web Almanac 2022 cache partitioning | No performance benefit from CDN, GDPR compliance easier, full control over font-display strategy |
| rgb/hsl color space | oklch color space | Tailwind v4 default | Perceptually uniform colors, wider gamut (Display P3), more vivid colors on modern screens |
| font-display: block or auto | font-display: swap with size-adjust fallbacks | Chrome 87 (late 2020) added size-adjust | Shows content immediately, prevents layout shift, better Core Web Vitals (CLS) |
| Static fonts (multiple files per weight) | Variable fonts (single file, weight range) | Widespread support 2020+ | Fewer HTTP requests, smaller total size, animation between weights, better performance |
| Dark mode via duplicated color utilities | Dark mode via CSS custom property overrides | Tailwind v3.0+ (2021) | Single source of truth, easier maintenance, no class duplication |
| @tailwind directives (base, components, utilities) | Single @import 'tailwindcss' | Tailwind v4 | Simpler setup, native CSS @import support, no PostCSS plugin configuration |

**Deprecated/outdated:**
- `tailwind.config.js` theme configuration: Replaced by `@theme` directive in CSS
- `@tailwind base/components/utilities` directives: Replaced by `@import 'tailwindcss'`
- `bg-gradient-to-r` utilities: Renamed to `bg-linear-to-r` in v4
- `rgb()` and `hsl()` color functions in custom colors: oklch() is now standard for better color uniformity
- Google Fonts Helper (third-party tool): Google Fonts now has built-in download UI (Get font button)

## Open Questions

1. **Exact size-adjust values for font fallbacks**
   - What we know: Size-adjust, ascent-override, descent-override, and line-gap-override reduce layout shift
   - What's unclear: Exact calculated values for Space Grotesk, Inter, and JetBrains Mono to match Arial/Courier New fallbacks
   - Recommendation: Use automated tools (Fontaine, next/font, or web-based calculators like Malte Ubl's tool) during implementation, or measure manually with DevTools. Values in code examples are estimates and should be validated.

2. **Brand color preservation during oklch migration**
   - What we know: Project currently uses `--color-brand-primary: #1e40af` (rgb blue), `--color-brand-accent: #f59e0b` (rgb amber)
   - What's unclear: Whether oklch conversion will maintain exact brand colors or require visual approval
   - Recommendation: Convert existing hex colors to oklch using tools (oklch.com converter), validate visually with stakeholders, adjust if needed for brand consistency

3. **WordPress content styling migration**
   - What we know: Existing WordPress content uses hardcoded Tailwind utilities (`.wp-caption figcaption` has `text-gray-500`)
   - What's unclear: Whether all WordPress content classes should migrate to semantic tokens immediately or gradually
   - Recommendation: Migrate WordPress content styles to semantic tokens in this phase (change `text-gray-500` to `text-subtle`) to ensure dark mode works correctly for legacy content

4. **Performance impact of oklch on older browsers**
   - What we know: oklch is modern CSS, supported in all evergreen browsers
   - What's unclear: Fallback strategy if older browsers don't support oklch (although Laravel 11 targets modern browsers)
   - Recommendation: Accept modern browser requirement (matches Laravel 11 philosophy), but could add rgb fallbacks if analytics show significant older browser traffic

## Sources

### Primary (HIGH confidence)
- Tailwind CSS v4 Official Documentation - https://tailwindcss.com/docs/theme (theme directive)
- Tailwind CSS v4 Blog Announcement - https://tailwindcss.com/blog/tailwindcss-v4 (features, migration, performance)
- Tailwind CSS Dark Mode Documentation - https://tailwindcss.com/docs/dark-mode (custom variants, manual toggle)
- Web.dev Font Best Practices - https://web.dev/articles/font-best-practices (font-display, self-hosting)
- Web.dev Variable Fonts - https://web.dev/articles/variable-fonts (implementation, @font-face syntax)

### Secondary (MEDIUM confidence)
- [Epic Web Dev Tailwind Color Tokens Tutorial](https://www.epicweb.dev/tutorials/tailwind-color-tokens/tailwind-css-color-tokens-introduction/introduction-to-tailwind-css-color-tokens) - semantic token patterns verified against Tailwind docs
- [FrontendTools Tailwind CSS Best Practices 2025-2026](https://www.frontendtools.tech/blog/tailwind-css-best-practices-design-system-patterns) - WebSearch verified with official docs
- [DebugBear Web Font Layout Shift Guide](https://www.debugbear.com/blog/web-font-layout-shift) - size-adjust implementation patterns
- [Chrome for Developers: Improved Font Fallbacks](https://developer.chrome.com/blog/font-fallbacks) - official Chrome guidance on size-adjust
- [Lisi Linhart CSS Variables Performance](https://lisilinhart.info/posts/css-variables-performance) - performance analysis cited by community

### Tertiary (LOW confidence)
- [Medium: Theming in Tailwind CSS v4](https://medium.com/@sir.raminyavari/theming-in-tailwind-css-v4-support-multiple-color-schemes-and-dark-mode-ba97aead5c14) - attempted fetch (403 error), content from similar sources used instead
- WebSearch results for spacing scales - general consensus on 8px grid, not single authoritative source
- WebSearch results for shadow/elevation systems - aggregated best practices from multiple design systems
- Font metric override values in code examples - calculated estimates, should be validated with tools

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Tailwind v4 is already in use, official documentation covers all features
- Architecture: HIGH - Patterns verified against official Tailwind v4 docs and web.dev guides
- Pitfalls: MEDIUM-HIGH - Common issues documented across multiple sources, some from community experience vs official docs
- Code examples: HIGH - Syntax verified against official Tailwind v4 and web.dev documentation
- Font fallback metrics: MEDIUM - Size-adjust pattern is standard, but exact values need tool calculation

**Research date:** 2026-01-30
**Valid until:** 2026-02-28 (30 days - Tailwind v4 is stable, CSS standards are stable, font strategies are stable)
