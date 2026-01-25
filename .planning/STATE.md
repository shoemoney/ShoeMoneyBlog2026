# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-01-24)

**Core value:** Readers can find and read two decades of blog content quickly, and authors can publish new posts without fighting WordPress.
**Current focus:** Phase 6 - Admin Panel

## Current Position

Phase: 6 of 7 (Admin Panel)
Plan: 5 of 7 in current phase
Status: In progress
Last activity: 2026-01-25 - Completed 06-03-PLAN.md (Post Management CRUD)

Progress: [████████░░] ~83%

## Performance Metrics

**Velocity:**
- Total plans completed: 30
- Average duration: ~5.9min
- Total execution time: ~3.4 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 01-data-migration-models | 7 | ~84min | ~12min |
| 02-url-preservation-routing | 7 | ~30min | ~4.3min |
| 03-public-content-display | 8 | ~26min | ~3.3min |
| 04-comment-system | 5 | ~11min | ~2.2min |

**Recent Trend:**
- Phase 1: 01-01 (10min), 01-02 (12min), 01-03 (12min), 01-04, 01-05, 01-06, 01-07
- Phase 2: 02-01 (8min), 02-02 (5min), 02-03 (1min), 02-04, 02-05 (1min), 02-06 (12min), 02-07 (3min)
- Phase 3: 03-01 (6min), 03-02 (2min), 03-03 (2min), 03-04 (3min), 03-05 (2min), 03-06 (2min), 03-07 (4min), 03-08 (5min)
- Phase 4: 04-01 (3min), 04-02 (2min), 04-03 (1min), 04-04 (2min), 04-05 (3min)
- Phase 5: 05-01 (4min), 05-02 (2min), 05-03 (15min)
- Phase 6: 06-01 (4min), 06-02 (2min), 06-03 (5min), 06-05 (3min)
- Trend: Post management CRUD complete

*Updated after each plan completion*

## Accumulated Context

### Decisions

Decisions are logged in PROJECT.md Key Decisions table.
Recent decisions affecting current work:

- Livewire over Inertia/Vue - Simpler mental model, stays in PHP, good for content sites
- Algolia over Meilisearch - User preference, proven at scale, excellent Laravel integration
- Custom admin over Filament - Full control over UX, avoid package dependencies
- Preserve WordPress URLs - Protect 20 years of SEO value
- Set WordPress connection strict mode to false for compatibility (01-01)
- Use WordPress uppercase ID convention for primary keys (01-01)
- Models are read-only with no fillable properties (01-01)
- Polymorphic relationships for tags/categories enable flexible content taxonomy (01-02)
- Self-referencing parent_id in comments enables WordPress-style threading (01-02)
- Custom unique constraint names prevent MySQL 64-character identifier limit errors (01-02)
- Compound indexes on comments table optimize queries for 160K+ records (01-02)
- Display name accessor prefers author_name over name for WordPress compatibility (01-03)
- URL accessors preserve WordPress permalink structure for SEO (01-03)
- Role helpers use constants for cleaner authorization checks (01-03)
- Route order strategy: specific patterns before catch-all (02-01)
- Regex constraints on all routes for URL validation (02-01)
- Date validation with whereYear/Month/Day prevents URL manipulation (02-02)
- 15 items per page for taxonomy archives (02-03)
- firstOrFail pattern for 404 handling on taxonomy lookups (02-03)
- 10-second HTTP timeout for URL verification requests (02-05)
- JSON failure reports stored in storage/logs for debugging (02-05)
- Only tags with published posts included in sitemap (915 vs 15k+) (02-04)
- Priority levels: homepage 1.0, posts 0.8, pages 0.6, categories 0.5, tags 0.3 (02-04)
- Chunked iteration (500/100) for memory-efficient large dataset processing (02-04)
- Handle GET and HEAD requests for trailing slash redirect (02-06)
- Global middleware prepend to run before route matching (02-06)
- SEO configured via service provider fluent API, config file stores values (03-01)
- Title modifier auto-appends ' - ShoeMoney' to all page titles (03-01)
- Brand colors as CSS variables for easy future customization (03-01)
- Strip unknown shortcodes rather than rendering raw brackets (03-02)
- Cache accessors with shouldCache() to prevent recomputation (03-02)
- 200 words per minute for reading time calculation (03-02)
- Anonymous components over class-based for simple layout elements (03-03)
- Flex-column body with mt-auto footer for sticky footer pattern (03-03)
- Display author.display_name with fallback to author.name (03-05)
- Categories as pill links, tags as hashtag links (03-05)
- prose prose-lg prose-slate for content typography (03-05)
- Post-card as anonymous component with @props directive (03-04)
- 200-char excerpt limit with Str::limit for post listings (03-04)
- max-w-3xl for focused reading on static pages (03-07)
- No author, date, reading time, categories, or tags on pages (03-07)
- Category and tag views share identical structure with taxonomy-specific labels (03-06)
- Hashtag prefix in SEO title for tags (#tagname) (03-06)
- Visual verification via human checkpoint confirms production readiness (03-08)
- Livewire 4.x with explicit @livewireStyles/@livewireScripts directives for clarity (04-01)
- Honeypot config published for future spam protection customization (04-01)
- Email normalization: lowercase + trim before lookup for consistent matching (04-02)
- Query efficiency: exists() over count() for boolean checks (04-02)
- 3-level deep eager loading with approved() scope at each level for comments (04-03)
- Depth prop passed through recursive includes for nesting control (04-03)
- nl2br(e()) pattern for safe content rendering with line breaks (04-03)
- Rate limit: 5 comments per minute per IP (04-04)
- Keep name/email after submit for user convenience (04-04)
- Unique field IDs using parentId suffix for multiple forms (04-04)
- Scout Extended over base Scout for zero-downtime reimports and settings sync (05-01)
- Content truncated to ~5000 chars to stay under Algolia 10KB limit (05-01)
- SCOUT_QUEUE=false for development, true for production (05-01)
- Index prefix 'shoemoney_' for namespace isolation (05-01)
- 300ms debounce on search input prevents excessive Algolia API calls (05-02)
- Minimum 2 characters before triggering search (05-02)
- Max 5 results in typeahead for quick selection (05-02)
- Alpine.js for keyboard navigation in search dropdown (05-02)
- gap-6 more reliable than space-x-6 for flex nav link spacing (05-03)
- Three-section flex layout for header: logo | center (tagline+search) | nav (05-03)
- shrink-0 on logo and nav prevents compression when search expands (05-03)
- Fixed w-64 width for search bar consistency across screen sizes (05-03)
- is_admin boolean over complex role system for admin access simplicity (06-01)
- Gate::before grants admin full access without individual policy checks (06-01)
- Middleware class over inline closure for Laravel 12 route groups (06-01)
- All admin routes defined as placeholders for immediate sidebar navigation (06-01)
- Dark sidebar with wire:navigate for SPA-like navigation (06-02)
- Anonymous x-admin components over class-based for simple layout composition (06-02)
- Three-type flash messages (success/error/info) with icons for admin feedback (06-02)
- Inline edit pattern: form toggles create/edit mode with same fields (06-05)
- Pagination 50 per page for large tag sets (15K+ from WordPress) (06-05)
- Delete protection: disable button if posts_count > 0 (06-05)
- Livewire Layout attribute for component pages (06-03)
- URL query string persistence with #[Url(history: true)] for bookmarkable filters (06-03)
- wire:model.blur for title to trigger slug generation after typing (06-03)
- Preserve original published_at when re-saving published posts (06-03)

### Pending Todos

None yet.

### Blockers/Concerns

None yet.

## Session Continuity

Last session: 2026-01-25
Stopped at: Completed 06-03-PLAN.md (Post Management CRUD)
Resume file: None
Next action: Continue with 06-04-PLAN.md (Comment Management)
