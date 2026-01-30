---
phase: 08-design-foundation
plan: 01
subsystem: ui
tags: [tailwindcss-v4, design-tokens, typography, variable-fonts, oklch, dark-mode, space-grotesk, inter, jetbrains-mono]

# Dependency graph
requires:
  - phase: v1.0
    provides: Laravel 11 + Livewire 3 + Tailwind CSS v4 base setup
provides:
  - Complete two-layer semantic design token system (primitive + semantic)
  - Self-hosted variable fonts with fallback optimization (Space Grotesk, Inter, JetBrains Mono)
  - Dark mode token overrides for both system preference and manual toggle
  - WordPress content styles migrated to semantic tokens
affects: [09-navigation-header, 10-typography-content, 11-dark-mode-polish, 12-final-touches, all-v2.0-ui-phases]

# Tech tracking
tech-stack:
  added:
    - Space Grotesk Variable font (300-700) for headings
    - Inter Variable font (100-900) for body text
    - JetBrains Mono Variable font (100-800) for code
  patterns:
    - Two-layer design tokens (primitive oklch colors → semantic purpose tokens)
    - Font fallback optimization with size-adjust to prevent layout shift
    - Identical semantic token overrides in both @media prefers-color-scheme and .dark class
    - WordPress content using semantic tokens instead of hardcoded utilities

key-files:
  created:
    - public/fonts/space-grotesk-variable.woff2
    - public/fonts/inter-variable.woff2
    - public/fonts/jetbrains-mono-variable.woff2
  modified:
    - resources/css/app.css

key-decisions:
  - "Used oklch color space for primitive tokens (wider gamut, perceptually uniform)"
  - "Semantic tokens follow surface/text/border/accent scoping pattern"
  - "Dark mode uses both @media and .dark class with identical overrides (system preference + manual toggle)"
  - "Higher shadow opacity in dark mode (0.3-0.6 vs 0.05-0.1) for visibility"
  - "Font fallbacks use size-adjust metrics to prevent FOUT layout shift"
  - "WordPress content styles migrated to semantic tokens with separate .dark overrides removed"

patterns-established:
  - "Two-layer token system: Primitive tokens (--color-slate-500) → Semantic tokens (--color-text-primary) → Components (text-text-primary utility)"
  - "Typography tokens: --font-display (headings), --font-body (paragraphs), --font-mono (code), --font-sans (default alias)"
  - "Semantic shadow tokens: --shadow-card, --shadow-dropdown, --shadow-modal (reference elevation scale)"
  - "Semantic radius tokens: --radius-card, --radius-button, --radius-input (reference base scale)"

# Metrics
duration: 3min
completed: 2026-01-30
---

# Phase 8 Plan 1: Design Foundation Summary

**Complete two-layer semantic design token system with self-hosted variable fonts (Space Grotesk, Inter, JetBrains Mono) and dark mode overrides in Tailwind v4 @theme block**

## Performance

- **Duration:** 3 min
- **Started:** 2026-01-30T06:34:35Z
- **Completed:** 2026-01-30T06:37:08Z
- **Tasks:** 2
- **Files modified:** 4 (3 created, 1 modified)

## Accomplishments
- Three self-hosted variable fonts (woff2 format, 20-26KB total) with optimized fallbacks to prevent layout shift
- Complete @theme block with 6 font-face declarations, typography/color/spacing/shadow/radius/transition tokens
- Two-layer semantic color system: primitive oklch palette → semantic purpose tokens (surface-*, text-*, border-*, accent-*)
- Dark mode support via both @media prefers-color-scheme and .dark class with identical semantic token overrides
- WordPress content styles migrated from hardcoded utilities (text-gray-500) to semantic tokens (text-text-subtle)

## Task Commits

Each task was committed atomically:

1. **Task 1: Download and place self-hosted variable fonts** - `c2038ab` (feat)
2. **Task 2: Implement complete design token system in app.css** - `e86af4e` (feat)

## Files Created/Modified
- `public/fonts/space-grotesk-variable.woff2` - Display heading font, 300-700 weight range, 6.6KB
- `public/fonts/inter-variable.woff2` - Body text font, 100-900 weight range, 25KB
- `public/fonts/jetbrains-mono-variable.woff2` - Code/monospace font, 100-800 weight range, 2KB
- `resources/css/app.css` - Complete design token system: 6 @font-face (3 variable + 3 fallback), @theme block with all token categories, dark mode overrides, WordPress content styles using semantic tokens

## Decisions Made

**1. oklch color space for primitive tokens**
- Rationale: Wider gamut (Display P3), perceptually uniform colors, matches Tailwind v4 defaults
- Impact: More vivid colors on modern screens, consistent visual weight across hue changes

**2. Semantic token naming pattern: surface/text/border/accent**
- Rationale: Industry-standard scoping, clear purpose mapping, enables theming without component changes
- Impact: Components reference semantic tokens only (bg-surface-card, text-primary), theme changes never touch component code

**3. Dual dark mode overrides (@media + .dark class)**
- Rationale: Support both system preference (prefers-color-scheme) and manual toggle (Alpine.js .dark class)
- Impact: Users can override system preference, both methods reference identical semantic token values

**4. Higher shadow opacity in dark mode (0.3-0.6 vs 0.05-0.1)**
- Rationale: Black shadows on dark backgrounds need higher opacity for visibility and depth perception
- Impact: Elevation hierarchy maintained in dark theme without looking flat

**5. Font fallback size-adjust metrics**
- Rationale: Prevent Cumulative Layout Shift (CLS) when web fonts load, improve Core Web Vitals
- Impact: No visible text reflow during font swap, better UX on slow connections

**6. WordPress content styles migrated to semantic tokens**
- Rationale: Legacy content must adapt to dark mode automatically, separate .dark overrides create maintenance burden
- Impact: WordPress-migrated content (captions, videos) correctly styled in both themes without duplication

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Fixed Tailwind v4 utility class naming**
- **Found during:** Task 2 (Vite build after implementing design tokens)
- **Issue:** Build failed with "Cannot apply unknown utility class `text-subtle`" - Tailwind v4 requires full token path: `text-text-subtle` (not `text-subtle`)
- **Fix:** Changed `.wp-caption figcaption` from `text-subtle` to `text-text-subtle` to reference `--color-text-subtle` token correctly
- **Files modified:** resources/css/app.css
- **Verification:** `npm run build` succeeded, generated 119.50 kB CSS bundle with all semantic utilities
- **Committed in:** e86af4e (Task 2 commit)

---

**Total deviations:** 1 auto-fixed (Rule 3 - blocking)
**Impact on plan:** Blocking issue preventing build completion. Fix required to proceed. No scope creep.

## Issues Encountered

**macOS grep -P flag not supported**
- Problem: Initial font download approach used `grep -oP` (Perl regex) unavailable on macOS BSD grep
- Solution: Switched to `grep -o` with `sed` to extract font URLs from Google Fonts CSS API
- Impact: Successfully downloaded all three variable fonts (Space Grotesk, Inter, JetBrains Mono)

## User Setup Required

None - no external service configuration required. Fonts are self-hosted, design tokens compile in Vite build.

## Next Phase Readiness

**Ready for Phase 09 (Navigation Header):**
- All semantic tokens defined and available as Tailwind utilities
- Typography tokens ready: --font-display for nav links, --font-body for default text
- Color tokens ready: --color-surface-card for header background, --color-text-primary for links, --color-accent-primary for active states
- Dark mode infrastructure complete: tokens automatically adapt when .dark class applied

**Ready for Phase 10 (Typography Content):**
- Font stack loaded and optimized with fallbacks
- Prose styling can reference --font-body, --font-display, --font-mono
- @tailwindcss/typography plugin already configured

**Ready for Phase 11 (Dark Mode Polish):**
- Dark mode token overrides functional for both system preference and manual toggle
- All semantic tokens respond to .dark class
- WordPress content styles already dark-mode aware

**No blockers identified.**

**Concerns:**
- Legacy WordPress content (20 years) may have edge cases not covered by .wp-caption/.wp-video/.wp-more styles. Stress-test page needed in Phase 10.
- Response cache must be cleared after CSS deploys to avoid serving pages with old token values.

---
*Phase: 08-design-foundation*
*Completed: 2026-01-30*
