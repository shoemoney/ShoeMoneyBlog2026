# Phase 7: Performance & Polish - Research

**Researched:** 2026-01-25
**Domain:** Laravel performance optimization, caching, dark mode, database optimization, automated backups
**Confidence:** HIGH

## Summary

This phase covers five distinct but interrelated areas: sub-second page load performance, response caching with invalidation, dark mode implementation, database query optimization, and automated backups. The research found mature, well-documented solutions for all requirements.

For performance, Laravel 12's built-in `Cache::flexible()` method provides stale-while-revalidate caching natively, and spatie/laravel-responsecache handles full HTTP response caching with model-based invalidation. Tailwind CSS v4 supports dark mode via custom variants with the `@custom-variant dark` directive, combined with Alpine.js (bundled with Livewire) for localStorage persistence. Database optimization is primarily about verifying eager loading (already in place) and adding strategic indexes. Spatie's laravel-backup package is the standard solution for automated backups to external storage like S3.

**Primary recommendation:** Use Laravel's native caching features with spatie/laravel-responsecache for response caching, implement dark mode via Tailwind v4's `@custom-variant` with Alpine.js persistence, and deploy spatie/laravel-backup for automated daily backups to S3.

## Standard Stack

The established libraries/tools for this domain:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| spatie/laravel-responsecache | ^7.0 | HTTP response caching | Industry standard for Laravel, automatic cache invalidation, middleware-based |
| spatie/laravel-backup | ^9.0 | Automated backups to external storage | De facto standard, supports S3/cloud, scheduled cleanup strategies |
| Tailwind CSS | ^4.0.0 | Dark mode styling (already installed) | Native `@custom-variant` support for class-based dark mode |
| Alpine.js | (bundled with Livewire 4) | Theme toggle + localStorage persistence | Already available, includes persist plugin |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Laravel Debugbar | ^3.14 | Development profiling | Identify N+1 queries during development |
| aws/aws-sdk-php-laravel | ^3.9 | S3 integration for backups | Required for storing backups to AWS S3 |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| spatie/laravel-responsecache | Laravel native Cache::flexible() | Response cache handles full HTTP responses automatically; Cache::flexible() is for data caching |
| Alpine.js localStorage | Livewire session storage | Alpine/localStorage avoids round-trips, works before Livewire hydration |
| spatie/laravel-backup | Custom artisan command | Backup package handles cleanup, notifications, cloud storage automatically |

**Installation:**
```bash
composer require spatie/laravel-responsecache spatie/laravel-backup

# If using S3 for backups (already have aws/aws-sdk-php-laravel dependency via scout-extended)
# No additional package needed for S3
```

## Architecture Patterns

### Response Caching Flow
```
Request → CacheResponse Middleware → Check Cache
                                        │
                                        ├─ HIT: Return cached response (< 10ms)
                                        │
                                        └─ MISS: Execute controller
                                                 │
                                                 └─ Store response → Return

Post Update → Model Observer → ResponseCache::clear()
```

### Dark Mode Architecture
```
                                ┌─────────────────────┐
                                │   Initial Load      │
                                │  (inline <head>)    │
                                └─────────┬───────────┘
                                          │
                           ┌──────────────┼──────────────┐
                           ▼              ▼              ▼
                      localStorage    System Pref    Default
                      'theme'         prefers-       'light'
                                      color-scheme
                                          │
                                          ▼
                           ┌──────────────────────────┐
                           │  Add/remove .dark class  │
                           │  on <html> element       │
                           └──────────────────────────┘
                                          │
                                          ▼
                           ┌──────────────────────────┐
                           │  Tailwind dark: classes  │
                           │  activate automatically  │
                           └──────────────────────────┘
```

### Pattern 1: Model-Based Cache Invalidation
**What:** Automatically clear response cache when Post model changes
**When to use:** Any model that affects cached pages
**Example:**
```php
// Source: https://github.com/spatie/laravel-responsecache

// app/Models/Post.php - Add trait
use App\Traits\ClearsResponseCache;

class Post extends Model
{
    use ClearsResponseCache;
    // ...
}

// app/Traits/ClearsResponseCache.php
namespace App\Traits;

use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache(): void
    {
        static::saved(fn() => ResponseCache::clear());
        static::deleted(fn() => ResponseCache::clear());
    }
}
```

### Pattern 2: Tailwind v4 Dark Mode with Alpine.js
**What:** Class-based dark mode toggle with localStorage persistence
**When to use:** User-controllable theme switching
**Example:**
```css
/* resources/css/app.css */
@import 'tailwindcss';

@custom-variant dark (&:where(.dark, .dark *));
```

```html
<!-- resources/views/components/layout.blade.php -->
<!DOCTYPE html>
<html lang="en"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' ||
                (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <!-- FOUC prevention script BEFORE any CSS -->
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <!-- ... -->
</head>
```

### Pattern 3: Cache::flexible() for Data Caching
**What:** Stale-while-revalidate pattern for expensive queries
**When to use:** Dashboard stats, aggregations, non-critical freshness
**Example:**
```php
// Source: https://laravel.com/docs/12.x/cache

// Fresh for 60s, stale-but-usable for up to 300s
$stats = Cache::flexible('homepage_stats', [60, 300], function () {
    return [
        'post_count' => Post::published()->count(),
        'comment_count' => Comment::approved()->count(),
    ];
});
```

### Anti-Patterns to Avoid
- **Caching authenticated responses:** Response cache middleware must skip authenticated users or you'll leak private data
- **Over-caching:** Don't cache admin pages, form submissions, or rapidly changing content
- **Dark mode without FOUC prevention:** Always add inline script in `<head>` before CSS loads
- **Forgetting cache invalidation:** Every cached resource needs a clear invalidation strategy

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Response caching | Custom middleware + file cache | spatie/laravel-responsecache | Handles invalidation, tags, cache profiles, bypasses for authenticated users |
| Theme toggle | Custom JavaScript + cookies | Alpine.js x-data + localStorage | Bundled with Livewire, handles SSR, state management built-in |
| Backup scheduling | Custom artisan command + cron | spatie/laravel-backup | Handles cleanup retention, notifications, cloud storage, encryption |
| N+1 detection | Manual query counting | Laravel Debugbar / preventLazyLoading() | Automatic detection, query analysis, duplicate query highlighting |
| Performance monitoring | Custom timing code | Laravel Telescope / Debugbar | Request profiling, query timing, cache hit rates built-in |

**Key insight:** All five success criteria have mature, battle-tested packages. Custom solutions would take weeks and miss edge cases (cache stampedes, backup cleanup, FOUC, etc.).

## Common Pitfalls

### Pitfall 1: Flash of Unstyled Content (FOUC) on Dark Mode
**What goes wrong:** Page briefly shows light mode before dark mode activates
**Why it happens:** Alpine.js initializes after DOM loads, but CSS is already applied
**How to avoid:** Add inline `<script>` in `<head>` BEFORE CSS that checks localStorage and adds `.dark` class synchronously
**Warning signs:** Users report "flash" or "flicker" when loading pages

### Pitfall 2: Caching Authenticated User Responses
**What goes wrong:** User A sees User B's cached personalized content
**Why it happens:** Response cache doesn't differentiate by user session by default
**How to avoid:** Configure CacheProfile to return `false` for authenticated requests:
```php
// config/responsecache.php
'cache_profile' => \Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests::class,
```
This default profile already skips authenticated users.
**Warning signs:** User reports seeing wrong name, settings, or admin-only content

### Pitfall 3: Cache Stampede on High Traffic
**What goes wrong:** Cache expires, 100 simultaneous requests all hit database
**Why it happens:** Traditional caching has "thundering herd" problem on expiry
**How to avoid:** Use `Cache::flexible()` which serves stale data while ONE request rebuilds cache
**Warning signs:** Periodic database spikes every [cache TTL] seconds

### Pitfall 4: Missing Indexes on Polymorphic Relations
**What goes wrong:** Slow queries on taggables/categorizables tables
**Why it happens:** Polymorphic `morphs()` creates columns but not always optimal indexes
**How to avoid:** Add composite indexes on `(taggable_type, taggable_id)` for lookups:
```php
$table->index(['taggable_type', 'taggable_id']);
```
**Warning signs:** Slow tag/category listing pages, high query times in Debugbar

### Pitfall 5: Backup Scheduling During Daylight Saving Time
**What goes wrong:** Backup runs twice or skips a day
**Why it happens:** 02:00-03:00 is unstable during DST transitions
**How to avoid:** Schedule backups at 01:00 or 04:00, never 02:00-03:00
**Warning signs:** Missing backups twice a year (spring/fall)

### Pitfall 6: N+1 Queries in Comment Rendering
**What goes wrong:** Each comment triggers queries for author, replies
**Why it happens:** Nested relationships need explicit eager loading
**How to avoid:** Already handled in CommentSection.php with nested `with()` - verify no changes break this
**Warning signs:** Debugbar shows 50+ queries on post with many comments

## Code Examples

Verified patterns from official sources:

### Response Cache Middleware Setup (Laravel 11+)
```php
// Source: https://github.com/spatie/laravel-responsecache

// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Spatie\ResponseCache\Middlewares\CacheResponse::class,
    ]);
    $middleware->alias([
        'doNotCacheResponse' => \Spatie\ResponseCache\Middlewares\DoNotCacheResponse::class,
    ]);
})
```

### Skip Caching for Admin Routes
```php
// routes/web.php
Route::middleware(['auth', 'admin', 'doNotCacheResponse'])
    ->prefix('admin')
    ->group(function () {
        // Admin routes never cached
    });
```

### Dark Mode Toggle Component
```php
// Source: https://tailwindcss.com/docs/dark-mode

// resources/views/components/theme-toggle.blade.php
<button
    x-data="{ darkMode: $persist(false).as('theme') }"
    x-init="darkMode = localStorage.getItem('theme') === 'dark'"
    @click="darkMode = !darkMode;
            document.documentElement.classList.toggle('dark', darkMode);
            localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
    class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700"
>
    <span x-show="!darkMode"><!-- Sun icon --></span>
    <span x-show="darkMode"><!-- Moon icon --></span>
</button>
```

### Backup Scheduling (Laravel 11+)
```php
// Source: https://spatie.be/docs/laravel-backup/v9/installation-and-setup

// routes/console.php
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:clean')->daily()->at('01:00');
Schedule::command('backup:run')->daily()->at('01:30');
```

### Database Index Migration for Performance
```php
// database/migrations/xxxx_add_performance_indexes.php
public function up(): void
{
    // Posts: optimize for homepage listing
    Schema::table('posts', function (Blueprint $table) {
        $table->index('status'); // Filter by status
        $table->index(['status', 'published_at']); // Homepage query
    });

    // Polymorphic tables: optimize reverse lookups
    Schema::table('taggables', function (Blueprint $table) {
        $table->index(['taggable_type', 'taggable_id']);
    });

    Schema::table('categorizables', function (Blueprint $table) {
        $table->index(['categorizable_type', 'categorizable_id']);
    });
}
```

### S3 Backup Disk Configuration
```php
// config/filesystems.php
'disks' => [
    // ... existing disks ...

    'backups' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BACKUP_BUCKET'),
        'visibility' => 'private',
    ],
],
```

```php
// config/backup.php (after publishing)
'destination' => [
    'disks' => ['backups'],
],
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Tailwind `darkMode: 'class'` in config | `@custom-variant dark` in CSS | Tailwind v4 (Jan 2025) | No config file needed, CSS-only |
| Laravel Page Cache package | Native `Cache::flexible()` + spatie/responsecache | Laravel 11+ | Stale-while-revalidate built into core |
| Custom backup scripts | spatie/laravel-backup | 2015+ | Standard, handles retention/cleanup |
| Manual query profiling | Model::preventLazyLoading() | Laravel 9+ | Automatic N+1 detection in development |

**Deprecated/outdated:**
- `tailwind.config.js` darkMode option: Still works but v4 prefers CSS-based configuration
- renatomarinho/laravel-page-speed: Package optimizes HTML output, but modern HTTP/2 and gzip make this less impactful

## Open Questions

Things that couldn't be fully resolved:

1. **Lighthouse Score Variability**
   - What we know: Lighthouse scores vary between runs and depend on server location
   - What's unclear: Exact threshold for "under 1 second" - is this Time to First Byte, Largest Contentful Paint, or full page load?
   - Recommendation: Target LCP (Largest Contentful Paint) < 1s as primary metric, run multiple tests and average

2. **Response Cache Granularity**
   - What we know: Can clear entire cache or by URL pattern
   - What's unclear: Whether to clear all cache on any post update or use cache tags for granular invalidation
   - Recommendation: Start with full cache clear on post/comment changes (simple); optimize with tags only if needed

3. **Backup Storage Cost**
   - What we know: S3 Standard pricing, backup size depends on database + files
   - What's unclear: Exact storage costs for 3,870 posts + attachments over time
   - Recommendation: Start with S3 Standard, monitor costs, consider S3 Glacier for old backups if needed

## Sources

### Primary (HIGH confidence)
- [Laravel 12.x Cache Documentation](https://laravel.com/docs/12.x/cache) - Cache::flexible(), remember(), tags
- [Tailwind CSS Dark Mode Documentation](https://tailwindcss.com/docs/dark-mode) - @custom-variant, class strategy
- [spatie/laravel-responsecache GitHub](https://github.com/spatie/laravel-responsecache) - Installation, middleware, invalidation
- [spatie/laravel-backup Documentation](https://spatie.be/docs/laravel-backup/v9/installation-and-setup) - Setup, scheduling, S3

### Secondary (MEDIUM confidence)
- [Laravel News - Flexible Caching](https://laravel-news.com/boosting-app-speed-with-flexible-caching-laravel-in-practice-ep8) - Stale-while-revalidate patterns
- [DEV Community - Alpine.js Dark Mode](https://dev.to/rinas/toggle-dark-and-sunny-mode-using-alpinejs-tailwindcss-and-localstorage-7fo) - localStorage patterns
- [Database Indexing Best Practices](https://hafiz.dev/blog/database-indexing-in-laravel-boost-mysql-performance-with-smart-indexes) - Index strategy for Laravel

### Tertiary (LOW confidence)
- Various Medium articles on Laravel optimization - General patterns, verify with official docs

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - All packages are well-established, documented, Laravel ecosystem standards
- Architecture: HIGH - Patterns verified with official documentation
- Pitfalls: MEDIUM - Based on community experience and official documentation warnings
- Database optimization: MEDIUM - Indexes already partially in place, need to verify with EXPLAIN

**Research date:** 2026-01-25
**Valid until:** 2026-02-25 (30 days - stable libraries, unlikely to change significantly)
