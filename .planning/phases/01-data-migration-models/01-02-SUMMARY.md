---
phase: 01-data-migration-models
plan: 02
subsystem: database
tags: [laravel, migrations, mysql, eloquent, polymorphic-relations]

# Dependency graph
requires:
  - phase: 01-data-migration-models
    provides: Laravel installation and WordPress database access
provides:
  - Complete Laravel database schema for blog content (posts, pages, comments, taxonomies)
  - Users table extension with WordPress mapping fields
  - Polymorphic taxonomy relationships (tags, categories)
  - Self-referencing comments table for threading
affects: [01-03-models, 01-04-seeders, 02-routing]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "Polymorphic pivot tables for flexible taxonomy (taggables, categorizables)"
    - "Self-referencing foreign keys for threaded comments"
    - "wordpress_id columns on all tables for data migration mapping"

key-files:
  created:
    - database/migrations/2026_01_24_000001_extend_users_table.php
    - database/migrations/2026_01_24_000002_create_posts_table.php
    - database/migrations/2026_01_24_000003_create_pages_table.php
    - database/migrations/2026_01_24_000004_create_categories_table.php
    - database/migrations/2026_01_24_000005_create_tags_table.php
    - database/migrations/2026_01_24_000006_create_taggables_table.php
    - database/migrations/2026_01_24_000007_create_categorizables_table.php
    - database/migrations/2026_01_24_000008_create_comments_table.php
  modified: []

key-decisions:
  - "Polymorphic relationships for tags/categories enable flexible content taxonomy"
  - "Self-referencing parent_id in comments enables WordPress-style threading"
  - "Custom unique constraint names prevent MySQL 64-character identifier limit errors"
  - "Compound indexes on comments table optimize queries for 160K+ records"

patterns-established:
  - "All content tables include wordpress_id for migration mapping"
  - "Use longText for content columns to handle WordPress posts with embedded HTML/shortcodes"
  - "Foreign key constraints with cascadeOnDelete for referential integrity"
  - "Performance-critical indexes defined at migration time"

# Metrics
duration: 12min
completed: 2026-01-24
---

# Phase 01 Plan 02: Database Migrations Summary

**Eight Laravel migrations establishing complete blog schema with WordPress ID mapping, polymorphic taxonomies, and threaded comments with performance indexes**

## Performance

- **Duration:** 12 minutes
- **Started:** 2026-01-24T20:10:43Z
- **Completed:** 2026-01-24T20:23:12Z
- **Tasks:** 3
- **Files modified:** 8

## Accomplishments
- Extended users table with author_name, wordpress_id, and role columns
- Created content tables (posts, pages) with URL routing indexes
- Created taxonomy tables (categories, tags) with polymorphic pivot tables
- Created comments table with self-referencing parent_id for threading and optimized indexes for 160K+ records
- All tables include wordpress_id unique columns for migration data mapping
- Foreign key constraints enforce referential integrity across all relationships

## Task Commits

Each task was committed atomically:

1. **Task 1: Create User Table Extension Migration** - `42c5c17` (feat)
2. **Task 2: Create Content and Taxonomy Migrations** - `dfab69b` (feat)
3. **Task 3: Create Comments Migration** - `5e7e8d4` (feat)

**Bug fix:** `2e6afeb` (fix: MySQL constraint name length)

## Files Created/Modified

- `database/migrations/2026_01_24_000001_extend_users_table.php` - Adds author_name, wordpress_id, role to users table
- `database/migrations/2026_01_24_000002_create_posts_table.php` - Posts with wordpress_id, user FK, published_at for URL routing
- `database/migrations/2026_01_24_000003_create_pages_table.php` - Pages with menu_order and unique slug
- `database/migrations/2026_01_24_000004_create_categories_table.php` - Categories taxonomy table
- `database/migrations/2026_01_24_000005_create_tags_table.php` - Tags taxonomy table
- `database/migrations/2026_01_24_000006_create_taggables_table.php` - Polymorphic pivot for tags
- `database/migrations/2026_01_24_000007_create_categorizables_table.php` - Polymorphic pivot for categories
- `database/migrations/2026_01_24_000008_create_comments_table.php` - Comments with parent_id threading, optimized indexes

## Decisions Made

**Polymorphic taxonomy pattern:** Used polymorphic pivot tables (taggables, categorizables) instead of separate post_tag/post_category tables. This enables flexible relationships - both posts AND pages can be tagged/categorized using the same taxonomy tables.

**Self-referencing comments:** Comments table uses parent_id foreign key referencing comments.id to enable WordPress-style threaded replies. Nullable parent_id indicates root-level comment.

**Performance indexes:** Added compound index (post_id, status, created_at) on comments table specifically for common query pattern: "get approved comments for a post, ordered chronologically". Critical for performance with 160K+ comment dataset.

**Custom constraint names:** Used short custom names for polymorphic pivot unique constraints to avoid MySQL's 64-character identifier length limit.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Fixed MySQL identifier length constraint**
- **Found during:** Task 2 execution - migration failed on categorizables table
- **Issue:** MySQL has a 64-character limit on identifier names. Auto-generated unique constraint names `categorizables_category_id_categorizable_id_categorizable_type_unique` (73 chars) and `taggables_tag_id_taggable_id_taggable_type_unique` (57 chars, would fail on longer table names) exceeded or approached this limit
- **Fix:** Added custom constraint names: `taggables_unique` and `categorizables_unique`
- **Files modified:**
  - database/migrations/2026_01_24_000006_create_taggables_table.php
  - database/migrations/2026_01_24_000007_create_categorizables_table.php
- **Verification:** `php artisan db:wipe && php artisan migrate` executed all migrations successfully
- **Committed in:** `2e6afeb` (separate fix commit after discovering migration failure)

---

**Total deviations:** 1 auto-fixed (Rule 1 - Bug)
**Impact on plan:** Essential fix for MySQL compatibility. No scope changes.

## Issues Encountered

**Migration rollback failure:** When attempting to rollback failed migration to re-run with fixes, encountered foreign key constraint error. The categorizables table had been partially created with a foreign key to categories, preventing categories table from being dropped.

**Resolution:** Used `php artisan db:wipe` to drop all tables cleanly, then ran migrations fresh. In production, would use `--force` flag cautiously or handle rollback order more carefully.

## User Setup Required

None - migrations are self-contained and require no external service configuration.

## Next Phase Readiness

**Ready for Phase 01 Plan 03 (Eloquent Models):**
- All tables created with correct schemas
- Foreign key relationships defined
- Indexes in place for performance
- wordpress_id mapping columns available for data migration

**Ready for Phase 01 Plan 04 (Seeders):**
- Target database structure exists
- Can begin mapping WordPress data to Laravel tables

**No blockers:** Schema is complete and verified. Models can be built against these tables immediately.

---
*Phase: 01-data-migration-models*
*Completed: 2026-01-24*
