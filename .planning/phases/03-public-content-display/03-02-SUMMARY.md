---
phase: 03-public-content-display
plan: 02
subsystem: content
tags: [shortcodes, wordpress, accessors, html-processing]

# Dependency graph
requires:
  - phase: 01-data-migration-models
    provides: Post and Page models with content field
provides:
  - ShortcodeProcessor service for WordPress shortcode to HTML conversion
  - Post.rendered_content accessor for processed content
  - Post.reading_time accessor for reading estimates
  - Page.rendered_content accessor for processed content
affects: [03-03 (views), 03-04 (homepage), 03-05 (archives)]

# Tech tracking
tech-stack:
  added: []
  patterns:
    - "ShortcodeProcessor service with process() method"
    - "Cached Attribute accessors for computed content"
    - "Fallback shortcode stripping for unknown types"

key-files:
  created:
    - app/Services/ShortcodeProcessor.php
  modified:
    - app/Models/Post.php
    - app/Models/Page.php

key-decisions:
  - "Strip unknown shortcodes rather than rendering raw brackets"
  - "Cache accessors with shouldCache() to prevent recomputation"
  - "200 words per minute for reading time calculation"
  - "Minimum 1 minute reading time for short posts"

patterns-established:
  - "Service class pattern for content processing"
  - "Attribute accessor pattern for computed model properties"
  - "Fallback stripping for unhandled shortcodes"

# Metrics
duration: 2min
completed: 2026-01-25
---

# Phase 3 Plan 2: Shortcode Processing & Content Accessors Summary

**ShortcodeProcessor service converting [more], [caption], [video], [gravityform] to HTML with cached rendered_content and reading_time accessors on Post/Page models**

## Performance

- **Duration:** 2 min
- **Started:** 2026-01-25T04:58:36Z
- **Completed:** 2026-01-25T05:00:46Z
- **Tasks:** 3
- **Files modified:** 3

## Accomplishments

- ShortcodeProcessor service handling top 4 WordPress shortcode types
- Post model with rendered_content and reading_time cached accessors
- Page model with rendered_content cached accessor
- Unknown shortcodes stripped automatically (no raw [shortcode] in output)

## Task Commits

Each task was committed atomically:

1. **Task 1: Create ShortcodeProcessor service** - `facb9d3` (feat)
2. **Task 2: Add content accessors to Post model** - `2c5a956` (feat)
3. **Task 3: Add content accessor to Page model** - `f3754c7` (feat)

## Files Created/Modified

- `app/Services/ShortcodeProcessor.php` - WordPress shortcode to HTML conversion with support for [more], [caption], [video], [gravityform]
- `app/Models/Post.php` - Added renderedContent and readingTime cached accessors
- `app/Models/Page.php` - Added renderedContent cached accessor

## Decisions Made

- **Fallback shortcode stripping:** Unknown shortcodes are removed rather than leaving raw brackets visible in content
- **Cached accessors:** Both accessors use shouldCache() to prevent recomputation on multiple accesses during a request
- **Reading time formula:** 200 words per minute with minimum of 1 minute for short posts
- **No reading_time on Page:** Pages are typically short static content where reading time adds no value

## Deviations from Plan

None - plan executed exactly as written.

## Issues Encountered

None.

## User Setup Required

None - no external service configuration required.

## Next Phase Readiness

- ShortcodeProcessor available for view templates via `$post->rendered_content`
- Reading time available via `$post->reading_time`
- Ready for Plan 03-03 (Blade layouts and templates) to use these accessors

---
*Phase: 03-public-content-display*
*Completed: 2026-01-25*
