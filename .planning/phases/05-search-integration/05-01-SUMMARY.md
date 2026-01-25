---
phase: 05-search-integration
plan: 01
subsystem: search
tags: [algolia, scout, laravel-scout, searchable]

# Dependency graph
requires:
  - phase: 01-data-migration-models
    provides: Post model with content, title, excerpt, slug, published_at fields
provides:
  - Scout Extended package configured with Algolia driver
  - Searchable Post model ready for index import
  - Environment variable placeholders for Algolia credentials
affects: [05-02, 05-03, 05-04]

# Tech tracking
tech-stack:
  added: [algolia/scout-extended:^3.2.2, laravel/scout:^10.23]
  patterns: [Searchable trait pattern, conditional indexing via shouldBeSearchable]

key-files:
  created: [config/scout.php]
  modified: [app/Models/Post.php, composer.json, composer.lock, .env]

key-decisions:
  - "Scout Extended over base Scout for zero-downtime reimports and settings management"
  - "Content truncated to ~5000 chars to stay under Algolia 10KB limit"
  - "SCOUT_QUEUE=false for development, change to true for production"
  - "Index prefix 'shoemoney_' for namespace isolation"

patterns-established:
  - "Searchable model pattern: toSearchableArray, shouldBeSearchable, searchableAs methods"
  - "Conditional indexing: only published posts indexed via shouldBeSearchable filter"

# Metrics
duration: 4min
completed: 2026-01-25
---

# Phase 5 Plan 01: Scout Extended Setup Summary

**Scout Extended v3.2.2 installed with Algolia driver and Post model configured for conditional search indexing**

## Performance

- **Duration:** 4 min
- **Started:** 2026-01-25T10:00:00Z
- **Completed:** 2026-01-25T10:04:00Z
- **Tasks:** 2
- **Files modified:** 4

## Accomplishments
- Scout Extended package installed with all Algolia-specific features (reimport, optimize, sync)
- Post model has Searchable trait with proper field selection for indexing
- Only published posts will be indexed (status=published AND published_at set)
- Content truncation prevents 10KB record size errors
- Environment ready for Algolia credentials

## Task Commits

Each task was committed atomically:

1. **Task 1: Install Scout Extended and publish configuration** - `b35715a` (feat)
2. **Task 2: Add Searchable trait to Post model** - `f651102` (feat)

## Files Created/Modified
- `config/scout.php` - Scout configuration with Algolia as default driver
- `app/Models/Post.php` - Added Searchable trait with toSearchableArray, shouldBeSearchable, searchableAs
- `composer.json` - Added algolia/scout-extended dependency
- `composer.lock` - Lock file updated
- `.env` - Added SCOUT_DRIVER, SCOUT_PREFIX, SCOUT_QUEUE, ALGOLIA_APP_ID, ALGOLIA_SECRET

## Decisions Made
- Used Scout Extended over base Scout for zero-downtime reimports and settings sync
- Content truncated to ~5000 chars (well under 10KB limit with room for other fields)
- SCOUT_QUEUE=false for development (no queue worker needed)
- after_commit=true ensures index updates only after successful database commits

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None.

## User Setup Required

**External services require manual configuration.** User needs to:

1. Create Algolia account at https://www.algolia.com/
2. Get credentials from Algolia Dashboard -> Settings -> API Keys
3. Add to `.env`:
   - `ALGOLIA_APP_ID=your_app_id`
   - `ALGOLIA_SECRET=your_admin_api_key`
4. Run `php artisan scout:import "App\Models\Post"` to import existing posts

## Verification Results

All 5 verification checks passed:

1. Scout Extended v3.2.2 installed
2. config/scout.php exists with Algolia configuration
3. Post model includes `use Searchable` trait
4. Post model has toSearchableArray, shouldBeSearchable, searchableAs methods
5. .env contains ALGOLIA_APP_ID and ALGOLIA_SECRET placeholders

## Next Phase Readiness
- Scout infrastructure complete, ready for Livewire search component (05-02)
- Index import can be run once Algolia credentials are configured
- Post model correctly filters to only published content

---
*Phase: 05-search-integration*
*Plan: 01*
*Completed: 2026-01-25*
