# Domain Pitfalls: UI Overhaul of a Live Tailwind CSS 4 + Livewire Blog

**Domain:** Visual overhaul of an existing Laravel 11 / Livewire 3 / Tailwind CSS 4 blog
**Researched:** 2026-01-29
**Confidence:** HIGH (based on project source code inspection + verified community reports)

---

## Critical Pitfalls

Mistakes that cause visible breakage on a live site with 2,500+ posts and 160,000+ comments.

---

### Pitfall 1: Legacy Content Rendering Breaks Under New Typography/Prose Styles

**What goes wrong:** The `prose` class from `@tailwindcss/typography` styles all child HTML elements. Twenty years of WordPress content contains arbitrary HTML: inline styles, `<center>` tags, `<font>` tags, nested tables, iframes, `<object>` embeds, hand-coded HTML from 2004-era editors, and WordPress shortcodes. A new typography scale, line-height, or color palette can make old posts unreadable -- cramped code blocks, invisible text on changed backgrounds, broken image layouts, or captions that overlap content.

**Why it happens:** Developers test the new design against recent posts that use modern HTML. Old posts use HTML patterns that no one remembers exist. The `prose` class applies aggressive defaults to headings, lists, blockquotes, images, and tables -- any of which may conflict with inline styles baked into legacy content.

**Consequences:**
- Posts with inline `style="color: #fff"` become invisible on a white background (or vice versa in dark mode)
- Old `<table>` layouts break under prose table styles
- Embedded content (iframes, objects) gets constrained by `max-w-none` or overflow rules
- WordPress shortcode output (`.wp-caption`, `.wp-video`, `.wp-more`) loses its custom styling if class names change
- The `@tailwindcss/typography` plugin does not style h5 or h6 elements out of the box -- legacy posts using deep heading hierarchies lose structure

**Warning signs:**
- Only testing with 5-10 recent posts during development
- No audit of HTML patterns present in the content column
- Changed prose modifiers without checking downstream effects

**Prevention:**
1. **Build a content stress-test page** early in the overhaul. Query the database for posts containing specific HTML patterns: `SELECT id, title FROM posts WHERE content LIKE '%<table%' OR content LIKE '%<center%' OR content LIKE '%style=%' OR content LIKE '%<iframe%' OR content LIKE '%<object%' OR content LIKE '%<font%' LIMIT 50`. Render these in the new design.
2. **Sample across decades.** Pull 3-5 posts from each year (2004, 2008, 2012, 2016, 2020, 2024) and visually inspect.
3. **Keep WordPress content CSS** (`app.css` lines 25-53) intact and test them under the new color palette. These `.wp-caption`, `.wp-video`, `.wp-more` rules are load-bearing.
4. **Add dark mode variants** for every legacy content style. The current CSS has `.dark .wp-caption figcaption` and `.dark .wp-more` but any new overhaul may add elements that need dark counterparts.

**Detection:** Visually inspect the stress-test page after every typography change. Automated tests cannot catch "looks wrong" -- this requires human eyes.

**Which phase should address it:** First phase (design tokens/typography). Establish the content stress-test page before changing any prose styles.

---

### Pitfall 2: Response Cache Serves Stale HTML After CSS Changes

**What goes wrong:** `spatie/laravel-responsecache` caches the **entire HTTP response** including the HTML that references CSS assets. After deploying new styles, cached pages still serve old HTML. If Vite's content hash changes the CSS filename (which it should), old cached HTML references a CSS file that no longer exists -- resulting in completely unstyled pages for visitors.

**Why it happens:** The response cache has a default lifetime of one week. During iterative development on a live site, developers deploy CSS changes and see them on uncached pages, but cached pages (the majority on a popular blog) show broken styles or the old design. The `ClearsResponseCache` concern on Post/Comment models only fires on content changes, not CSS deploys.

**Consequences:**
- Users see completely unstyled pages (broken CSS file reference after Vite rehash)
- Or users see the old design intermixed with new design (partial cache clear)
- Admin thinks deploy failed because the homepage looks unchanged
- Debugging is confusing because local dev works fine (response cache usually disabled locally)

**Warning signs:**
- Testing only in local dev (where response cache is typically disabled)
- No `responsecache:clear` in deployment pipeline
- Forgetting that Vite generates hashed filenames (`app-Bx3f4k.css`)

**Prevention:**
1. **Add `php artisan responsecache:clear` to every deployment** that touches CSS/JS assets. This should be a permanent part of the deploy script.
2. **During active UI development on the live site**, reduce `cache_lifetime_in_seconds` to 1 hour or disable response caching entirely behind a feature flag.
3. **Test with response cache enabled** in staging before going live. Simulate the deploy-clear-verify cycle.
4. **Verify Vite manifest** is updated -- the `@vite()` directive reads from `public/build/manifest.json`. If this file is stale, all pages reference old assets.

**Detection:** After deploying CSS changes, open the site in an incognito window. If it looks unchanged or unstyled, the response cache is serving stale HTML.

**Which phase should address it:** Pre-work / infrastructure phase. Set up the cache invalidation pipeline before any visual changes ship.

---

### Pitfall 3: Dark Mode Coverage Gaps -- The "Forgot to Add dark: Variant" Problem

**What goes wrong:** Every Tailwind utility that sets a color needs a corresponding `dark:` variant. During a redesign that touches dozens of components, it is easy to set `text-gray-900` without adding `dark:text-slate-100`, or change `bg-white` without updating `dark:bg-slate-800`. The result: elements that look correct in light mode but are invisible or jarring in dark mode.

**Why it happens:** Developers often work in light mode by default. Dark mode is tested as an afterthought, or only tested on the homepage, not across all page types (post detail, category archive, tag archive, search results, admin panel, comment threads, pagination, error pages).

**EXISTING BUG IN CODEBASE:** The footer component (`footer.blade.php`) currently has **zero dark mode support**. It uses `bg-gray-100`, `text-gray-600`, and `hover:text-gray-900` with no `dark:` variants. On the dark-themed site, the footer renders as a jarring light rectangle. This is the exact type of oversight the overhaul must catch systematically.

**Consequences:**
- White text on white background (invisible text)
- Dark text on dark background (invisible text)
- Border colors that disappear in one mode
- Focus rings and hover states that are invisible in dark mode
- Inconsistent look across page types -- some pages updated, others not

**Warning signs:**
- Reviewing PRs without toggling dark mode
- Components that have 0 `dark:` prefixes
- New color values that are not in the `@theme` block (hardcoded instead of using design tokens)

**Prevention:**
1. **Enforce a "dual-mode review" rule:** Every component change must be visually verified in both light and dark mode before merge.
2. **Use CSS custom properties for semantic colors** (e.g., `--color-surface`, `--color-text-primary`) that change values in dark mode, reducing the number of `dark:` variants needed. The current `@theme` block defines separate light/dark tokens (`--color-brand-light` vs `--color-dark-bg`) but they are not mode-switching -- they require manual `dark:` prefixes everywhere.
3. **Create a component showcase page** that renders every UI component (post card, navigation, footer, sidebar widgets, pagination, comment thread, flash messages) on a single page for quick visual scanning in both modes.
4. **Audit the admin layout:** The admin layout (`components/admin/layouts/app.blade.php`) currently has **zero dark mode support** (`bg-gray-100`, `bg-white`, hardcoded light colors only). Decide early: will the admin get dark mode too, or remain light-only? This affects how much work the overhaul requires.

**Detection:** Toggle dark mode on every page type after changes. Use browser DevTools "Emulate CSS media feature prefers-color-scheme: dark" for quick toggling.

**Which phase should address it:** Design token phase (establish mode-switching tokens) and then every subsequent phase (enforce dual-mode review).

---

### Pitfall 4: Livewire DOM Morphing Breaks After Markup Restructuring

**What goes wrong:** Livewire 3 uses a DOM morphing engine (Morph) to apply server-rendered HTML diffs to the live page. When a UI overhaul restructures the HTML hierarchy -- wrapping elements in new `<div>`s, changing tag types, reordering siblings -- Morph can mis-identify elements and produce ghost elements, duplicated content, lost form state, or vanishing components.

**Why it happens:** Developers change the HTML structure of Livewire components for visual reasons (adding wrapper divs for grid layouts, changing `<div>` to `<section>`, restructuring conditional blocks) without understanding that Morph tracks DOM nodes by position and `wire:key`. This is the #1 source of "weird UI bugs" in Livewire 3.

**Consequences:**
- Search bar (`livewire/search/search-bar.blade.php`) loses input focus after typing
- Comment section duplicates or vanishes mid-interaction
- Admin CRUD forms lose unsaved data after a Livewire round-trip
- Pagination breaks: clicking page 2 shows page 1 content in a stale DOM
- Elements from one Livewire component "leak" into another after navigation

**Warning signs:**
- Restructuring Livewire component HTML without adding `wire:key`
- Using `@if`/`@endif` to conditionally render large HTML blocks (instead of `x-show`)
- Wrapping existing Livewire content in new structural elements without testing interactivity
- Changing the root element of a Livewire component

**Prevention:**
1. **Add `wire:key` to every dynamic element** in Livewire components -- not just loops. Any element that might appear/disappear or change position needs a stable key.
2. **Prefer `x-show` over `@if`** for toggling visibility within Livewire components. `@if` removes elements from the DOM, confusing Morph. `x-show` hides them via CSS, keeping the DOM stable.
3. **Test interactivity after every structural change.** After restructuring a Livewire component's HTML, test: search typing, comment submission, pagination clicks, form validation, flash messages.
4. **Keep Livewire component root elements stable.** Do not change the root element tag or add/remove root-level wrappers.
5. **Wrap Blade conditional blocks** (`@if`/`@endif` that render HTML) inside a containing `<div>` so the morphing engine has a stable reference point.

**Detection:** After any Livewire component HTML restructuring, manually test all interactive features in that component. DOM morphing bugs are silent -- no errors in console, just wrong visual output.

**Which phase should address it:** Every phase that modifies Livewire component markup. Flag specifically in the admin panel phase (heavy Livewire usage) and comments phase (interactive).

---

### Pitfall 5: Card Layout with No Featured Images -- The Empty Card Problem

**What goes wrong:** Moving from the current list-based layout (`divide-y` borders between text-only post entries) to a card-based grid layout exposes a critical data gap: **the Post model has no `featured_image` field**. All 2,500+ posts lack image data entirely. Card layouts without images look like broken wireframes -- a grid of text boxes with empty space where images should be. This is the single most visible failure mode of the redesign.

**Why it happens:** The project was migrated from WordPress, but featured image data (stored in `wp_postmeta` as `_thumbnail_id` referencing `wp_posts` attachments) was not included in the migration. The Post model's fillable fields are: `wordpress_id`, `user_id`, `title`, `slug`, `content`, `excerpt`, `status`, `published_at`. There is no `featured_image`, `thumbnail`, or `image_url` column.

**Consequences:**
- Cards in a grid with image placeholders look broken or unfinished
- If images are extracted from post content as a fallback, many old posts have no images at all, or have images hosted on dead domains
- Inconsistent card heights in a grid: some cards have images, most do not
- The design looks like a template that was not filled in
- Social sharing cards (Twitter meta already uses `summary_large_image`) will have no image to display

**Warning signs:**
- Designing card mockups with images in every card
- Assuming a migration script can "just pull" featured images from WordPress (the media library relationship is complex)
- Not testing with real data where 80%+ of posts have no image

**Prevention:**
1. **Design the card layout with NO image as the primary state**, not the exception. The image-present card should be the enhancement, not the default.
2. **Create a robust fallback strategy:**
   - First choice: Extract first `<img>` from post content (but verify the URL is still live)
   - Second choice: Category-based placeholder images (e.g., a different color/icon per category)
   - Third choice: A branded placeholder with the post's first letter or a pattern
3. **If adding a `featured_image` column**, plan a migration script that extracts images from post content. But do NOT block the redesign on this -- design for image-absent first.
4. **Test the card grid with real data:** Query for posts with no `<img>` in content (`SELECT COUNT(*) FROM posts WHERE content NOT LIKE '%<img%'`) to understand what percentage of posts are truly imageless.
5. **For variable card heights in grids**, use CSS Grid with `grid-template-rows: masonry` (experimental) or fixed aspect ratios, or avoid image boxes entirely and use accent borders/colors for visual interest.

**Detection:** Load the homepage with real production data. If most cards look identical and empty, the card design needs a no-image-first approach.

**Which phase should address it:** Component design phase. This is an architectural decision that must be made BEFORE designing post cards.

---

## Moderate Pitfalls

Mistakes that cause visual regressions, user complaints, or significant rework.

---

### Pitfall 6: Font Loading Performance -- Layout Shift and Invisible Text

**What goes wrong:** The current `@theme` block declares `'Instrument Sans'` as the primary font but **no `@font-face` declaration or font loading strategy exists** in the CSS or HTML. If the overhaul adds custom fonts (Google Fonts, self-hosted, or variable fonts), improper loading causes two visible problems: (1) FOIT -- Flash of Invisible Text where text disappears for 1-3 seconds while the font loads, and (2) CLS -- Cumulative Layout Shift where the page jumps as the system font is swapped for the custom font.

**Why it happens:** Developers add a Google Fonts `<link>` tag or an `@import` in CSS without considering the loading waterfall. The CSS file must download, then the font file must download, then the browser can render text. On slow connections, this creates a 2-5 second window of broken rendering.

**Consequences:**
- Above-the-fold text invisible for 1-3 seconds on first visit (FOIT)
- Page layout jumps as font metrics differ between system fallback and custom font (CLS)
- Google Core Web Vitals penalties (CLS > 0.1 is "needs improvement")
- Returning visitors may not notice (font cached), but first impressions suffer
- If the font CDN is slow or down, the entire site becomes unreadable

**Warning signs:**
- Adding `<link href="https://fonts.googleapis.com/css2?family=...">`  without `font-display: swap`
- Using `@import url(...)` in CSS (blocks rendering)
- Not testing on throttled connections
- Declaring font-family without a proper fallback stack that matches metrics

**Prevention:**
1. **Self-host fonts** instead of using Google Fonts CDN. Download the font files, place them in `public/fonts/`, and use `@font-face` with `font-display: swap`.
2. **Use `font-display: swap`** always. This shows the fallback font immediately and swaps when the custom font loads -- prevents FOIT.
3. **Add `<link rel="preload" as="font" ...>` in `<head>`** for the primary font weight(s) to start the download before CSS is parsed.
4. **Choose a fallback font with similar metrics** to minimize CLS. Tools like `fontaine` or `capsize` can generate adjusted fallback font declarations.
5. **Test on "Slow 3G"** in Chrome DevTools to make font loading visible during development.
6. **Consider if a custom font is necessary.** The current system font stack (`ui-sans-serif, system-ui, sans-serif`) renders instantly with zero performance cost. A custom font must justify its CLS and loading cost.

**Detection:** Run Lighthouse or PageSpeed Insights. CLS score above 0.1 or LCP above 2.5s indicates font loading issues. Visual test: hard refresh on throttled network and watch for text flash.

**Which phase should address it:** Design tokens / typography phase. Font loading strategy must be decided before any typography work.

---

### Pitfall 7: Tailwind v4 Default Changes Silently Alter Existing Components

**What goes wrong:** Tailwind CSS v4 changed several defaults from v3: border color changed from `gray-200` to `currentColor`, ring width from `3px` to `1px`, ring color from `blue-500` to `currentColor`, and buttons use `cursor: default` instead of `cursor: pointer`. Any component that relies on these implicit defaults will look different after changes to the `@theme` block or Tailwind config, even if those components were not intentionally touched.

**Why it happens:** The project is already on Tailwind v4 (confirmed by `@import 'tailwindcss'` in `app.css` and `@tailwindcss/vite` in `vite.config.js`), so a version upgrade is not the issue. However, during an overhaul, developers may restructure the `@theme` block, update `@tailwindcss/typography`, or write new components that rely on v4 defaults without realizing they differ from v3 conventions. Existing components that use bare `border` (without explicit color) will suddenly show `currentColor` borders instead of `gray-200`.

**Prevention:**
1. **Always specify border and ring colors explicitly.** Search all Blade files for bare `border`, `ring`, `divide` without color modifiers and add explicit colors.
2. **Add `cursor-pointer` to all interactive elements** (buttons, clickable cards) since v4 defaults buttons to `cursor: default`.
3. **Audit existing components for implicit defaults** before the overhaul begins. Run: `grep -rn 'class=".*\bborder\b' --include="*.blade.php"` to find bare border usage.

**Detection:** Side-by-side comparison of components before and after `@theme` changes. Look specifically at borders, rings, and button cursors.

**Which phase should address it:** Pre-work audit before any component changes.

---

### Pitfall 8: Custom Header/Footer Code Injection Breaks Under New Layout

**What goes wrong:** The layout renders `Setting::getValue('custom_header_code')` and `Setting::getValue('custom_footer_code')` which can contain arbitrary HTML, CSS, and JavaScript injected by the site admin. A layout restructuring may place these injections in a different DOM context, breaking their CSS selectors or JavaScript assumptions (e.g., scripts that query by specific element IDs or class names).

**Why it happens:** These injected code blocks are invisible in the codebase -- they live in the database `settings` table. Developers restructure the `<head>` or `<body>` layout without considering that unknown HTML/JS/CSS is being injected at lines 24 and 52-53 of `layout.blade.php`.

**Prevention:**
1. **Query the `settings` table** for `custom_header_code`, `custom_footer_code`, and `analytics_code` values. Review what is currently injected before touching the layout.
2. **Keep injection points in the same relative positions** (header code in `<head>` after Vite assets, footer code before closing `</body>` after Livewire scripts).
3. **Test with actual injected content** in the staging environment, not just empty values.

**Detection:** After layout changes, verify that analytics tracking fires correctly (check network tab for beacon/pixel requests) and that any custom widgets/embeds in header/footer still render.

**Which phase should address it:** Layout restructuring phase (when touching the main layout template).

---

### Pitfall 9: Widget HTML Content Breaks Under New Prose/Typography Styles

**What goes wrong:** HTML widgets (`type: 'html'` and `type: 'about'`) render raw HTML inside `prose prose-sm dark:prose-invert` containers in the sidebar. This HTML was authored in the admin panel without knowledge of the new typography styles. Changed prose defaults (font size, line height, heading scale, link colors) can make widget content look wrong -- especially because the sidebar is only 320px wide (`lg:w-80`), very different from the main content area.

**Why it happens:** Widget HTML is user-authored content stored in the database, similar to post content but rendered at a different width and with `prose-sm` (smaller scale). Prose modifications that look fine in the main content area may look cramped, overflow, or clip in the narrow sidebar context.

**Prevention:**
1. **Test widget rendering separately** from post content. The sidebar is `lg:w-80` (320px) -- prose styles at this width behave differently than in the main content area.
2. **Query for active HTML widgets** and review their content before changing prose styles.
3. **Consider isolating widget prose styles** from post prose styles if they need different treatment (e.g., different link colors, different image handling).

**Detection:** After any typography changes, check the sidebar on a page that has active HTML/about widgets.

**Which phase should address it:** Typography/component phase, when prose styles are modified.

---

### Pitfall 10: Design Token Rename Breaks All Existing References

**What goes wrong:** The current `@theme` block defines custom colors like `--color-brand-primary`, `--color-brand-accent`, `--color-brand-light`. Every Blade template references these as `text-brand-primary`, `hover:text-brand-accent`, etc. If the overhaul renames these tokens (e.g., to `--color-primary`, `--color-accent`), **every reference in every Blade file silently breaks** -- Tailwind v4 simply will not generate the utility class, resulting in unstyled elements with no build error.

**Why it happens:** Tailwind v4's CSS-first configuration means design tokens are CSS custom properties. Renaming a property does not produce a build error. The old class names (`text-brand-primary`) just stop working -- they become no-ops. This is especially dangerous because there is no compile-time validation.

**Prevention:**
1. **If renaming design tokens**, do a project-wide search for the old token name first. Every `.blade.php` file using `text-brand-primary`, `bg-brand-accent`, `hover:text-brand-primary` etc. must be updated simultaneously.
2. **Prefer aliasing over renaming** during the transition. Keep old token names pointing to new values until all references are updated, then remove the aliases.
3. **Test navigation, sidebar, post cards, and tag badges** specifically -- these are the components in this codebase that reference `brand-primary` and `brand-accent` most heavily.

**Detection:** After changing any `@theme` values, visually scan the homepage (uses `brand-primary` in post cards), navigation (uses `brand-primary`), and sidebar widgets (uses `brand-primary` in category/tag links). If text appears in default black instead of the brand color, the token reference is broken.

**Which phase should address it:** Design token phase. This is a comprehensive find-and-replace operation that must happen atomically.

---

### Pitfall 11: Code Block Styling Conflicts with Prose and Dark Mode

**What goes wrong:** The current codebase has **zero custom code block styling** -- no Prism, no Shiki, no highlight.js, no custom `<pre>/<code>` CSS. The `@tailwindcss/typography` prose class provides basic code styling, but it is minimal: gray background, small monospace font, and inline code gets backtick-style formatting. When the overhaul adds a proper code highlighting theme, it will conflict with prose's code styles in unpredictable ways -- especially in dark mode where prose's `dark:prose-invert` inverts colors that the syntax theme also tries to control.

**Why it happens:** Code highlighting libraries (Prism, highlight.js, Shiki) apply their own background colors, text colors, and padding to `<pre>` and `<code>` elements. The `prose` class also styles these same elements. The result is a specificity war where the winner depends on CSS load order, and dark mode doubles the conflict surface.

**Consequences:**
- Code blocks have double backgrounds (prose gray + highlighter dark)
- Inline `<code>` in paragraphs gets syntax highlighting unexpectedly
- Dark mode inverts the syntax theme colors, making highlighted code unreadable
- Old posts with `<pre>` blocks that are not code (ASCII art, formatted text) get syntax-highlighted incorrectly

**Warning signs:**
- Adding a syntax highlighting library without overriding prose's code styles
- Testing code blocks only in light mode
- Not distinguishing between inline `<code>` and block `<pre><code>`

**Prevention:**
1. **Override prose code styles explicitly** when adding a syntax highlighter. Use `prose-pre:p-0 prose-pre:bg-transparent` (or equivalent) to let the highlighter own `<pre>` styling entirely.
2. **Scope syntax highlighting to block code only.** Use `prose code:not(pre code)` for inline code styling and let the highlighter handle `pre code`.
3. **Test dark mode independently.** If using `dark:prose-invert`, it will invert code text colors. The syntax highlighter's dark theme must be loaded separately, not rely on prose inversion.
4. **Consider server-side highlighting** (Shiki via Torchlight or similar) to avoid client-side JS and produce deterministic HTML with inline styles that override prose.
5. **Audit legacy content for `<pre>` usage.** Old WordPress posts may use `<pre>` for non-code content (formatted text, ASCII art). These should not receive syntax highlighting.

**Detection:** Find posts containing code blocks (`SELECT id, title FROM posts WHERE content LIKE '%<pre%' OR content LIKE '%<code%' LIMIT 20`) and verify they render correctly in both light and dark mode.

**Which phase should address it:** Typography/component phase. Must be addressed before shipping any prose changes that affect `<pre>/<code>`.

---

### Pitfall 12: Content Width Change Breaks Embedded Content

**What goes wrong:** The current layout uses `flex-1 min-w-0` for the content area when the sidebar is present, and full `container` width when absent. Moving to a card-based grid or changing the content column width causes embedded content (iframes, videos, tables wider than the new container) to overflow. WordPress-migrated content often has hardcoded pixel widths on images, iframes, and tables (e.g., `<iframe width="640" height="480">`, `<img width="600">`).

**Why it happens:** Legacy WordPress content frequently has fixed-width embeds. The WordPress editor added explicit `width` and `height` attributes to images and embeds. When the content container shrinks (from ~800px to ~600px in a card layout, or from full-width to sidebar-constrained), these elements overflow their container.

**Consequences:**
- Horizontal scrollbar appears on posts with wide embeds
- Images extend beyond the content boundary and overlap the sidebar
- Tables with fixed column widths become unreadable with horizontal scroll
- Videos may render at wrong aspect ratios if `width` is constrained but `height` is fixed

**Warning signs:**
- Changing content area width without testing old posts
- No `max-w-full` or `overflow-x-auto` rules on content containers
- Posts with `<iframe>`, `<table>`, or `<img>` with explicit pixel dimensions

**Prevention:**
1. **Add overflow protection to the prose container:** `prose` already has `max-w-none` in `show.blade.php`, but add `overflow-x-auto` to the content wrapper so wide content scrolls instead of breaking layout.
2. **Add responsive embed CSS:**
   ```css
   .prose img { max-width: 100%; height: auto; }
   .prose iframe { max-width: 100%; }
   .prose table { display: block; overflow-x: auto; }
   ```
3. **Test with the widest content.** Query for posts with wide embeds: `SELECT id, title FROM posts WHERE content LIKE '%width="6%' OR content LIKE '%width="7%' OR content LIKE '%width="8%' LIMIT 20`.
4. **If the sidebar changes width or is added/removed conditionally**, test content at every possible container width.

**Detection:** Open posts from 2008-2015 (peak era of fixed-width YouTube embeds and wide images) at various viewport widths. Check for horizontal overflow.

**Which phase should address it:** Layout restructuring phase, as a defensive CSS addition before changing any widths.

---

### Pitfall 13: Threaded Comments Visual Hierarchy Collapses

**What goes wrong:** 160,000+ comments with threading (parent_id for self-referential comments) depend on visual indentation and border/background differentiation to show hierarchy. A redesign that changes spacing, border colors, or background colors can flatten the visual hierarchy, making it impossible to tell which comment replies to which.

**Why it happens:** Comment threading is typically implemented with nested `margin-left` or `padding-left` and alternating background shades. Changing the base spacing scale or the background color palette without testing deeply nested threads (3-4+ levels) produces unreadable comment sections. At mobile widths, deep nesting is especially problematic.

**Prevention:**
1. **Find posts with deeply threaded comments** for testing: `SELECT post_id, COUNT(*) as c FROM comments WHERE parent_id IS NOT NULL GROUP BY post_id ORDER BY c DESC LIMIT 5`
2. **Test at mobile widths.** Nested indentation that works at 1024px breaks at 375px -- four levels of `ml-8` consumes the entire viewport width.
3. **Ensure each nesting level is visually distinguishable** in both light and dark mode. Use a combination of indentation + border-left color + subtle background shade.
4. **Cap visual nesting depth.** After 3-4 levels, flatten the indentation but keep a "replying to @username" indicator.

**Detection:** Load the highest-comment posts and look at threading on both desktop and mobile viewports.

**Which phase should address it:** Comment section redesign phase.

---

### Pitfall 14: wire:navigate Causes CSS Persistence Between Pages

**What goes wrong:** If any page uses `wire:navigate` for SPA-like navigation, Livewire only replaces the `<body>` content -- the `<head>` (including stylesheets) is loaded once and persists. Page-specific styles from the previous page bleed into the next page. Styles added to `<head>` during navigation are never removed.

**Why it happens:** Livewire's `wire:navigate` creates an SPA experience where, from the browser's perspective, you never leave the original page. The `<head>` element (containing `<link>` and `<style>` tags) persists across navigations. This is by design for performance, but it means CSS loaded for page A remains active on page B.

**Consequences:**
- Styles from admin pages leak into public pages (or vice versa) if both use `wire:navigate`
- Dark mode toggles may not re-initialize properly after navigation
- The FOUC prevention script (lines 8-14 of `layout.blade.php`) runs once, not on every navigation

**Prevention:**
1. **Consolidate all CSS into a single shared entry point** (the project already does this with `@vite(['resources/css/app.css'])` -- do not introduce page-specific stylesheets).
2. **If the admin panel needs different styles**, ensure admin navigation does not use `wire:navigate` to cross between admin and public layouts, or use separate layouts entirely.
3. **Test the dark mode toggle** after `wire:navigate` transitions -- ensure Alpine.js state survives correctly.

**Detection:** Navigate between pages using `wire:navigate` links and check that styles are consistent. Toggle dark mode, navigate, and verify it persists.

**Which phase should address it:** Layout restructuring phase, especially if introducing any new CSS entry points.

---

### Pitfall 15: Responsive Regression -- Navigation Breaks at Mid-Width Viewports

**What goes wrong:** The current navigation uses a simple horizontal flex layout with no mobile hamburger menu. The nav items (`flex items-center gap-6`) plus the search bar (`w-full max-w-xs sm:w-64`) plus the site name compete for space. At tablet widths (768px-1024px), the nav items wrap awkwardly or overflow. Adding more visual elements during the redesign (logo image, larger font sizes, new nav items) pushes this past the breaking point.

**Why it happens:** The navigation is tested at two extremes: desktop (1280px+) and mobile (375px). The "problem zone" is 600px-900px where the horizontal layout technically fits but looks cramped, items stack unpredictably, or the search bar gets squeezed to an unusable width. The current design relies on `shrink-0` on the nav which prevents compression but causes overflow.

**Consequences:**
- Nav items overflow the container at tablet width, creating horizontal scroll on the entire page
- Search bar becomes too narrow to be usable
- Tagline (already hidden on `lg:` breakpoint via `hidden lg:block`) may need to be hidden earlier
- If a hamburger menu is added, it must handle the transition smoothly -- partial collapse (some items visible, some in hamburger) is confusing

**Warning signs:**
- Testing only at 375px and 1440px
- Adding visual weight to the header without checking 768px
- Not testing with long menu item labels (DB-driven labels can be any length)

**Prevention:**
1. **Test at every 100px increment from 320px to 1440px.** Use Chrome DevTools responsive mode with "responsive" draggable width, not just device presets.
2. **Plan the mobile navigation strategy** before implementing the desktop design. If a hamburger menu is needed, design mobile-first.
3. **Set a max character limit** for menu item labels or truncate with ellipsis in the UI.
4. **Consider the search bar placement** separately from navigation. On mobile, search may need to be a toggle/overlay rather than inline.

**Detection:** Slowly resize the browser from 1440px down to 320px and watch for the moment the navigation breaks. Note the exact breakpoint.

**Which phase should address it:** Navigation/header component phase. Mobile navigation strategy must be decided before implementing the desktop header redesign.

---

## Minor Pitfalls

Mistakes that cause polish issues or developer friction.

---

### Pitfall 16: FOUC (Flash of Unstyled/Wrong-Mode Content) on Page Load

**What goes wrong:** The page briefly flashes in the wrong color mode before Alpine.js initializes and applies the `.dark` class. The current layout already has a FOUC prevention script (lines 8-14 of `layout.blade.php`), but restructuring the layout can break this by moving the script, changing the element it targets, or introducing a new layout that lacks the script entirely.

**Prevention:**
1. **Keep the FOUC prevention `<script>` as the very first element in `<head>`**, before any CSS loads. It must run synchronously before the browser renders.
2. **Replicate the script** in any new layout file. The admin layout currently lacks it (admin is light-only), but if dark mode is added to admin, it needs the same script.
3. **Test on slow connections** (Chrome DevTools network throttling to "Slow 3G") to make the flash visible during development.

**Detection:** Hard-refresh the page with dark mode preference saved in localStorage. Any flash of white before dark mode applies indicates the script is not running early enough.

**Which phase should address it:** Layout restructuring phase.

---

### Pitfall 17: Admin Panel Shares CSS Bundle with Public Site

**What goes wrong:** Both the public layout (`layout.blade.php`) and admin layout (`admin/layouts/app.blade.php`) load the same Vite entry point: `@vite(['resources/css/app.css', 'resources/js/app.js'])`. Changes to global styles (body background, base typography, link colors, prose styles) affect both public and admin views simultaneously. An admin-only visual change leaks to the public site, or a public redesign breaks admin panel styling.

**Why it happens:** The project uses a single CSS entry point for simplicity. This is fine until the public site and admin panel need divergent visual directions -- which a UI overhaul typically requires.

**Prevention:**
1. **Audit which styles are truly shared** (Tailwind utilities, Alpine directives) vs. which should be scoped (admin-specific card styles, public-specific prose styles).
2. **If the admin gets a separate visual treatment**, consider: (a) a separate CSS entry point (`resources/css/admin.css`), or (b) scoping admin-specific custom CSS under a parent class like `.admin-layout`.
3. **At minimum**, test every admin page after public site style changes, and vice versa. The admin layout wraps everything in `bg-gray-100` with `bg-white` content cards -- changes to these gray/white shades affect admin readability.

**Detection:** After any public site CSS changes, load the admin panel and verify it still looks correct. After any admin changes, load the public site.

**Which phase should address it:** Pre-work / architecture decision. Decide on shared vs. split CSS before the overhaul begins.

---

### Pitfall 18: Pagination Component Styling Gets Overlooked

**What goes wrong:** Laravel's pagination views are vendor files. The `app.css` includes `@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php'` to scan them for Tailwind classes, but the actual pagination HTML uses its own color and spacing conventions. A color palette change that works everywhere else may not carry through to pagination because the pagination views use hardcoded Tailwind classes, not design tokens.

**Prevention:**
1. **Publish Laravel's pagination views** (`php artisan vendor:publish --tag=laravel-pagination`) to get project-local copies that can be styled consistently with the new design.
2. **If already published**, ensure they are included in the overhaul scope -- pagination views are easy to forget because they render only on archive pages with enough content.
3. **Test pagination on category/tag archive pages** with enough posts to generate multiple pages.

**Detection:** Navigate to a category page with 20+ posts and check that pagination controls match the new design language.

**Which phase should address it:** Component redesign phase.

---

### Pitfall 19: Sidebar Conditional Layout Creates Inconsistent Page Widths

**What goes wrong:** The main layout conditionally includes the sidebar (`@if ($hasWidgets)`) with a flex layout. When widgets are active, the content area is narrower (flex-1 minus 320px sidebar). When widgets are deactivated, content spans the full container width. A redesign that looks great at full width may break at the narrower width (or vice versa), and toggling widgets on/off changes the entire page layout.

**Why it happens:** The layout queries `Widget::where('is_active', true)->exists()` at render time. During development, if widgets are disabled, developers test at full width. When widgets are re-enabled in production, the content area shrinks by 320px and the design breaks.

**Prevention:**
1. **Always test with widgets both enabled and disabled** during development.
2. **Test content rendering at both widths** -- especially post content with images, code blocks, and tables that may overflow at the narrower width.
3. **Consider always reserving sidebar space** even when empty (to prevent layout shifts), or ensure the design gracefully handles both states.

**Detection:** Toggle widget active status in the admin panel and reload the public site. Check that content does not overflow or break at the narrower width.

**Which phase should address it:** Layout restructuring phase.

---

### Pitfall 20: Card Grid Variable Heights Create Visual Chaos

**What goes wrong:** When moving from a vertical list (`divide-y`) to a CSS Grid or Flexbox card layout, cards with different content lengths create uneven row heights. Some posts have 10-word excerpts, others have 200-word excerpts. Some have categories, others do not. Without constraints, the grid looks jagged and unprofessional.

**Why it happens:** The current `post-card.blade.php` uses `Str::limit(strip_tags($post->excerpt ?: $post->content), 200)` for excerpt text, but actual length varies: some excerpts are manually written (short), while the fallback strips HTML from content (may produce odd text). Categories are conditional (`@if($post->categories->isNotEmpty())`), adding variable height at the bottom of each card.

**Consequences:**
- Adjacent cards in a grid row have different heights, creating empty space
- Long titles on some cards push content down inconsistently
- Cards without categories are shorter, creating visual holes in the grid
- The grid looks "broken" compared to the clean list layout

**Prevention:**
1. **Enforce fixed card heights** or at minimum fixed content sections. Use `line-clamp-2` on titles and `line-clamp-3` on excerpts to normalize text height.
2. **Always render the category section**, even if empty (use a fixed-height container with `min-h-[28px]` or similar).
3. **Use CSS Grid with `grid-auto-rows: 1fr`** to equalize row heights, or Flexbox with `items-stretch`.
4. **Test with real data diversity.** Create a test page showing 12 cards: some with long titles, short titles, no excerpt, long excerpt, many categories, no categories.
5. **Consider a "masonry" approach** only if the design calls for it -- masonry is still experimental in CSS Grid and requires JS polyfills for cross-browser support.

**Detection:** Load the homepage grid with real production data and look for jagged card heights. Check at both desktop (3 columns) and tablet (2 columns) widths.

**Which phase should address it:** Component design phase, when building the post card component.

---

## Phase-Specific Warnings

| Phase Topic | Likely Pitfall | Mitigation |
|-------------|---------------|------------|
| Design tokens / color palette | Token rename breaks all existing Blade references (Pitfall 10) | Project-wide search-and-replace; alias old names during transition |
| Typography / prose styles | Legacy content becomes unreadable (Pitfall 1) | Content stress-test page with posts from each era |
| Typography / prose styles | Widget HTML breaks in narrow sidebar (Pitfall 9) | Test HTML widgets separately at 320px width |
| Typography / prose styles | Code block styling conflicts (Pitfall 11) | Override prose code styles before adding syntax highlighter |
| Typography / font loading | Font performance causes CLS (Pitfall 6) | Self-host with `font-display: swap` and preload |
| Layout restructuring | Response cache serves stale pages (Pitfall 2) | Add cache clear to deploy pipeline before starting |
| Layout restructuring | FOUC script displaced or missing (Pitfall 16) | Keep script as first `<head>` element in every layout |
| Layout restructuring | Custom code injections break (Pitfall 8) | Review injected content from settings table first |
| Layout restructuring | wire:navigate CSS persistence (Pitfall 14) | Do not introduce page-specific stylesheets |
| Layout restructuring | Sidebar conditional changes page width (Pitfall 19) | Test with widgets both enabled and disabled |
| Layout restructuring | Embedded content overflows new widths (Pitfall 12) | Add overflow-x-auto and max-width CSS to prose containers |
| Component redesign — post cards | No featured images in data model (Pitfall 5) | Design no-image state as primary; image as enhancement |
| Component redesign — post cards | Variable card heights in grid (Pitfall 20) | Use line-clamp and fixed sections; test with real data |
| Component redesign (public) | Dark mode gaps (Pitfall 3) | Dual-mode review on component showcase page |
| Component redesign (public) | Pagination unstyled (Pitfall 18) | Publish and customize pagination views |
| Component redesign (public) | Tailwind v4 default changes (Pitfall 7) | Audit bare border/ring/divide usage |
| Component redesign — navigation | Responsive breakage at mid-widths (Pitfall 15) | Test every 100px from 320px-1440px |
| Admin panel overhaul | Livewire DOM morphing breaks (Pitfall 4) | wire:key everywhere; prefer x-show over @if |
| Admin panel overhaul | Shared CSS bundle leaks changes (Pitfall 17) | Decide on split CSS before starting |
| Comment section | Thread hierarchy lost (Pitfall 13) | Test with deeply nested threads at mobile widths |

---

## Pre-Overhaul Checklist

Before writing any new CSS or modifying any components, complete these steps:

- [ ] Build a content stress-test page with posts spanning all eras of the blog (Pitfall 1)
- [ ] Add `php artisan responsecache:clear` to deployment pipeline (Pitfall 2)
- [ ] Build a component showcase page for dual-mode (light/dark) visual review (Pitfall 3)
- [ ] Fix the existing footer dark mode bug before overhauling further (Pitfall 3)
- [ ] Audit all Blade files for bare `border`/`ring`/`divide` without explicit color modifiers (Pitfall 7)
- [ ] Decide on card layout strategy given zero featured images in the data model (Pitfall 5)
- [ ] Decide on font loading strategy: custom font vs. system font stack (Pitfall 6)
- [ ] Review database-stored HTML: query `settings` table for injected code, query `widgets` table for HTML content (Pitfalls 8, 9)
- [ ] Decide on shared vs. split CSS for admin and public layouts (Pitfall 17)
- [ ] Publish Laravel pagination views if not already done (Pitfall 18)
- [ ] Identify posts with deeply threaded comments for testing (Pitfall 13)
- [ ] Audit legacy content for embedded content with fixed pixel widths (Pitfall 12)
- [ ] Test current navigation at all viewport widths to establish baseline breakpoints (Pitfall 15)

---

## Sources

- [Tailwind CSS v4 Upgrade Guide](https://tailwindcss.com/docs/upgrade-guide) -- official documentation on breaking default changes (HIGH confidence)
- [Tailwind CSS v4 Missing Defaults and Broken Dark Mode](https://github.com/tailwindlabs/tailwindcss/discussions/16517) -- community reports of dark mode breakage after v4 migration (HIGH confidence)
- [Fix Dark Classes Not Applying in Tailwind CSS v4](https://www.sujalvanjare.com/blog/fix-dark-class-not-applying-tailwind-css-v4) -- @custom-variant dark mode fix for v4 (MEDIUM confidence)
- [Avoiding DOM-Diffing Bugs in Livewire 3](https://medium.com/codex/avoiding-dom-diffing-bugs-in-livewire-3-how-to-prevent-disappearing-elements-lost-input-focus-e3c4f692859b) -- DOM morphing pitfalls and prevention (MEDIUM confidence)
- [Livewire wire:navigate Stylesheet Discussion](https://github.com/livewire/livewire/discussions/6037) -- CSS persistence between pages (HIGH confidence, official repo)
- [Livewire Navigate Documentation](https://livewire.laravel.com/docs/3.x/navigate) -- official docs on wire:navigate behavior (HIGH confidence)
- [@tailwindcss/typography GitHub](https://github.com/tailwindlabs/tailwindcss-typography) -- prose class behavior with CMS content (HIGH confidence)
- [Spatie Laravel ResponseCache](https://github.com/spatie/laravel-responsecache) -- cache invalidation patterns (HIGH confidence)
- [Tailwind CSS Dark Mode Documentation](https://tailwindcss.com/docs/dark-mode) -- official dark mode strategies (HIGH confidence)
- [Web.dev CLS documentation](https://web.dev/cls/) -- Cumulative Layout Shift and font loading impact (HIGH confidence)
- Project source code inspection: `app.css`, `layout.blade.php`, `show.blade.php`, `post-card.blade.php`, `sidebar-widgets.blade.php`, `admin/layouts/app.blade.php`, `vite.config.js`, `Post.php`, `footer.blade.php`, `navigation.blade.php` (HIGH confidence)
