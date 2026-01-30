# Requirements: ShoeMoney Blog v2.0 UI Overhaul

**Defined:** 2026-01-29
**Core Value:** Readers can find and read two decades of blog content quickly, and authors can publish new posts without fighting WordPress.

## v2.0 Requirements

Requirements for the UI Overhaul milestone. Each maps to roadmap phases.

### Design Foundation

- [ ] **FOUN-01**: Site uses a semantic color token system with light and dark palettes defined as CSS custom properties
- [ ] **FOUN-02**: Site uses self-hosted variable fonts (Space Grotesk display, Inter body, JetBrains Mono code) with font-display: swap
- [ ] **FOUN-03**: Tailwind @theme block defines complete design tokens (colors, spacing, shadows, radii, transitions)

### Typography & Content

- [ ] **TYPO-01**: Blog post content renders at 750-800px max-width, centered, with 18-20px body text and 1.6+ line-height
- [ ] **TYPO-02**: Code blocks render with syntax highlighting on always-dark background regardless of page theme
- [ ] **TYPO-03**: Reading progress bar displays at top of post pages showing scroll position in brand color

### Navigation & Layout

- [ ] **NAVL-01**: Header displays ShoeMoney shield logo + brand text in static (non-sticky) layout
- [ ] **NAVL-02**: Mobile navigation uses hamburger menu with slide-out/dropdown
- [ ] **NAVL-03**: Footer redesigned as minimal: brand name, social icons, copyright, few links

### Pagination

- [ ] **PAGE-01**: Post listing pages use "Load More" button that appends posts without full page reload

### Dark Mode

- [ ] **DARK-01**: All public site components render correctly in dark mode using semantic tokens
- [ ] **DARK-02**: Admin panel supports dark mode across all views (dashboard, posts, pages, comments, categories, tags, users, settings)
- [ ] **DARK-03**: Dark mode and light mode are designed with equal priority — both polished, not one as afterthought

### Polish & Interactions

- [ ] **PLSH-01**: Interactive elements have hover micro-interactions (card lift, button transitions, link animations) via CSS transitions
- [ ] **PLSH-02**: Page sections fade/slide in on viewport entry using Intersection Observer (respects prefers-reduced-motion)

## Future Requirements (v2.1+)

### Homepage Enhancement

- **HOME-01**: Bold hero section with full-bleed brand background, headline, subheadline, CTA
- **HOME-02**: Card-based post grid layout (2-3 columns) replacing list
- **HOME-03**: Featured/pinned posts section with admin toggle
- **HOME-04**: "As Seen In" credibility bar with publication logos

### Engagement

- **ENGM-01**: Newsletter signup forms (hero placement + post-end)
- **ENGM-02**: Category color coding system with admin color picker
- **ENGM-03**: Animated stat counters in hero section

### SEO

- **SEOX-01**: Breadcrumb navigation with Schema.org BreadcrumbList markup

## Out of Scope

| Feature | Reason |
|---------|--------|
| Component library (Flux, daisyUI) | Custom brand identity conflicts with pre-built component styles |
| GSAP/Framer Motion animations | Overkill for blog; CSS + Alpine transitions sufficient |
| Spatie Media Library | Would require re-importing WordPress images; CSS-only approach for UI overhaul |
| Sticky header | Static header chosen for reading-focused UX |
| Infinite scroll | "Load More" chosen for URL state preservation |
| Sidebar removal | Keeping two-column layout with sidebar widgets |
| Full page rebuild | Restyling existing components, not rewriting |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| FOUN-01 | TBD | Pending |
| FOUN-02 | TBD | Pending |
| FOUN-03 | TBD | Pending |
| TYPO-01 | TBD | Pending |
| TYPO-02 | TBD | Pending |
| TYPO-03 | TBD | Pending |
| NAVL-01 | TBD | Pending |
| NAVL-02 | TBD | Pending |
| NAVL-03 | TBD | Pending |
| PAGE-01 | TBD | Pending |
| DARK-01 | TBD | Pending |
| DARK-02 | TBD | Pending |
| DARK-03 | TBD | Pending |
| PLSH-01 | TBD | Pending |
| PLSH-02 | TBD | Pending |

**Coverage:**
- v2.0 requirements: 15 total
- Mapped to phases: 0 (awaiting roadmap)
- Unmapped: 15

---
*Requirements defined: 2026-01-29*
*Last updated: 2026-01-29 after milestone scoping*
