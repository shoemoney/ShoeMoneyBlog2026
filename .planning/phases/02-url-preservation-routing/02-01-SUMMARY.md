---
phase: "02-url-preservation-routing"
plan: "01"
subsystem: routing
tags: [laravel-routes, wordpress-urls, seo-preservation, controllers]

dependency-graph:
  requires: []
  provides: [wordpress-route-patterns, controller-stubs]
  affects: [02-02, 02-03, 02-04]

tech-stack:
  added: []
  patterns: [wordpress-permalink-routing, regex-route-constraints, catch-all-routing]

key-files:
  created:
    - routes/web.php
    - app/Http/Controllers/PostController.php
    - app/Http/Controllers/PageController.php
    - app/Http/Controllers/CategoryController.php
    - app/Http/Controllers/TagController.php
  modified: []

decisions:
  - id: route-order-strategy
    choice: "Specific patterns before catch-all"
    rationale: "Date-based post route must match before single-slug page route to avoid false matches"
  - id: regex-constraints
    choice: "Use regex where clauses on all routes"
    rationale: "Ensures only valid URL patterns match (4-digit year, 2-digit month/day, lowercase slug)"

metrics:
  duration: "~8min"
  completed: "2026-01-24"
---

# Phase 02 Plan 01: WordPress-Compatible Routes Summary

**One-liner:** Laravel routes matching WordPress permalink structures with regex constraints and proper ordering.

## What Was Built

### Routes Created (routes/web.php)

| Route Name | Pattern | Controller | Purpose |
|------------|---------|------------|---------|
| `home` | `/` | PostController@index | Blog homepage listing |
| `post.show` | `/{year}/{month}/{day}/{slug}` | PostController@show | Individual post display |
| `category.show` | `/category/{slug}` | CategoryController@show | Category archive |
| `tag.show` | `/tag/{slug}` | TagController@show | Tag archive |
| `page.show` | `/{slug}` | PageController@show | Static page (catch-all) |

### Route Ordering Strategy

Critical ordering to prevent false matches:
1. Homepage `/` (exact match)
2. Date-based posts `/{year}/{month}/{day}/{slug}` (most specific pattern)
3. Category `/category/{slug}` (prefixed)
4. Tag `/tag/{slug}` (prefixed)
5. Page `/{slug}` (catch-all - must be last)

### Regex Constraints Applied

```php
// Post route constraints
'year' => '[0-9]{4}'    // 4-digit year
'month' => '[0-9]{2}'   // 2-digit month
'day' => '[0-9]{2}'     // 2-digit day
'slug' => '[a-z0-9\-]+' // lowercase alphanumeric with hyphens

// All slug parameters
'slug' => '[a-z0-9\-]+' // consistent across all routes
```

### Controllers Created

Placeholder implementations returning 200 responses:
- **PostController**: `index()` and `show($year, $month, $day, $slug)`
- **PageController**: `show($slug)`
- **CategoryController**: `show($slug)`
- **TagController**: `show($slug)`

## Verification Results

```bash
$ php artisan route:list | grep -E "(home|post|category|tag|page)"
GET|HEAD   / ................................... home > PostController@index
GET|HEAD   category/{slug} ......... category.show > CategoryController@show
GET|HEAD   tag/{slug} ........................ tag.show > TagController@show
GET|HEAD   {slug} .......................... page.show > PageController@show
GET|HEAD   {year}/{month}/{day}/{slug} ..... post.show > PostController@show
```

Route testing confirmed all 5 patterns return 200 with placeholder content.

## Decisions Made

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Route order | Specific before general | Prevents `/about` matching as year in date pattern |
| Regex constraints | Strict patterns | Ensures only valid WordPress URLs match |
| Placeholder controllers | Simple Response objects | Minimal implementation until Plan 02 |

## Deviations from Plan

None - plan executed exactly as written.

## Commit History

| Commit | Message |
|--------|---------|
| `33c006f` | feat(02-01): define WordPress-compatible routes |

## Next Phase Readiness

### Immediate Dependencies Ready
- Routes exist for Plan 02 (controller implementation)
- Controller stubs in place for method additions

### Requirements for Plan 02
- Post/Page models from Phase 1 available
- Database connection to WordPress tables configured

## Files Changed

```
routes/web.php                              (new) - 47 lines
app/Http/Controllers/PostController.php    (new) - 32 lines
app/Http/Controllers/PageController.php    (new) - 22 lines
app/Http/Controllers/CategoryController.php (new) - 22 lines
app/Http/Controllers/TagController.php     (new) - 22 lines
```

## Integration Points

- **From Phase 1:** Models (Post, Page, Category, Tag) will be used in Plan 02
- **To Plan 02:** Controllers ready for database query implementation
- **To Plan 05:** Route names available for URL generation (`route('post.show', [...])`)
