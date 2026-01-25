---
phase: 07
plan: 03
name: "Database Performance Indexes"
completed: 2026-01-25
duration: ~3min

subsystem: database
tags: [mysql, indexes, performance, optimization]

dependency-graph:
  requires:
    - 01-02 (taggables/categorizables tables)
    - 01-01 (posts table)
  provides:
    - Optimized query paths for status filtering
    - Efficient polymorphic reverse lookups
  affects: []

tech-stack:
  added: []
  patterns:
    - Compound index for multi-column WHERE + ORDER BY
    - Reverse lookup index for polymorphic relations

key-files:
  created:
    - database/migrations/2026_01_25_100000_add_performance_indexes.php
  modified: []

decisions:
  - key: compound-index-order
    choice: "(status, published_at) rather than (published_at, status)"
    rationale: "WHERE status = X ORDER BY published_at DESC matches index left-to-right"
  - key: explicit-reverse-lookup
    choice: "Add explicit reverse lookup even though morphs() creates some indexes"
    rationale: "morphs() creates (type, id) index but explicit naming improves clarity"

metrics:
  tasks: 2
  commits: 1
  files-changed: 1
---

# Phase 7 Plan 3: Database Performance Indexes Summary

**One-liner:** Added strategic database indexes for status filtering, homepage listing, and polymorphic reverse lookups.

## What Was Built

Created a migration that adds four performance indexes:

1. **posts_status_index** - Single column index on `status` for admin dashboard queries
2. **posts_status_published_index** - Compound index on `(status, published_at)` for homepage listing
3. **taggables_reverse_lookup** - Index on `(taggable_type, taggable_id)` for tag queries
4. **categorizables_reverse_lookup** - Index on `(categorizable_type, categorizable_id)` for category queries

## Key Implementation Details

### Index Strategy

The compound index `(status, published_at)` was chosen to optimize the most common query pattern:

```php
// scopePublished() + latest()
Post::where('status', 'published')->orderBy('published_at', 'desc')
```

The MySQL query optimizer now uses `posts_status_published_index` as verified by EXPLAIN:
- `key: posts_status_published_index`
- `possible_keys: posts_status_index, posts_status_published_index`

### Polymorphic Indexes

The `morphs()` helper in Laravel creates `(type, id)` indexes automatically, but we added explicit reverse lookup indexes with clear naming for:
- Querying all tags for a given post
- Querying all categories for a given post

## Files Changed

| File | Change |
|------|--------|
| `database/migrations/2026_01_25_100000_add_performance_indexes.php` | New migration with 4 indexes |

## Decisions Made

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Compound index column order | (status, published_at) | Matches WHERE + ORDER BY pattern left-to-right |
| Explicit reverse lookup naming | Add despite morphs() auto-index | Clearer naming, guaranteed optimization path |

## Deviations from Plan

None - plan executed exactly as written.

## Verification Results

- [x] Migration file exists
- [x] `php artisan migrate` ran successfully
- [x] SHOW INDEX FROM posts includes posts_status_index and posts_status_published_index
- [x] SHOW INDEX FROM taggables includes taggables_reverse_lookup
- [x] SHOW INDEX FROM categorizables includes categorizables_reverse_lookup
- [x] EXPLAIN shows query using posts_status_published_index

## Next Phase Readiness

Database indexes are in place. Ready for:
- 07-04: Query optimization (N+1 prevention)
- 07-05: Caching strategy

No blockers or concerns.
