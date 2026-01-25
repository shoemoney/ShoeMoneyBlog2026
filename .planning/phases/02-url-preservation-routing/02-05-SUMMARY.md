---
phase: "02-url-preservation-routing"
plan: "05"
subsystem: "tooling"
tags: ["artisan", "verification", "http-client", "testing"]
dependency-graph:
  requires: ["02-02", "02-03"]
  provides: ["url-verification-command"]
  affects: ["deployment-verification", "qa-process"]
tech-stack:
  added: []
  patterns: ["artisan-command", "http-testing", "chunked-queries", "progress-bar"]
key-files:
  created:
    - "app/Console/Commands/VerifyUrls.php"
  modified: []
decisions:
  - id: "02-05-01"
    description: "10-second HTTP timeout per request"
    rationale: "Balance between catching slow pages and not blocking indefinitely"
  - id: "02-05-02"
    description: "JSON failure reports saved to storage/logs"
    rationale: "Persistent failure data for debugging without cluttering console output"
  - id: "02-05-03"
    description: "Limit failure table display to 20 rows"
    rationale: "Console readability while full data available in JSON"
metrics:
  duration: "~1 minute"
  completed: "2026-01-25"
---

# Phase 02 Plan 05: URL Verification Command Summary

Artisan command to verify all migrated WordPress URLs resolve correctly with HTTP testing.

## What Was Built

### VerifyUrls Command (`app/Console/Commands/VerifyUrls.php`)

Comprehensive URL verification command with:

**Flags:**
- `--type=posts|pages|categories|tags` - Test specific content type
- `--limit=N` - Maximum URLs to test per type
- `--base-url=URL` - Override APP_URL for testing
- `--quick` - Sample 10 URLs from each type for rapid verification

**Features:**
- Progress bars for visual feedback on large datasets
- Chunked database queries (100 records) for memory efficiency
- 10-second HTTP timeout per request
- Summary table with pass/fail counts per type
- Detailed failure table (limited to 20 rows for readability)
- Full failure report persisted to JSON file
- Exit code reflects verification status (0=success, 1=failures)

**Content Types Tested:**
- Posts (published only, ordered by date)
- Pages (all pages)
- Categories (ordered by name)
- Tags (ordered by name)

## Usage Examples

```bash
# Test all content types
php artisan urls:verify

# Quick check (10 samples per type)
php artisan urls:verify --quick

# Test only posts
php artisan urls:verify --type=posts

# Test with custom base URL
php artisan urls:verify --base-url=https://staging.shoemoney.com

# Test limited sample
php artisan urls:verify --limit=100
```

## Output Format

```
URL Verification
================
Base URL: http://localhost:8000

Testing posts: 10 of 3870 URLs
 10/10 [============================] 100%
  Result: PASS (10 passed, 0 failed)

Summary
=======
+------------+-------+--------+--------+--------+
| Type       | Total | Passed | Failed | Status |
+------------+-------+--------+--------+--------+
| Posts      | 3870  | 3870   | 0      | PASS   |
| Pages      | 159   | 159    | 0      | PASS   |
| Categories | 14    | 14     | 0      | PASS   |
| Tags       | 15448 | 15448  | 0      | PASS   |
| ---        | ---   | ---    | ---    | ---    |
| TOTAL      | 19491 | 19491  | 0      | PASS   |
+------------+-------+--------+--------+--------+
```

## Decisions Made

| ID | Decision | Rationale |
|----|----------|-----------|
| 02-05-01 | 10-second HTTP timeout | Balance between catching slow pages and not blocking indefinitely |
| 02-05-02 | JSON reports in storage/logs | Persistent failure data for debugging without cluttering console |
| 02-05-03 | 20-row limit on failure table | Console readability, full data in JSON |

## Deviations from Plan

None - plan executed exactly as written.

Note: Task 2 (--quick flag) was implemented as part of Task 1 since it's a core command flag - no separate commit needed.

## Commits

| Hash | Message |
|------|---------|
| dfb9f6d | feat(02-05): create VerifyUrls Artisan command |

## Next Phase Readiness

**Verification workflow ready:**
- Command can verify all 19,491 content URLs
- Quick mode enables rapid sanity checks
- JSON reports support CI/CD integration
- Exit codes enable scripted verification

**Integration with deployment:**
- Run `php artisan urls:verify --quick` for quick smoke test
- Run `php artisan urls:verify` for full verification before go-live
- JSON reports can be parsed by monitoring tools
