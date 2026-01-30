# Project Research Summary

**Project:** ShoeMoney Blog v2.0 UI Overhaul
**Domain:** Personal brand blog visual redesign (existing Laravel/Livewire platform)
**Researched:** 2026-01-29
**Confidence:** HIGH

## Executive Summary

This is a pure visual overhaul of a live Laravel 11 + Livewire 3 + Tailwind CSS 4 blog with 2,500+ posts and 160,000+ comments spanning 20+ years. The existing application architecture is sound and unchanged -- v2.0 replaces the generic default styling with a bold personal brand identity built on royal blue + black + white, geometric sans-serif typography (Space Grotesk headings, Inter body), card-based post grids, and a properly engineered dark mode. The recommended approach is a 3-layer semantic design token system in Tailwind CSS 4's native `@theme inline` directive, which eliminates 60-70% of `dark:` prefixes and makes the entire site rebrandable by changing 3-5 primitive CSS variables. Stack additions are minimal: one Composer package (blade-heroicons), two npm packages (highlight.js, auto-animate), and three self-hosted font files.

The highest-impact risk is legacy content rendering. Twenty years of WordPress HTML includes inline styles, fixed-width embeds, `<center>` tags, and hand-coded tables from 2004. Any typography or color change can make old posts unreadable. The mitigation is non-negotiable: build a content stress-test page sampling posts from every era before changing a single prose style. The second critical risk is response cache serving stale HTML after CSS deploys -- add `responsecache:clear` to the deploy pipeline before any visual work ships. The third is designing card layouts around the fact that 80%+ of posts have zero featured images -- the text-only card must be the primary design, not a fallback.

The overhaul decomposes into 7 phases following a strict dependency chain: design tokens and infrastructure first (everything depends on colors and fonts), then public layout shell, then homepage transformation, then post reading experience, then engagement polish, then admin dark mode and token migration, and finally verification/cleanup. Each phase leaves the site fully functional. No phase breaks existing behavior.

## Key Findings

### Recommended Stack

The existing stack (Laravel 11, Livewire 3, Tailwind CSS 4, Alpine.js, Vite) is locked and unchanged. Stack additions are deliberately minimal -- the overhaul's visual impact comes from design decisions expressed through Tailwind's native `@theme` system, not from libraries.

**Additions only:**
- **blade-heroicons** (Composer): Tailwind-native icon set, 300+ icons, Blade component syntax -- matches Tailwind's design DNA
- **highlight.js** (npm, ~30KB gzipped): Client-side syntax highlighting with auto-language-detection -- critical for legacy WordPress `<pre><code>` blocks that lack language class attributes
- **@formkit/auto-animate** (npm, ~3KB gzipped): Zero-config list animations for Livewire DOM updates -- fills the gap Alpine.js transitions cannot cover
- **Space Grotesk + Inter + JetBrains Mono** (self-hosted woff2, ~125KB total): Variable fonts for display/body/code -- self-hosted for performance, privacy, and cache control
- **Expanded @theme tokens** (CSS-only): 25-30 semantic design tokens replacing the current 6, enabling automatic dark mode switching

**Explicitly rejected:** Flux UI (design lock-in, rewrites working admin), daisyUI/maryUI (fights @theme approach), GSAP/Motion (overkill for a blog), Shiki (280KB + WASM), Prism.js (abandoned, requires language classes on code blocks), Spatie Media Library (requires image re-import), React-based headless UI libraries (second reactivity system).

### Expected Features

**Table stakes (must have for a modern blog in 2026):**
- Semantic color system with CSS custom properties (foundation for everything)
- Typography system with display + body + mono fonts and strict type scale
- Card-based post grid replacing the current divider list
- Responsive mobile navigation with hamburger menu
- Dark mode as a designed system, not naive color inversion
- "Load More" pagination replacing default Laravel links
- Content width constraint (750-800px for optimal reading)
- Code block syntax highlighting with copy button
- Reading progress indicator on post detail pages
- Social sharing buttons (X, LinkedIn, copy link)
- Author bio card at end of posts
- Related posts section (3-card grid)
- Back-to-top button
- WCAG AA accessible color contrast in both modes
- Featured image support with graceful text-only fallback
- Warm background tones (off-white, not pure white)

**Differentiators (brand identity):**
- Bold homepage hero section with photo, headline, and CTA
- Featured/pinned posts "Start Here" section
- "As Seen In" credibility bar with publication logos
- Category color coding system
- Scroll-triggered fade-in animations (respecting prefers-reduced-motion)
- Hover micro-interactions (card lift, button states)
- Newsletter signup CTAs (hero + post-end, local storage first)
- Animated stat counters ("2,500+ posts. 20+ years.")

**Anti-features (deliberately avoided):**
- Sticky header, aggressive popups, sidebar on every page, infinite scroll, parallax, auto-playing video, carousels, social feed embeds, generic stock photography, cookie-cutter component libraries

**Deferred to later milestones:**
- Content hub / topic cluster pages
- "Most Popular" widget (needs analytics data)
- Video support, keyboard navigation, branded newsletter slide-in

### Architecture Approach

The architecture centers on a 3-layer semantic token system: Layer 1 (primitives in `:root` -- raw hex values never used in templates), Layer 2 (semantic tokens in `:root` + `.dark` -- intent-based names like `--surface-page` that swap values), Layer 3 (`@theme inline` -- bridges semantic tokens to Tailwind utility classes). This means `bg-surface-page` automatically adapts when `.dark` toggles, eliminating most `dark:` prefixes. One CSS file serves both public and admin interfaces with namespaced admin tokens (`--color-admin-sidebar-*`).

**Major components and boundaries:**
1. **Design token system** (`app.css`) -- Single source of truth for all colors, fonts, spacing, shadows. 3-layer architecture with ~25-30 semantic tokens.
2. **Font loading** (`public/fonts/` + `@font-face`) -- Self-hosted variable woff2 files with `font-display: swap` and preload for the display font only.
3. **Code highlighting** (`app.js`) -- highlight.js with 8 registered languages, `livewire:navigated` re-initialization, always-dark code block background.
4. **Public layout shell** (4 Blade files) -- layout, navigation (+ mobile hamburger), footer (+ dark mode fix), theme toggle.
5. **Public content components** (~6 files) -- Redesigned post card (text-first with optional image), card grid index, constrained-width post detail, search bar, sidebar widgets, comments.
6. **Admin infrastructure** (2 existing + 2 new extracted Blade components) -- Dark mode support for admin layout, extracted sidebar-link and flash-messages components for DRY.
7. **Admin Livewire views** (~15 files) -- Formulaic token swap across all admin pages.

**Key architectural decisions:**
- One CSS entry point for both public and admin (no split)
- Admin sidebar stays perpetually dark in both modes (standard admin pattern)
- Featured images use database column + accessor fallback (extract first `<img>` from content)
- No `@apply` component classes (Blade components are the abstraction layer)
- Backward-compatible token migration (old names aliased until all references updated)
- Text-only card as the primary design state; image as progressive enhancement

### Critical Pitfalls

1. **Legacy content rendering breaks** -- 20 years of WordPress HTML with inline styles, fixed-width embeds, and deprecated tags will break under new prose/typography styles. **Prevention:** Build a content stress-test page sampling posts from each era (2004-2026) before any typography changes. Keep all WordPress compatibility CSS intact.

2. **Response cache serves stale HTML** -- `spatie/laravel-responsecache` caches entire HTTP responses including Vite asset references. After CSS deploys, cached pages reference nonexistent hashed filenames, showing completely unstyled pages. **Prevention:** Add `php artisan responsecache:clear` to every deployment touching CSS/JS. Consider disabling cache during active development.

3. **Dark mode coverage gaps** -- The footer and entire admin panel currently have zero dark mode support. Every component change must be verified in both modes. **Prevention:** Build a component showcase page. Enforce dual-mode review rule. Semantic tokens eliminate most but not all `dark:` needs.

4. **Livewire DOM morphing breaks after markup restructuring** -- Restructuring HTML hierarchy (adding wrapper divs for grid layouts, changing tag types) confuses Livewire's morph engine, causing ghost elements, lost form state, and duplicated content. **Prevention:** Add `wire:key` to all dynamic elements. Prefer `x-show` over `@if` for visibility toggles. Test all interactivity after every structural change.

5. **Card layout with no featured images** -- The Post model has no `featured_image` field. All 2,500+ posts lack image data. Cards designed around images will look like broken wireframes. **Prevention:** Design text-only cards as the primary state. Add `featured_image` column with accessor fallback that extracts first `<img>` from content. Test with real data where 80%+ of posts have no image.

## Implications for Roadmap

Based on research, suggested phase structure (7 phases):

### Phase 1: Design Foundation and Infrastructure
**Rationale:** Everything depends on the design token system, fonts, and build tooling. This phase changes CSS/JS infrastructure only -- zero template modifications, zero visual changes to the live site. Backward-compatible by design.
**Delivers:** Complete 3-layer semantic token system, self-hosted fonts with @font-face, highlight.js initialization, auto-animate setup, heroicons package installed. Content stress-test page and component showcase page for verification. Site looks identical after this phase (no templates changed yet).
**Addresses features:** Semantic color system (#1), typography system (#2), warm background tones (#16), code highlighting infrastructure (#15)
**Avoids pitfalls:** Token rename breaks (keep old names as aliases), font loading CLS (self-host with swap + preload), response cache (add clear to deploy pipeline)
**Pre-work included:** Content stress-test page, component showcase page, audit bare border/ring usage, review database-stored injected code, publish Laravel pagination views

### Phase 2: Public Layout Shell
**Rationale:** The 4 layout shell files frame every public page. Updating them first establishes the visual frame before filling in content components. Mobile navigation is table stakes.
**Delivers:** New fonts visible site-wide, semantic token classes on all structural elements, mobile hamburger menu, footer dark mode support, full-bleed section capability, content width constraint.
**Addresses features:** Responsive mobile navigation (#4), content width constraint (#3), layout restructure for full-bleed sections (#25), footer redesign
**Avoids pitfalls:** FOUC script displacement (keep as first head element), custom code injection breaks (preserve injection point positions), wire:navigate CSS persistence (single CSS entry point), sidebar conditional layout (test both states), embedded content overflow (add overflow-x-auto)

### Phase 3: Homepage Transformation
**Rationale:** The homepage is the first impression and most-visited page. Hero section, card grid, and featured posts create the brand identity. This phase has the highest visual impact.
**Delivers:** Bold hero section with photo/headline/CTA, card-based post grid (text-first design), featured/pinned posts "Start Here" section, "Load More" pagination, "As Seen In" credibility bar.
**Addresses features:** Hero section (#17), card grid (#5), featured posts (#21), Load More (#7), As Seen In (#20), featured image support with fallback (#8)
**Avoids pitfalls:** Empty card problem (text-first design), variable card heights (line-clamp + grid-auto-rows), dark mode gaps (dual-mode review)

### Phase 4: Post Reading Experience
**Rationale:** Post detail is where readers spend the most time. Typography refinement, code blocks, and engagement features make the reading experience premium. Code highlighting infrastructure from Phase 1 activates here.
**Delivers:** Refined post detail typography at 750-800px width, syntax-highlighted code blocks with copy button and language label, reading progress bar, social sharing buttons, author bio card, related posts section (3-card grid), back-to-top button.
**Addresses features:** Reading progress (#10), post typography (#13), code blocks (#15), social sharing (#9), author bio (#12), related posts (#11), back-to-top (#13)
**Avoids pitfalls:** Legacy content rendering (stress-test page from Phase 1), code block styling conflicts with prose (override prose code styles explicitly), embedded content overflow, widget HTML breaks at sidebar width

### Phase 5: Engagement and Polish
**Rationale:** Personality layer that transforms "a blog" into "THE ShoeMoney blog." Animations, micro-interactions, newsletter forms, category colors, and breadcrumbs. Enhancement features that build on the structural work of Phases 1-4.
**Delivers:** Newsletter signup CTAs (hero + post-end), category color coding system, scroll-triggered fade-in animations, hover micro-interactions (card lift, button states), animated stat counters, breadcrumb navigation with Schema.org markup.
**Addresses features:** Newsletter CTA (#23), category colors (#22), scroll animations (#24), hover interactions (#26), stat counters (#27), breadcrumbs (#28)
**Avoids pitfalls:** Reduced motion (respect prefers-reduced-motion), dark mode gaps (verify all new interactive states in both modes)

### Phase 6: Admin Dark Mode and Token Migration
**Rationale:** Admin is not user-facing and is lower priority than public pages. However, dark mode support and consistent token usage improve the developer/admin experience. Formulaic work -- same pattern repeated across 15+ files.
**Delivers:** Admin dark mode support (Alpine.js data binding, FOUC script), extracted sidebar-link and flash-messages Blade components (DRY), all 15 admin Livewire views using semantic tokens, consistent styling across all admin pages.
**Addresses features:** Dark mode design system full coverage (#6), admin panel token consistency
**Avoids pitfalls:** Livewire DOM morphing (wire:key on all dynamic elements, test interactivity), shared CSS bundle (single entry point, namespaced admin tokens), threaded comments visual hierarchy (test deeply nested threads at mobile widths)

### Phase 7: Verification and Cleanup
**Rationale:** Final sweep to catch cross-phase regressions. Comprehensive audit ensures nothing was missed across 27+ template files.
**Delivers:** Clean codebase with zero hardcoded color references, full dark mode coverage verified on every page type, WCAG AA accessibility audit passed, legacy content rendering verified across eras, deprecated token aliases removed, cross-browser testing complete.
**Addresses features:** Accessible color contrast (#14)
**Avoids pitfalls:** All remaining -- this is the comprehensive verification phase

### Phase Ordering Rationale

- **Tokens before templates:** The 3-layer token system must exist before any component migration. Phase 1 is additive (no breaking changes) and enables all subsequent phases.
- **Layout shell before components:** Navigation, footer, and layout frame every page. Updating the frame first means component work inherits the correct visual context.
- **Homepage before post detail:** Highest traffic page first. Hero section establishes brand identity that informs post detail styling decisions.
- **Public before admin:** User-facing pages take priority over internal-facing admin.
- **Engagement last (before verification):** Animations, micro-interactions, and newsletter forms are polish on top of structure. They depend on the card grid, hero, and post detail being complete.
- **Verification always last:** Comprehensive sweep catches cross-phase regressions.

### Research Flags

**Phases needing deeper research during planning:**
- **Phase 3 (Homepage):** Hero section content strategy (photo, headline copy, CTA destination) requires stakeholder input. Featured post curation criteria need definition. "As Seen In" logo assets need sourcing. Database migration for `is_featured` and `featured_image` columns needs planning.
- **Phase 4 (Post Reading):** Related posts query strategy (shared categories/tags vs. recent) needs performance testing against 2,500+ posts. Social sharing URL formats for current platforms should be verified.
- **Phase 5 (Engagement):** Newsletter provider integration decision (Mailchimp, ConvertKit, or local-only) affects form implementation. Category color assignments need stakeholder input. Schema.org breadcrumb markup should be verified against current Google recommendations.

**Phases with standard patterns (skip phase research):**
- **Phase 1 (Foundation):** Tailwind CSS 4 @theme, @font-face, highlight.js -- all thoroughly documented with verified patterns in STACK.md and ARCHITECTURE.md.
- **Phase 2 (Layout Shell):** Mobile hamburger menu with Alpine.js, semantic token class replacement -- well-established Tailwind/Alpine patterns.
- **Phase 6 (Admin):** Formulaic token swap, component extraction -- repetitive work with no novel patterns.
- **Phase 7 (Verification):** Audit and testing process -- no research needed.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | All recommendations verified against official docs and package registries. Minimal additions to a locked, working stack. highlight.js and auto-animate both confirmed on npm with active maintenance. |
| Features | HIGH | Table stakes confirmed across multiple 2026 design trend sources. Feature dependencies mapped with clear critical path. Anti-features explicitly justified. |
| Architecture | HIGH | 3-layer token system verified against Tailwind CSS 4 official docs and GitHub discussions. `@theme inline` behavior confirmed. Component boundary analysis based on direct source code inspection of 27 templates. |
| Pitfalls | HIGH | Derived from direct source code inspection of all Blade templates plus verified community reports. Existing bugs (footer dark mode, admin dark mode absence) confirmed in codebase. Response cache and Livewire morphing pitfalls backed by official repo discussions. |

**Overall confidence:** HIGH

### Gaps to Address

- **Featured image data:** The Post model has no `featured_image` column. A migration must be created (Phase 3 at latest). The accessor fallback (extract first img from content) needs testing against real data to determine what percentage of posts have extractable images.
- **Hero section content:** Research covers the pattern and layout but not the actual content (photo, headline, CTA text, "As Seen In" logos). This requires stakeholder input before Phase 3 implementation.
- **Newsletter provider:** Phase 5 newsletter forms are designed to store emails locally first. The eventual provider integration is deferred but the form HTML/UX should anticipate it.
- **Legacy content audit depth:** The stress-test page approach is prescribed but the actual distribution of problematic HTML patterns across 2,500+ posts is unknown. Early database queries during Phase 1 pre-work will quantify this.
- **Admin dark mode scope confirmation:** Architecture research recommends full admin dark mode. Pitfalls research flags the shared CSS bundle risk. Stakeholder should confirm admin dark mode is in scope before Phase 6 planning.

## Sources

### Primary (HIGH confidence)
- [Tailwind CSS v4 @theme documentation](https://tailwindcss.com/docs/theme) -- token system, @theme inline, utility generation
- [Tailwind CSS v4 dark mode documentation](https://tailwindcss.com/docs/dark-mode) -- @custom-variant, class-based toggling
- [Tailwind CSS v4 release blog](https://tailwindcss.com/blog/tailwindcss-v4) -- breaking changes, new defaults, container queries
- [highlight.js GitHub](https://github.com/highlightjs/highlight.js) -- 24.8K stars, auto-detection, DOM API
- [blade-ui-kit/blade-heroicons](https://github.com/blade-ui-kit/blade-heroicons) -- Blade icon components
- [@formkit/auto-animate](https://github.com/formkit/auto-animate) -- list animation library
- [Spatie Laravel ResponseCache](https://github.com/spatie/laravel-responsecache) -- cache invalidation
- [Livewire wire:navigate documentation](https://livewire.laravel.com/docs/3.x/navigate) -- SPA navigation behavior
- [@tailwindcss/typography](https://github.com/tailwindlabs/tailwindcss-typography) -- prose styling behavior with CMS content
- [Tailwind CSS v4 Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide) -- breaking default changes
- Project source code inspection (27 Blade templates, app.css, app.js, models, routes)

### Secondary (MEDIUM confidence)
- [GitHub Discussion: Dark Mode CSS Variables in v4](https://github.com/tailwindlabs/tailwindcss/discussions/15083) -- community @theme inline patterns
- [GitHub Discussion: Dark-Mode-Specific CSS Variables](https://github.com/tailwindlabs/tailwindcss/discussions/16730) -- :root + .dark recommended workaround
- [Building a Production Design System with Tailwind CSS v4](https://dev.to/saswatapal/building-a-production-design-system-with-tailwind-css-v4-1d9e) -- community best practices
- [Avoiding DOM-Diffing Bugs in Livewire 3](https://medium.com/codex/avoiding-dom-diffing-bugs-in-livewire-3) -- morphing pitfalls
- [Syntax highlighter comparison](https://chsm.dev/blog/2025/01/08/comparing-web-code-highlighters) -- hljs vs Prism vs Shiki
- Design trend sources: Creative Boom, Elementor, Adobe, Muzli, Tubik Studio (2026 font and UI trends)
- Blog layout UX: BdThemes, Schwartz-Edmisten (optimal content width), Marketer Milk (2026 blog designs)
- Dark mode design systems: Siva Designer, Frank Congson, Imperavi, GitLab Pajamas

### Tertiary (LOW confidence)
- [Multi-Theme System with Tailwind CSS v4](https://medium.com/render-beyond/build-a-flawless-multi-theme-ui-using-new-tailwind-css-v4-react-dca2b3c95510) -- React-focused but CSS patterns transfer

---
*Research completed: 2026-01-29*
*Ready for roadmap: yes*
