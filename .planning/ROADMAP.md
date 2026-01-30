# Roadmap: ShoeMoney Blog

## Milestones

- v1.0 MVP - Phases 1-7 (shipped 2026-01-25)
- v2.0 UI Overhaul - Phases 8-12 (in progress)

## Phases

<details>
<summary>v1.0 MVP (Phases 1-7) - SHIPPED 2026-01-25</summary>

7 phases, 42 plans. Full WordPress-to-Laravel migration with Livewire frontend, Algolia search, admin panel, and production optimizations. See `.planning/milestones/` for archived details.

</details>

### v2.0 UI Overhaul (In Progress)

**Milestone Goal:** Transform ShoeMoney from generic blog styling into a bold, distinctive brand identity with royal blue + black + white palette, geometric sans-serif typography, and polished dark mode across public and admin.

**Phase Numbering:**
- Integer phases (8, 9, 10, 11, 12): Planned milestone work
- Decimal phases (e.g., 9.1): Urgent insertions (marked with INSERTED)

- [ ] **Phase 8: Design Foundation** - Semantic color tokens, self-hosted fonts, and Tailwind theme configuration
- [ ] **Phase 9: Navigation & Layout** - Header with brand logo, mobile hamburger menu, and minimal footer
- [ ] **Phase 10: Typography & Content** - Post reading experience with proper content width, code blocks, progress bar, and load more pagination
- [ ] **Phase 11: Dark Mode** - Full dark mode coverage across all public and admin views using semantic tokens
- [ ] **Phase 12: Polish & Interactions** - Hover micro-interactions and scroll-triggered entrance animations

## Phase Details

### Phase 8: Design Foundation
**Goal**: The entire site draws colors, fonts, and spacing from a single semantic token system that automatically adapts to light and dark modes
**Depends on**: Nothing (first v2.0 phase)
**Requirements**: FOUN-01, FOUN-02, FOUN-03
**Success Criteria** (what must be TRUE):
  1. Changing one CSS custom property (e.g., `--color-primary`) updates the brand color across every page of the site
  2. All text on the site renders in the new font stack (Space Grotesk for headings, Inter for body, JetBrains Mono for code) with no flash of unstyled text
  3. The Tailwind `@theme` block exposes all design tokens as utility classes (e.g., `bg-surface-page`, `text-primary`, `shadow-card`)
  4. The site looks and functions identically to its current state after this phase (no visual regressions from infrastructure-only changes)
**Plans**: TBD

Plans:
- [ ] 08-01: TBD
- [ ] 08-02: TBD

### Phase 9: Navigation & Layout
**Goal**: Every public page displays the ShoeMoney brand identity through a distinctive header and minimal footer, with full mobile navigation support
**Depends on**: Phase 8
**Requirements**: NAVL-01, NAVL-02, NAVL-03
**Success Criteria** (what must be TRUE):
  1. The header displays the ShoeMoney shield logo and brand text in a static (non-sticky) layout that scrolls with content
  2. On mobile viewports, a hamburger icon opens a slide-out or dropdown menu containing all navigation links
  3. The footer shows only brand name, social icons, copyright, and a few essential links -- no clutter
  4. Desktop pages maintain the two-column layout (content + sidebar) with generous spacing and the new brand palette visible in structural elements
**Plans**: TBD

Plans:
- [ ] 09-01: TBD
- [ ] 09-02: TBD

### Phase 10: Typography & Content
**Goal**: Readers experience blog posts as polished, readable content with proper typography, syntax-highlighted code, scroll progress feedback, and seamless pagination
**Depends on**: Phase 9
**Requirements**: TYPO-01, TYPO-02, TYPO-03, PAGE-01
**Success Criteria** (what must be TRUE):
  1. Blog post body text renders at 18-20px with 1.6+ line-height inside a 750-800px max-width centered content column
  2. Code blocks display with syntax highlighting on a dark background regardless of light or dark page theme, with language auto-detection for legacy WordPress code blocks
  3. A reading progress bar at the top of post pages fills left-to-right in the brand color as the reader scrolls through the article
  4. Post listing pages display a "Load More" button that appends additional posts to the existing list without a full page reload
  5. Legacy WordPress content from all eras (2004-2026) renders readably without broken layouts from inline styles or deprecated HTML
**Plans**: TBD

Plans:
- [ ] 10-01: TBD
- [ ] 10-02: TBD
- [ ] 10-03: TBD

### Phase 11: Dark Mode
**Goal**: Both light and dark modes are fully polished across every public page and every admin view, with neither mode treated as an afterthought
**Depends on**: Phase 10
**Requirements**: DARK-01, DARK-02, DARK-03
**Success Criteria** (what must be TRUE):
  1. Every public page component (header, footer, sidebar, post listing, post detail, comments, search) renders with correct colors, contrast, and readability in dark mode
  2. Every admin page (dashboard, posts, pages, comments, categories, tags, users, all settings views) renders with correct colors and usable contrast in dark mode
  3. Toggling between light and dark mode produces no flash of wrong-mode colors (FOUC prevention)
  4. Both modes pass WCAG AA contrast ratios on all text and interactive elements -- neither mode has "washed out" or hard-to-read areas
**Plans**: TBD

Plans:
- [ ] 11-01: TBD
- [ ] 11-02: TBD
- [ ] 11-03: TBD

### Phase 12: Polish & Interactions
**Goal**: Interactive elements feel responsive and alive through hover transitions and scroll-triggered entrance animations that respect user motion preferences
**Depends on**: Phase 11
**Requirements**: PLSH-01, PLSH-02
**Success Criteria** (what must be TRUE):
  1. Buttons, links, and post list items respond to hover with visible micro-interactions (color transitions, subtle lifts, or underline animations) via CSS transitions
  2. Page sections fade or slide into view as the user scrolls down, using Intersection Observer
  3. Users with `prefers-reduced-motion` enabled see no animations -- all motion effects are suppressed
**Plans**: TBD

Plans:
- [ ] 12-01: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 8 -> 9 -> 10 -> 11 -> 12

| Phase | Milestone | Plans Complete | Status | Completed |
|-------|-----------|----------------|--------|-----------|
| 1-7 | v1.0 | 42/42 | Complete | 2026-01-25 |
| 8. Design Foundation | v2.0 | 0/TBD | Not started | - |
| 9. Navigation & Layout | v2.0 | 0/TBD | Not started | - |
| 10. Typography & Content | v2.0 | 0/TBD | Not started | - |
| 11. Dark Mode | v2.0 | 0/TBD | Not started | - |
| 12. Polish & Interactions | v2.0 | 0/TBD | Not started | - |
