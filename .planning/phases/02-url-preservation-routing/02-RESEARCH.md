# Phase 2: URL Preservation & Routing - Research

**Researched:** 2026-01-24
**Domain:** Laravel Routing, SEO URL Preservation, WordPress URL Migration
**Confidence:** HIGH

## Summary

This phase focuses on preserving the SEO value of 3,870 published WordPress posts by implementing exact URL matching in Laravel. The WordPress permalink structure `/%year%/%monthnum%/%day%/%postname%/` (e.g., `/2015/08/15/how-i-made-money/`) must be matched by Laravel routes without any 404 errors on indexed content.

The standard approach uses Laravel's native routing with regex constraints for date-based URLs, combined with route model binding for clean resolution. For sitemap generation, spatie/laravel-sitemap (v7.3.8) is the industry standard with 12.6M+ installs. URL verification should use a custom Artisan command since the previously popular spatie/laravel-link-checker is now archived.

**Primary recommendation:** Use Laravel's `where()` route constraints with regex patterns to match WordPress date-based URLs, implement `getRouteKeyName()` on models for slug-based lookups, and create a custom URL verification command that queries migrated post URLs directly.

## Standard Stack

The established libraries/tools for this domain:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel Routing | 12.x | Date-based URL matching with regex constraints | Built-in, no external deps, full regex support |
| spatie/laravel-sitemap | 7.3.8 | Sitemap generation | 12.6M+ installs, Laravel 11/12 support, Eloquent integration |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| spatie/laravel-missing-page-redirector | latest | Legacy redirect handling | When old URL variations need 301 redirects to canonical |
| GuzzleHttp | bundled | HTTP client for URL verification | Used by sitemap package, available for custom verification |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| spatie/laravel-sitemap | Manual sitemap generation | More control but must handle sitemap index, image/video tags, scheduling |
| Custom verification | spatie/laravel-link-checker | Package archived Jan 2023, no longer maintained |
| Native route redirects | Database redirector | Database adds flexibility but overhead for simple redirect maps |

**Installation:**
```bash
composer require spatie/laravel-sitemap
# Optional for redirect handling:
composer require spatie/laravel-missing-page-redirector
```

## Architecture Patterns

### Recommended Route Structure
```
routes/
├── web.php              # Main routes including WordPress-style URLs
```

```
app/
├── Http/
│   └── Controllers/
│       ├── PostController.php       # Single post display
│       ├── PageController.php       # Static pages
│       ├── CategoryController.php   # Category archives
│       ├── TagController.php        # Tag archives
│       └── SitemapController.php    # Sitemap generation
├── Console/
│   └── Commands/
│       └── VerifyUrls.php           # URL verification command
```

### Pattern 1: WordPress Date-Based Post Routes
**What:** Match `/{year}/{month}/{day}/{slug}/` exactly like WordPress
**When to use:** For all published posts with the WordPress permalink structure
**Example:**
```php
// Source: Laravel 12.x Routing Documentation
// routes/web.php

Route::get('/{year}/{month}/{day}/{slug}', [PostController::class, 'show'])
    ->where([
        'year' => '[0-9]{4}',
        'month' => '[0-9]{2}',
        'day' => '[0-9]{2}',
        'slug' => '[a-z0-9\-]+',
    ])
    ->name('post.show');
```

### Pattern 2: Route Model Binding with Custom Resolution
**What:** Resolve posts by slug + published_at date validation
**When to use:** Ensures URL date matches post's actual publish date
**Example:**
```php
// Source: Laravel 12.x Routing Documentation
// app/Providers/AppServiceProvider.php

use Illuminate\Support\Facades\Route;
use App\Models\Post;

public function boot(): void
{
    Route::bind('slug', function (string $value, $route) {
        $year = $route->parameter('year');
        $month = $route->parameter('month');
        $day = $route->parameter('day');

        return Post::where('slug', $value)
            ->whereYear('published_at', $year)
            ->whereMonth('published_at', $month)
            ->whereDay('published_at', $day)
            ->where('status', 'published')
            ->firstOrFail();
    });
}
```

### Pattern 3: Category and Tag Archive Routes
**What:** Match WordPress archive URLs `/category/{slug}/` and `/tag/{slug}/`
**When to use:** For taxonomy archive pages
**Example:**
```php
// routes/web.php
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])
    ->name('category.show');

Route::get('/tag/{tag:slug}', [TagController::class, 'show'])
    ->name('tag.show');
```

### Pattern 4: Page Routes (Slug-Only)
**What:** Match WordPress page URLs `/{slug}/`
**When to use:** For static pages
**Example:**
```php
// routes/web.php
// MUST be registered AFTER more specific routes to avoid conflicts
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->name('page.show')
    ->where('slug', '[a-z0-9\-]+');
```

### Anti-Patterns to Avoid
- **Catch-all routes early:** Placing `/{slug}` before date-based routes will intercept post URLs. Order matters: specific routes first.
- **Optional route segments:** Using `{year?}/{month?}` creates ambiguity. Use explicit routes for each URL pattern.
- **Not validating dates:** Accepting any 4-digit year without checking it matches the post allows URL manipulation.
- **Missing trailing slash handling:** WordPress URLs often have trailing slashes. Laravel strips them by default which is correct, but redirects may be needed.

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Sitemap generation | Custom XML builder | spatie/laravel-sitemap | Handles sitemap index, image tags, lastmod, priorities, scheduling |
| Sitemap scheduling | Custom cron job | Laravel Scheduler + package | Built-in scheduling with package integration |
| Legacy URL redirects | Manual redirect map | spatie/laravel-missing-page-redirector | Wildcard patterns, database support, 301/302 control |
| Slug generation | Manual string manipulation | Str::slug() | Handles unicode, transliteration, reserved chars |

**Key insight:** Sitemap generation has numerous edge cases (multiple sitemaps for large sites, image/video sitemaps, news sitemaps, proper lastmod handling). The spatie package handles all of these with minimal configuration.

## Common Pitfalls

### Pitfall 1: Route Order Conflicts
**What goes wrong:** Page catch-all route `/{slug}` matches before post routes like `/2015/08/15/slug/`
**Why it happens:** Laravel matches routes in order of definition
**How to avoid:** Define specific routes (date-based posts, categories, tags) BEFORE generic routes (pages)
**Warning signs:** Posts returning 404 while pages work, or pages showing when posts expected

### Pitfall 2: Missing Date Validation
**What goes wrong:** URL `/2015/08/15/my-post/` loads a post that was actually published on a different date
**Why it happens:** Only matching on slug without verifying the date
**How to avoid:** Use compound query: `where('slug', $slug)->whereYear('published_at', $year)->whereMonth()...`
**Warning signs:** Multiple URLs resolving to same post, SEO duplicate content issues

### Pitfall 3: Trailing Slash Inconsistency
**What goes wrong:** `/2015/08/15/my-post` and `/2015/08/15/my-post/` both work without redirect
**Why it happens:** Laravel doesn't enforce trailing slash convention
**How to avoid:** Configure web server or middleware to 301 redirect to canonical version (WordPress uses trailing slash)
**Warning signs:** Google Search Console showing duplicate URLs, inconsistent link building

### Pitfall 4: Slug Collisions Between Content Types
**What goes wrong:** A page with slug "contact" and a post with slug "contact" conflict
**Why it happens:** Flat URL namespace for pages vs hierarchical for posts
**How to avoid:** Check for slug uniqueness across content types OR rely on route order (date-based routes won't match page slugs)
**Warning signs:** Wrong content type displayed, 404 on valid content

### Pitfall 5: Sitemap Size Limits
**What goes wrong:** Sitemap with 3,870+ posts exceeds 50,000 URL limit or 50MB size
**Why it happens:** Not splitting into sitemap index
**How to avoid:** Use spatie/laravel-sitemap which auto-creates sitemap index for large sites
**Warning signs:** Sitemap validation failures, incomplete indexing

### Pitfall 6: Not Testing Actual WordPress URLs
**What goes wrong:** Routes work in isolation but fail for real migrated content
**Why it happens:** Edge cases in slugs (special characters, numbers, very long slugs)
**How to avoid:** Export actual WordPress URLs and verify each resolves correctly
**Warning signs:** Random 404s on live site, user complaints about bookmarked links

## Code Examples

Verified patterns from official sources:

### WordPress-Style Post Route with Full Validation
```php
// Source: Laravel 12.x Routing Docs + Custom Implementation
// routes/web.php

Route::get('/{year}/{month}/{day}/{slug}', [PostController::class, 'show'])
    ->where([
        'year' => '[0-9]{4}',
        'month' => '[0-9]{2}',
        'day' => '[0-9]{2}',
        'slug' => '[a-z0-9\-]+',
    ])
    ->name('post.show');
```

### PostController with Date Validation
```php
// app/Http/Controllers/PostController.php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(string $year, string $month, string $day, string $slug)
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->whereYear('published_at', $year)
            ->whereMonth('published_at', $month)
            ->whereDay('published_at', $day)
            ->where('status', 'published')
            ->firstOrFail();

        return view('post.show', compact('post'));
    }
}
```

### Sitemap Generation with Eloquent Models
```php
// Source: spatie/laravel-sitemap GitHub README
// app/Console/Commands/GenerateSitemap.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Models\Tag;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap';

    public function handle(): int
    {
        $sitemap = Sitemap::create();

        // Add posts with WordPress-style URLs
        Post::published()->each(function (Post $post) use ($sitemap) {
            $sitemap->add(
                Url::create($post->url)
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8)
            );
        });

        // Add pages
        Page::all()->each(function (Page $page) use ($sitemap) {
            $sitemap->add(
                Url::create($page->url)
                    ->setLastModificationDate($page->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.6)
            );
        });

        // Add categories
        Category::all()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create($category->url)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.5)
            );
        });

        // Add tags
        Tag::all()->each(function (Tag $tag) use ($sitemap) {
            $sitemap->add(
                Url::create($tag->url)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.3)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
        return Command::SUCCESS;
    }
}
```

### URL Verification Command
```php
// app/Console/Commands/VerifyUrls.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Models\Tag;

class VerifyUrls extends Command
{
    protected $signature = 'urls:verify {--type=all : Type to verify (posts, pages, categories, tags, all)}';
    protected $description = 'Verify all migrated URLs return 200 status';

    public function handle(): int
    {
        $baseUrl = config('app.url');
        $failures = [];

        if (in_array($this->option('type'), ['all', 'posts'])) {
            $this->info('Verifying posts...');
            $bar = $this->output->createProgressBar(Post::published()->count());

            Post::published()->chunk(100, function ($posts) use ($baseUrl, &$failures, $bar) {
                foreach ($posts as $post) {
                    $url = $baseUrl . $post->url;
                    try {
                        $response = Http::timeout(10)->get($url);
                        if ($response->status() !== 200) {
                            $failures[] = [
                                'type' => 'post',
                                'url' => $url,
                                'status' => $response->status(),
                            ];
                        }
                    } catch (\Exception $e) {
                        $failures[] = [
                            'type' => 'post',
                            'url' => $url,
                            'status' => 'error: ' . $e->getMessage(),
                        ];
                    }
                    $bar->advance();
                }
            });
            $bar->finish();
            $this->newLine();
        }

        // Similar loops for pages, categories, tags...

        if (count($failures) > 0) {
            $this->error('Found ' . count($failures) . ' URL failures:');
            $this->table(['Type', 'URL', 'Status'], $failures);
            return Command::FAILURE;
        }

        $this->info('All URLs verified successfully!');
        return Command::SUCCESS;
    }
}
```

### Legacy URL Redirect Configuration
```php
// Source: spatie/laravel-missing-page-redirector GitHub
// config/missing-page-redirector.php

return [
    'redirects' => [
        // Handle any URL variations that need redirecting
        // Example: old category URL format to new
        '/category/{slug}/page/{page}' => '/category/{slug}?page={page}',

        // Example: feed URLs (WordPress RSS)
        '/feed' => '/rss',
        '/feed/rss' => '/rss',
        '/feed/rss2' => '/rss',
        '/feed/atom' => '/rss',
    ],

    'redirect_status_codes' => [
        \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND,
    ],
];
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| spatie/laravel-link-checker | Custom verification command | Jan 2023 (archived) | Build simple custom command using Http facade |
| Manual XML sitemap | spatie/laravel-sitemap | Stable | 12.6M installs, actively maintained |
| Route::redirect() for legacy URLs | spatie/laravel-missing-page-redirector | Optional | Database/config redirects with wildcards |

**Deprecated/outdated:**
- `spatie/laravel-link-checker`: Archived January 2023, no longer maintained. Use custom Artisan command instead.

## Open Questions

Things that couldn't be fully resolved:

1. **Pagination URL structure**
   - What we know: WordPress uses `/page/2/` for archive pagination, query params for post pagination
   - What's unclear: Exact pagination URLs used on this WordPress site
   - Recommendation: Check exported URLs for pagination patterns, implement after core routes work

2. **Author archive URLs**
   - What we know: WordPress typically uses `/author/{username}/`
   - What's unclear: Whether author archives were used/indexed on this site
   - Recommendation: Add if SEO audit shows indexed author URLs

3. **Feed URLs**
   - What we know: WordPress exposes `/feed/`, `/feed/rss/`, `/feed/atom/`
   - What's unclear: Whether feeds should redirect to new feed system or 410 Gone
   - Recommendation: Plan feed handling based on site requirements

## Sources

### Primary (HIGH confidence)
- [Laravel 12.x Routing Documentation](https://laravel.com/docs/12.x/routing) - Route parameters, constraints, model binding
- [Laravel 12.x URL Generation](https://laravel.com/docs/12.x/urls) - Named routes, URL helpers
- [Laravel 12.x HTTP Tests](https://laravel.com/docs/12.x/http-tests) - Testing routes, status assertions
- [spatie/laravel-sitemap GitHub](https://github.com/spatie/laravel-sitemap) - v7.3.8, Laravel 11/12 support verified

### Secondary (MEDIUM confidence)
- [spatie/laravel-missing-page-redirector GitHub](https://github.com/spatie/laravel-missing-page-redirector) - Redirect patterns, configuration
- [Laravel SEO best practices 2025](https://www.plerdy.com/blog/ultimate-laravel-seo-guide/) - URL structure, redirects
- [301 Redirect SEO 2025](https://discoverwebtech.com/digital-marketing/seo/is-301-redirect-good-for-seo/) - SEO impact of redirects

### Tertiary (LOW confidence)
- Laravel.io forum discussions on WordPress permalink migration - Implementation patterns

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Verified with official Laravel docs and Packagist
- Architecture patterns: HIGH - Based on Laravel 12.x official routing documentation
- Pitfalls: MEDIUM - Based on community patterns and migration experience, some site-specific
- URL verification approach: MEDIUM - Custom solution since spatie package archived

**Research date:** 2026-01-24
**Valid until:** 60 days (Laravel routing is stable, packages may update)
