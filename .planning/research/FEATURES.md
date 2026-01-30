# Feature Landscape: ShoeMoney Blog UI Overhaul (v2.0)

**Domain:** Personal brand blog for internet entrepreneur (20+ years of content, 2,500+ posts)
**Researched:** 2026-01-29
**Mode:** Ecosystem (UI features and design patterns for bold personal brand)
**Context:** Subsequent milestone -- existing blog is functional, this is a visual redesign

---

## Design Decisions Already Made

These were established before research and constrain feature scope:

| Decision | Value | Implication |
|----------|-------|-------------|
| Post layout | Card-based grid | Replace current divider-separated list |
| Display typography | Geometric sans-serif (e.g., Space Grotesk) | Need font loading strategy |
| Content width | 750-800px for body text | Constrain `prose` container |
| Body text size | 18-20px | Override Tailwind prose defaults |
| Whitespace | Generous | Increase spacing tokens across the board |
| Header | Static (not sticky) | Simpler implementation than sticky-on-scroll |
| Footer | Minimal | Do NOT over-engineer footer; keep it lean |
| Pagination | "Load More" button | Replace Laravel paginator links |
| Brand colors | Royal blue + black (Superman-style) | Bold, high-contrast palette |

These decisions are NOT up for debate in the feature landscape. Features below work within these constraints.

---

## Current State Assessment

Review of existing Blade templates confirms:

- **Homepage** (`posts/index.blade.php`): `h1` "Latest Posts" with `divide-y` list of post cards. No hero, no grid, no visual identity.
- **Post card** (`post-card.blade.php`): Plain `article` with border-bottom. Title, date/author/read-time metadata, excerpt, category links. No images, no badges, no hover effects.
- **Post detail** (`posts/show.blade.php`): Title, metadata row, category badges, `prose prose-lg` content, tags footer, comments. No progress bar, no sharing, no related posts, no author bio card.
- **Navigation** (`navigation.blade.php`): Horizontal text links, search bar, theme toggle. No mobile hamburger menu -- links overflow on small screens.
- **Footer** (`footer.blade.php`): 20 lines. Copyright + Privacy/Terms links. No social, no newsletter, no brand reinforcement.
- **Layout** (`layout.blade.php`): `bg-white dark:bg-slate-900` body. Container with optional sidebar. No semantic color tokens.

---

## Table Stakes

Features users expect from a modern blog in 2026. Missing any of these makes the site feel dated or incomplete.

| # | Feature | Why Expected | Complexity | Depends On | Current State |
|---|---------|--------------|------------|------------|---------------|
| 1 | **Semantic color system (CSS custom properties)** | Foundation for everything else. Colors defined as `--color-surface`, `--color-text-primary`, `--color-accent` etc. Light and dark palettes swap token values, not individual classes. Without this, every component needs duplicate `dark:` classes that drift out of sync. | Medium | Nothing (foundational) | Hard-coded Tailwind colors with `dark:` prefix inversion |
| 2 | **Typography system** | Display font (geometric sans-serif like Space Grotesk) for headings + clean body font (Inter or system). Strict type scale: 4 heading sizes, 1 body, 1 small. Two weights max per typeface. Font loaded via Google Fonts or self-hosted WOFF2. | Low | Nothing (foundational) | System fonts / Tailwind defaults |
| 3 | **Content width constraint** | 750-800px max for body text (approximately 50-75 characters per line). UX consensus: 66 chars/line is ideal for reading comprehension. | Low | Nothing | Unconstrained `flex-1 min-w-0` fills all available space |
| 4 | **Responsive mobile navigation** | 60%+ traffic is mobile. Hamburger menu with slide-out or dropdown. Alpine.js toggle is sufficient -- no heavy JS library needed. | Low | Nothing | Nav links overflow/wrap on small screens, no hamburger |
| 5 | **Card-based post grid** | Replace divider list with visual cards. CSS Grid with `repeat(auto-fit, minmax(min(100%, 320px), 1fr))` for responsive columns. Cards need: category badge, title in display font, excerpt, metadata row. Subtle shadow or border with hover lift. | Medium | Typography, Color system | Flat text list with `divide-y` dividers |
| 6 | **Dark mode design system** | Current dark mode is naive color inversion (`dark:bg-slate-900`). Proper approach: semantic tokens that swap values. Accent colors need reduced brightness for dark backgrounds. Shadows become lighter/softer. Images may need reduced brightness filter. Must pass WCAG AA contrast (4.5:1 body text). | Medium | Color system | Functional but not designed |
| 7 | **"Load More" pagination** | Replace Laravel's default pagination links with a single "Load More" button. Livewire makes this straightforward -- increment page count, append results. Keep URL state for back-button support. | Low | Card grid | Standard Laravel pagination links |
| 8 | **Featured image support with fallback** | Posts with images: show image on card and detail page. Posts without images (majority of 2,500+ legacy posts): graceful fallback. Options: (a) category-colored gradient header, (b) typography-only card (no image area), (c) branded placeholder. Recommend option (b): do NOT show placeholder images. Let cards without images be text-focused -- this is honest and avoids stock-photo feel. | Medium | Color system, Card grid | No image support anywhere |
| 9 | **Social sharing buttons** | Minimum: X/Twitter, LinkedIn, copy link. Appear on post detail page after content. Use native share URLs (no third-party JS). Copy-to-clipboard via Alpine.js. | Low | Nothing | Missing entirely |
| 10 | **Reading progress indicator** | Thin bar at top of viewport showing scroll position through article. Brand color. CSS-only or lightweight Alpine.js scroll listener. Fixed position, appears only on post detail pages. | Low | Color system | Missing |
| 11 | **Related posts section** | 3-card grid after article content, before comments. Query by shared categories/tags. Falls back to recent posts if no matches. Keeps readers on-site. | Medium | Card grid | Posts end with tags then comments |
| 12 | **Author bio card** | Photo, name, one-liner bio, social links. End of every post. Reinforces personal brand. | Low | Typography | Just "By [name]" text |
| 13 | **Back-to-top button** | Floating button, appears after scrolling down. Essential for long-form posts (2,500+ words common). Alpine.js scroll listener. | Low | Nothing | Missing |
| 14 | **Accessible color contrast** | WCAG AA minimum (4.5:1 body text, 3:1 large text). Must audit both light and dark palettes. Royal blue on white and on near-black both need verification. | Low | Color system | Needs audit -- gray-500/600 text may fail |
| 15 | **Code block styling** | Technical blog with code examples. Need syntax highlighting (Prism.js or highlight.js), dark/light theme-aware, copy button, language label. Rounded corners, distinct background color from page surface. Horizontal scroll for long lines. | Medium | Color system, Dark mode | Default `prose` code styling only |
| 16 | **Warm background tones** | Replace pure `bg-white` with warm off-white (e.g., `#FAFAF8` or `slate-50`). Reduces eye strain for content-heavy reading. Dark mode uses warm dark gray instead of cold slate. | Low | Color system | Pure `bg-white` / `dark:bg-slate-900` |

---

## Differentiators

Features that transform the site from "a blog" to "THE ShoeMoney blog." These create brand identity and memorable experience.

### Tier 1: Identity (Makes it feel like a brand)

| # | Feature | Value Proposition | Complexity | Depends On | Notes |
|---|---------|-------------------|------------|------------|-------|
| 17 | **Bold homepage hero section** | First impression IS the brand. Large photo, punchy headline ("I built a $17M business from a blog"), single CTA. Full-bleed brand-color background breaks the container. This is what every strong personal brand does. Without it, the site is "just a blog." | Medium | Color system, Typography | Currently: generic "Latest Posts" h1 |
| 18 | **Signature color palette applied to structure** | Royal blue + black is not just for links. Brand color on hero background, category badges, button fills, progress bar, blockquote borders. Monochromatic boldness -- one color pushed to extremes. This is the 2026 trend: own your color. | Medium | Color system | `brand-primary` exists but only used on link hover |
| 19 | **Custom display typography** | Space Grotesk (or similar geometric sans-serif) for headings. This typeface becomes brand recognition -- readers associate the font with ShoeMoney. Available on Google Fonts, 5 weights, retro-future character, works great at large sizes. Pair with Inter for body. | Low | Font loading setup | System fonts |
| 20 | **"As Seen In" credibility bar** | Social proof strip with logos of publications/podcasts/events. Jeremy sold AuctionAds for $17M. Instantly communicates authority. Low complexity: static image row with flexbox. | Low | Nothing | Not present |

### Tier 2: Engagement (Keeps people on-site)

| # | Feature | Value Proposition | Complexity | Depends On | Notes |
|---|---------|-------------------|------------|------------|-------|
| 21 | **Featured/pinned posts section** | "Start Here" or "Best Of" -- curated 3-5 cornerstone posts on homepage below hero. Guides new visitors through 20 years of content. Admin toggle: `is_featured` boolean on posts table. | Medium | Card grid, Admin field | No featured post system |
| 22 | **Category color coding** | Each major category gets an accent color. Category badges on cards and detail pages use these colors. Creates visual variety while maintaining system consistency. Admin: color picker per category. | Low | Color system, Admin field | Categories shown as plain text |
| 23 | **Newsletter signup CTA** | Email capture in 2 locations: hero section and post-end. Brand-colored form. Not aggressive -- appears after content delivery. Integration with provider (Mailchimp, ConvertKit, etc.) is Phase 4+; start with form UI that stores emails locally. | Medium | Color system | No email capture anywhere |
| 24 | **Scroll-triggered fade-in animations** | Elements fade/slide in as they enter viewport via Intersection Observer. Adds energy and premium feel. MUST respect `prefers-reduced-motion`. Keep subtle: 200-300ms duration, small translate distances (10-20px). | Low | Nothing | Zero animations |

### Tier 3: Polish (Makes it feel premium)

| # | Feature | Value Proposition | Complexity | Depends On | Notes |
|---|---------|-------------------|------------|------------|-------|
| 25 | **Full-bleed section backgrounds** | Hero, CTAs, and footer break out of container for visual impact. Creates rhythm while scrolling -- contained content alternates with full-width brand sections. | Low | Layout restructure | Everything trapped in `container mx-auto` |
| 26 | **Hover micro-interactions** | Card lift on hover (translateY + shadow increase), button state transitions, link underline animations. 150-200ms transitions. These separate "working website" from "designed experience." | Low | Card grid | Hard static states |
| 27 | **Animated stat counters** | "2,500+ posts. 20+ years. Millions earned." Counters animate on scroll into view. One-time intersection observer trigger. Communicates scale. | Low | Hero section | Nothing |
| 28 | **Breadcrumb navigation** | Structured data for SEO (Google shows breadcrumbs in search results). Appears on post/category/tag pages. Schema.org BreadcrumbList markup. | Low | Nothing | Missing |

---

## Anti-Features

Design choices to deliberately avoid. Common mistakes in the personal brand blog space.

| # | Anti-Feature | Why Avoid | What to Do Instead |
|---|--------------|-----------|-------------------|
| 1 | **Sticky header on scroll** | Decision already made: static header. Sticky headers consume vertical space on mobile, add scroll-listener complexity, and fight with reading progress bar for top-of-viewport real estate. For a reading-focused blog, let the header scroll away and give content full screen. | Static header that scrolls with page. Back-to-top button provides navigation recovery. |
| 2 | **Aggressive popups on page load** | Interrupting before delivering value is hostile UX. Google penalizes intrusive interstitials on mobile. | Inline CTAs after content. Post-end newsletter form. Never interrupt reading. |
| 3 | **Sidebar on every page** | Current two-column layout with 320px sidebar constrains content width. For a reading-focused redesign, sidebar is clutter on post detail pages. On homepage, card grid replaces the need for sidebar discovery. | Remove sidebar from post detail. Homepage uses card grid. Move useful widgets (about, newsletter) into footer or dedicated sections. Keep sidebar optional on archive pages only if needed. |
| 4 | **Dark mode as simple color inversion** | Just flipping colors looks cheap. Accent colors that work on white rarely work on dark backgrounds. Shadows invert wrong. | Semantic color tokens with intentional dark palette. Reduce accent brightness 10-15% for dark mode. Use lighter shadows on dark backgrounds. |
| 5 | **Tiny text for "elegant" feel** | Under-16px body is unreadable for sustained reading. ShoeMoney audience skews older (20-year readership). | 18-20px body minimum. 1.6 line-height. Decision already made. |
| 6 | **Auto-playing video** | Wastes bandwidth, annoys users, kills LCP scores. | Thumbnail with play button if video content is added later. |
| 7 | **Carousel/slider for featured posts** | Low engagement (users see slide 1 only). Adds JS complexity. Accessibility nightmare. | Static grid of 3-5 featured cards, all visible simultaneously. |
| 8 | **Infinite scroll** | Breaks back button, prevents footer access, impossible to find "that post from page 3." Decision already made: "Load More" button. | "Load More" button that appends posts. Maintains URL state. |
| 9 | **Parallax scrolling** | Dated (2015 trend). Causes motion sickness. Performance cost. | Subtle fade-in-on-scroll via Intersection Observer. Respect `prefers-reduced-motion`. |
| 10 | **Social media feed embeds** | Slow loading, layout breaks when APIs change (X API unstable), third-party tracking. | Static social icons linking to profiles. No embedded feeds. |
| 11 | **Generic stock photography** | Nothing kills personal brand faster than stock photos. This is Jeremy's blog. | Typography-driven design (bold type on colored backgrounds). Personal photos only when available. Cards without images should look intentional, not broken. |
| 12 | **Cookie-cutter Tailwind UI** | Current site looks like every `create-laravel-app` output. Component libraries without heavy customization scream "template." | Custom border-radius (e.g., 12px not default 8px), custom shadow values, distinctive spacing rhythm, brand colors on structural elements. Design system should feel bespoke. |
| 13 | **Too many font weights/sizes** | 6+ different sizes creates visual chaos. | Strict type scale defined in Tailwind config. 3-4 heading sizes, 1 body, 1 small. |
| 14 | **Stuffed sidebar with 5+ widgets** | "Sidebar junk drawer." Decision fatigue. Low engagement on most widgets. | If sidebar exists on any page, curate to 2-3 max. Better: move content into purpose-built sections. |

---

## Feature Dependencies

```
FOUNDATIONAL (must come first, everything depends on these):
  Color System (semantic tokens) ──┬──> Dark mode proper palette
                                   ├──> Card grid styling
                                   ├──> Hero section
                                   ├──> Code block themes
                                   ├──> Category color coding
                                   └──> All button/link states

  Typography System ───────────────┬──> Card titles
                                   ├──> Hero headline
                                   ├──> Post detail reading experience
                                   └──> Navigation visual weight

STRUCTURAL (layout changes):
  Layout restructure ──────────────┬──> Full-bleed sections (hero, footer)
  (remove sidebar default,        ├──> Card grid on homepage
   allow full-width sections)     └──> Content width constraint on posts

COMPONENT (built on foundations):
  Card Grid ───────────────────────┬──> Featured image support
                                   ├──> "Load More" pagination
                                   ├──> Featured posts section
                                   └──> Related posts section

  Hero Section ────────────────────┬──> Newsletter CTA (hero placement)
                                   ├──> "As Seen In" bar (below hero)
                                   └──> Animated counters

  Post Detail ─────────────────────┬──> Reading progress bar
                                   ├──> Social sharing
                                   ├──> Author bio card
                                   └──> Code block styling
```

**Critical path:** Color System + Typography System are the two pillars. Every visual component depends on knowing the exact colors and fonts. Do these first, validate in browser, then build components on top.

---

## Specific Pattern References

### Card Grid Pattern (CSS Grid, responsive)

```css
.post-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr));
  gap: 2rem;
}
```

Card anatomy:
```
+----------------------------------+
| [CATEGORY BADGE - colored]       |
| Post Title in Display Font       |
| Two lines of excerpt text that   |
| gives a taste of the content...  |
|                                  |
| Jan 15, 2026  ·  5 min read     |
+----------------------------------+
```

- NO featured image area on cards for posts without images (majority of archive). Cards without images are text-focused by design.
- Cards WITH images: image as full-width card header with `object-fit: cover` and fixed aspect ratio (16:9 or 3:2).
- Hover: `translateY(-4px)` + shadow increase. 200ms transition.
- Category badge uses category accent color (when color coding is implemented).

### Post Detail Reading Experience

```
[====== Progress Bar (brand color) ======------]  <- Fixed top, thin

# Post Title (display font, 36-44px)
By Jeremy Schoemaker  ·  January 15, 2026  ·  8 min read
[Category Badge] [Category Badge]

---

Body text at 18-20px, max-width 750-800px, centered.
Line-height 1.6-1.7. Paragraph spacing 1.5em+.

Code blocks with syntax highlighting, copy button,
language label. Rounded corners. Distinct background.

...article content...

---

[Share: X/Twitter | LinkedIn | Copy Link]

+------------------------------------------+
| [Photo]  Jeremy Schoemaker               |
|          Internet entrepreneur, blogger  |
|          [Twitter] [LinkedIn] [YouTube]  |
+------------------------------------------+

+----------+ +----------+ +----------+
| Related  | | Related  | | Related  |
| Post 1   | | Post 2   | | Post 3   |
+----------+ +----------+ +----------+

+------------------------------------------+
| Enjoyed this? Get the best stuff weekly. |
| [email field] [Subscribe]                |
+------------------------------------------+

--- Comments Section (existing) ---
```

### Code Block Pattern

```html
<div class="code-block relative rounded-xl overflow-hidden my-8">
  <div class="code-header flex justify-between items-center px-4 py-2
              bg-gray-800 dark:bg-gray-950 text-gray-400 text-xs">
    <span class="language-label">javascript</span>
    <button class="copy-btn hover:text-white transition-colors">Copy</button>
  </div>
  <pre class="p-4 overflow-x-auto bg-gray-900 dark:bg-gray-950">
    <code class="language-javascript">...</code>
  </pre>
</div>
```

- Use Prism.js (2KB core, lightweight) or Shiki (build-time, zero runtime).
- Dark background regardless of page theme (code blocks are always dark).
- Rounded corners (12px to match card radius).
- Horizontal scroll for long lines, never wrap code.
- Copy button with "Copied!" feedback.
- Language label in header bar.

### Featured Image Fallback Strategy

For a 20-year archive where most posts lack images:

```
IF post has featured_image:
  Show image at top of card (16:9, object-fit: cover)
  Show image as hero on detail page

ELSE (no image):
  Card: text-only layout (no image area, no placeholder)
  Detail page: title + metadata only (no empty hero area)

DO NOT: show generic placeholder, stock photo, or gradient
```

This is deliberate. Text-only cards look clean when the typography and spacing are strong. Placeholder images look worse than no images.

### Hero Section Pattern

```
+----------------------------------------------------------+
|  [ROYAL BLUE BACKGROUND - full bleed]                    |
|                                                          |
|  [Photo]     BOLD HEADLINE (Space Grotesk, 48-64px)     |
|              Subheadline (Inter, 20-24px)                |
|              [PRIMARY CTA BUTTON - white on blue]        |
|                                                          |
+----------------------------------------------------------+
|  [AS SEEN IN: logo  logo  logo  logo]  <- optional bar  |
+----------------------------------------------------------+
```

- Full-bleed background (breaks out of container).
- Photo is real, not stock. Personality over polish.
- Headline is personality-driven: "I built a $17M business from a blog."
- Single CTA. Never two competing CTAs.

### Footer Pattern (Minimal per design decision)

```
+----------------------------------------------------------+
|  [DARK BACKGROUND - full bleed]                          |
|                                                          |
|  ShoeMoney          [Twitter] [LinkedIn] [YouTube]       |
|  Making Money Online                                     |
|                                                          |
|  (c) 2026 ShoeMoney. All rights reserved.  Privacy|Terms|
+----------------------------------------------------------+
```

- NOT a mega-footer. Minimal per design decision.
- Brand name + tagline, social icons, legal links. That is it.
- Newsletter CTA goes in post-end and hero, NOT footer.

---

## Admin Panel Requirements

The admin panel is not user-facing and should NOT be redesigned. However, these frontend features need admin support:

| Frontend Feature | Admin Requirement | Complexity |
|-----------------|-------------------|------------|
| Featured/pinned posts | `is_featured` boolean on post edit form | Low |
| Featured images | Image upload/URL field on post edit | Medium |
| Hero section content | Settings fields: headline, subheadline, CTA text/URL, photo | Low |
| "As Seen In" logos | Settings field or simple HTML widget | Low |
| Category colors | Color picker on category edit form | Low |
| Social profile URLs | Settings fields (social group) | Low |
| Newsletter form | Settings field for provider integration later; form stores locally first | Low |

---

## MVP Recommendation (Phase Structure)

### Phase 1: Design Foundation
Build the system everything else depends on.
1. Semantic color system (CSS custom properties, light + dark palettes)
2. Typography system (Space Grotesk display + Inter body, type scale in Tailwind config)
3. Warm background tones (off-white light, warm dark)
4. Content width constraint (750-800px for prose)
5. Layout restructure (allow full-bleed sections, remove default sidebar)
6. Responsive mobile navigation with hamburger menu

### Phase 2: Homepage Transformation
Transform the first impression.
7. Bold hero section with photo, headline, CTA (full-bleed)
8. Card-based post grid (CSS Grid, responsive)
9. Featured/pinned posts section ("Start Here")
10. "Load More" pagination replacing default links
11. "As Seen In" credibility bar

### Phase 3: Post Reading Experience
Make the reading experience premium.
12. Reading progress indicator (brand color, fixed top)
13. Post detail typography and width refinement
14. Code block styling with syntax highlighting (Prism.js)
15. Social sharing buttons
16. Author bio card at end of post
17. Related posts section (3-card grid)
18. Back-to-top button

### Phase 4: Engagement and Polish
Add personality and interactivity.
19. Newsletter signup forms (hero + post-end)
20. Category color coding system
21. Scroll-triggered fade-in animations
22. Hover micro-interactions (card lift, button states)
23. Animated stat counters in hero
24. Breadcrumb navigation with Schema.org markup
25. Footer redesign (minimal: brand, social icons, legal)

### Defer to Later Milestones:
- **Content hub / topic cluster pages** -- High complexity, needs content strategy
- **"Most Popular" widget** -- Needs analytics data to populate
- **Keyboard navigation (J/K)** -- Niche, low priority
- **Video support** -- No current video content
- **Branded newsletter slide-in** -- Needs A/B testing infrastructure

---

## Confidence Assessment

| Area | Confidence | Reason |
|------|------------|--------|
| Table stakes features | HIGH | Standard across all modern blogs; confirmed by multiple design trend sources |
| Card grid CSS patterns | HIGH | CSS Grid `auto-fit`/`minmax` is well-documented standard; subgrid has 97%+ browser support |
| Typography recommendations | HIGH | Space Grotesk confirmed available on Google Fonts, 5 weights; font pairing patterns well-established |
| Dark mode design system | HIGH | Semantic token approach confirmed as 2026 best practice by GitLab Pajamas, Style Dictionary, and multiple design system guides |
| Code block styling | HIGH | Prism.js is mature (millions of sites), well-documented; pattern is straightforward |
| Featured image fallback | HIGH | Text-only card approach is design choice, not technical limitation; confirmed by editorial sites that prioritize typography over imagery |
| Differentiator features | MEDIUM | Hero section and "As Seen In" patterns drawn from competitor analysis, but effectiveness depends on execution and content quality |
| Phase ordering | HIGH | Color + typography first is clear dependency; confirmed by feature dependency analysis |

---

## Sources

### CSS Grid and Card Layouts
- [FreeFrontEnd: CSS Card Layouts](https://freefrontend.com/css-card-layouts/)
- [FrontendTools: Modern CSS Layout Techniques 2025-2026](https://www.frontendtools.tech/blog/modern-css-layout-techniques-flexbox-grid-subgrid-2025)
- [MDN: Common Grid Layouts](https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Grid_layout/Common_grid_layouts)

### Typography and Fonts
- [Creative Boom: 50 Fonts Popular in 2026](https://www.creativeboom.com/resources/top-50-fonts-in-2026/)
- [Elementor: Modern Fonts 2026](https://elementor.com/blog/modern-fonts-to-use-on-your-website/)
- [IK Agency: Typography Trends 2026](https://www.ikagency.com/graphic-design-typography/typography-trends-2026/)
- [Design Monks: Best UI Design Fonts 2026](https://www.designmonks.co/blog/best-fonts-for-ui-design)

### Dark Mode Design Systems
- [Siva Designer: Dark Mode Mandatory in 2026](https://www.sivadesigner.in/blog/dark-mode-evolution-modern-web-design/)
- [Frank Congson: Design Tokens to Dark Mode](https://frankcongson.com/blog/design-tokens-to-dark-mode/)
- [Imperavi: Designing Semantic Colors](https://imperavi.com/blog/designing-semantic-colors-for-your-system/)
- [FrontendTools: Tailwind CSS Best Practices 2025-2026](https://www.frontendtools.tech/blog/tailwind-css-best-practices-design-system-patterns)
- [design.dev: Dark Mode CSS Complete Guide](https://design.dev/guides/dark-mode-css/)
- [GitLab Pajamas: Design Tokens](https://design.gitlab.com/product-foundations/design-tokens-using/)

### Code Block Styling
- [Prism.js](https://prismjs.com/)
- [Tania Rascia: Adding Syntax Highlighting](https://www.taniarascia.com/adding-syntax-highlighting-to-code-snippets/)
- [DEV Community: Syntax Highlighting Guide](https://dev.to/ehlo_250/how-to-add-syntax-highlighting-to-code-snippets-on-your-website-app-or-blog-2mi2)

### Design Trends 2026
- [KOTA: Brand Design Trends 2026](https://kota.co.uk/blog/branding-inspiration-brand-design-trends-for-2026)
- [Adobe: Design Trends 2026](https://www.adobe.com/express/learn/blog/design-trends-2026)
- [Muzli: Web Design Trends 2026](https://muz.li/blog/web-design-trends-2026/)
- [Tubik Studio: UI Design Trends 2026](https://blog.tubikstudio.com/ui-design-trends-2026/)

### Blog Layout and Reading UX
- [BdThemes: Modern Blog Layout Design](https://bdthemes.com/best-blog-layout-design-to-rank-on-search-engine/)
- [Schwartz-Edmisten: Optimal Blog Post Width](https://schwartz-edmisten.com/blog/the-scientifically-optimal-blog-post-width)
- [Marketer Milk: Best Blog Designs 2026](https://www.marketermilk.com/blog/best-blog-designs)
