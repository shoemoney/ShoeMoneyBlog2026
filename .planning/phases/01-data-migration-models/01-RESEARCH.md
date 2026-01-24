# Phase 1: Data Migration & Models - Research

**Researched:** 2026-01-24
**Domain:** WordPress to Laravel data migration, database schema design, Eloquent ORM
**Confidence:** HIGH

## Summary

Phase 1 involves migrating a substantial WordPress site (3,870 posts, 160,569 comments, 15,448 tags) from WordPress database tables (`wp2_` prefix in `shoemoney_wp`) to a normalized Laravel schema in `shoemoney26`. The migration must preserve all relationships, implement hybrid password authentication (phpass to bcrypt), and maintain SEO-critical URL structures.

The research reveals that **Laravel's Eloquent ORM provides first-class support for multi-database connections**, allowing models to simultaneously query both WordPress and Laravel databases during the transition. The standard approach uses **database seeders (not migrations) for data import**, with chunking for large datasets. WordPress-specific challenges include handling **serialized postmeta**, **3-table taxonomy structure**, and **phpass password hashes**.

The ecosystem provides battle-tested packages: **Corcel** for WordPress data modeling, **mikemclin/laravel-wp-password** for password compatibility, and Laravel's native **polymorphic many-to-many relationships** map perfectly to WordPress taxonomies.

**Primary recommendation:** Use database seeders with chunking for one-time data migration, create dual-connection Eloquent models for WordPress tables (read-only), implement hybrid password authentication via event listeners, and use attribute casting for WordPress serialized data.

## Standard Stack

The established libraries/tools for WordPress to Laravel migration:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel | 11.x | Application framework | Official LTS version with modern Eloquent features |
| Eloquent ORM | 11.x (bundled) | Database abstraction | Native multi-database, polymorphic relationships |
| PHP | 8.2+ | Runtime | Required for Laravel 11.x features |
| MySQL/MariaDB | 8.0+ / 10.3+ | Database | First-party Laravel support |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| corcel/corcel | 8.x | WordPress data models | Optional - if keeping WordPress database long-term |
| mikemclin/laravel-wp-password | 2.0+ | WordPress password hashing | Required - for phpass compatibility |
| webwizo/laravel-shortcodes | 10.x+ | Shortcode rendering | If preserving shortcodes instead of converting |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Seeders for data import | Migrations with data | Seeders better for one-time imports (won't bloat migration history) |
| Custom password validator | roots/wp-password-bcrypt | Custom gives control, package easier but adds WordPress dependency |
| Corcel models | Custom WordPress models | Corcel faster to implement, custom gives full control |

**Installation:**
```bash
composer require mikemclin/laravel-wp-password
# Optional: composer require corcel/corcel
```

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Models/
│   ├── Post.php                    # Laravel post model
│   ├── User.php                    # Laravel user model (hybrid auth)
│   ├── Comment.php                 # Laravel comment model
│   ├── Tag.php                     # Laravel tag model
│   ├── Category.php                # Laravel category model
│   └── WordPress/                  # WordPress database models (read-only)
│       ├── WpPost.php
│       ├── WpUser.php
│       ├── WpComment.php
│       ├── WpTerm.php
│       ├── WpTermTaxonomy.php
│       └── WpTermRelationship.php
├── Listeners/
│   └── UpgradePasswordOnLogin.php  # phpass → bcrypt upgrader
database/
├── migrations/
│   └── create_laravel_tables.php   # Laravel schema only (no data)
└── seeders/
    ├── DatabaseSeeder.php
    ├── PostSeeder.php              # Chunked post import
    ├── UserSeeder.php              # User migration with roles
    ├── CommentSeeder.php           # Comment threading preserved
    └── TaxonomySeeder.php          # Categories, tags, relationships
```

### Pattern 1: Multi-Database Configuration
**What:** Configure separate database connections for WordPress (source) and Laravel (target)
**When to use:** Always - allows models to access both databases during migration and runtime
**Example:**
```php
// config/database.php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'shoemoney26'),
        // Laravel database
    ],
    'wordpress' => [
        'driver' => 'mysql',
        'host' => env('WP_DB_HOST', '127.0.0.1'),
        'database' => env('WP_DB_DATABASE', 'shoemoney_wp'),
        'prefix' => 'wp2_',
        // WordPress database
    ],
],
```

### Pattern 2: Read-Only WordPress Models
**What:** Create Eloquent models for WordPress tables using the `wordpress` connection
**When to use:** To query source data during migration and for reference
**Example:**
```php
// app/Models/WordPress/WpPost.php
// Source: Laravel 11.x Eloquent documentation
namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpPost extends Model
{
    protected $connection = 'wordpress';
    protected $table = 'posts'; // Prefix auto-applied from config
    protected $primaryKey = 'ID';
    public $timestamps = false; // WordPress uses post_date, post_modified

    public function meta()
    {
        return $this->hasMany(WpPostMeta::class, 'post_id', 'ID');
    }

    public function terms()
    {
        return $this->belongsToMany(
            WpTerm::class,
            'term_relationships',
            'object_id',
            'term_taxonomy_id',
            'ID',
            'term_taxonomy_id'
        )->using(WpTermRelationship::class);
    }
}
```

### Pattern 3: Chunked Seeder for Large Datasets
**What:** Process large dataset imports in batches to avoid memory exhaustion
**When to use:** Always for datasets > 10,000 records (you have 160,569 comments)
**Example:**
```php
// database/seeders/CommentSeeder.php
// Source: Laravel community best practices
use App\Models\WordPress\WpComment;
use App\Models\Comment;

public function run(): void
{
    WpComment::where('comment_approved', '1')
        ->chunk(1000, function ($wpComments) {
            $laravelComments = [];

            foreach ($wpComments as $wpComment) {
                $laravelComments[] = [
                    'id' => $wpComment->comment_ID,
                    'post_id' => $wpComment->comment_post_ID,
                    'user_id' => $wpComment->user_id ?: null,
                    'parent_id' => $wpComment->comment_parent ?: null,
                    'author_name' => $wpComment->comment_author,
                    'author_email' => $wpComment->comment_author_email,
                    'content' => $wpComment->comment_content,
                    'created_at' => $wpComment->comment_date,
                ];
            }

            Comment::insert($laravelComments);
        });
}
```

### Pattern 4: Hybrid Password Authentication
**What:** Check Laravel bcrypt first, fall back to WordPress phpass, upgrade on success
**When to use:** Required for WordPress user migration with preserved passwords
**Example:**
```php
// app/Listeners/UpgradePasswordOnLogin.php
// Source: https://escapehatch.com/migrating-wordpress-user-passwords-to-laravel-11-application/
use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Hash;
use MikeMcLin\WpPassword\Facades\WpPassword;

class UpgradePasswordOnLogin
{
    public function handle(Attempting $event): void
    {
        $user = User::where('email', $event->credentials['email'])->first();

        if (!$user) return;

        // Check if password is WordPress phpass format ($P$ prefix)
        if (str_starts_with($user->password, '$P$')) {
            // Verify with WordPress hasher
            if (WpPassword::check($event->credentials['password'], $user->password)) {
                // Upgrade to bcrypt
                $user->password = Hash::make($event->credentials['password']);
                $user->save();
            }
        }
    }
}

// app/Providers/EventServiceProvider.php
protected $listen = [
    \Illuminate\Auth\Events\Attempting::class => [
        \App\Listeners\UpgradePasswordOnLogin::class,
    ],
];
```

### Pattern 5: WordPress Taxonomy as Polymorphic Many-to-Many
**What:** Model WordPress's term_relationships table as polymorphic relationship
**When to use:** To preserve taxonomy flexibility (tags/categories on posts/pages)
**Example:**
```php
// app/Models/Post.php
// Source: Laravel 11.x Eloquent Relationships documentation
public function tags()
{
    return $this->morphToMany(
        Tag::class,
        'taggable',
        'taggables',
        'taggable_id',
        'tag_id'
    )->withTimestamps();
}

public function categories()
{
    return $this->morphToMany(
        Category::class,
        'categorizable',
        'categorizables'
    )->withTimestamps();
}

// Migration creates polymorphic pivot table
Schema::create('taggables', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->morphs('taggable'); // Creates taggable_id, taggable_type
    $table->timestamps();
});
```

### Pattern 6: Attribute Casting for WordPress Data
**What:** Use Laravel's attribute casting to handle WordPress serialized data and dates
**When to use:** For postmeta values, user roles, and WordPress datetime formats
**Example:**
```php
// app/Models/Post.php
// Source: Laravel 11.x Eloquent Mutators documentation
protected function casts(): array
{
    return [
        'meta_data' => 'array',           // JSON cast for serialized meta
        'published_at' => 'datetime',     // WordPress datetime strings
        'is_sticky' => 'boolean',         // WordPress 0/1 to true/false
    ];
}

// Accessor for WordPress post_status enum
protected function status(): Attribute
{
    return Attribute::make(
        get: fn (string $value) => match($value) {
            'publish' => 'published',
            'draft' => 'draft',
            'pending' => 'pending',
            default => 'draft',
        }
    );
}
```

### Pattern 7: Self-Referencing for Comment Threading
**What:** Use Eloquent self-referencing relationship for comment replies
**When to use:** To preserve WordPress comment threading (comment_parent)
**Example:**
```php
// app/Models/Comment.php
// Source: Laravel 11.x Eloquent Relationships documentation
public function parent()
{
    return $this->belongsTo(Comment::class, 'parent_id');
}

public function replies()
{
    return $this->hasMany(Comment::class, 'parent_id');
}

// Query with recursive loading
$rootComments = Comment::whereNull('parent_id')
    ->with('replies.replies.replies')
    ->get();
```

### Anti-Patterns to Avoid
- **Data in migrations:** Don't put data import logic in migrations - use seeders. Migrations bloat over time and slow down new developer onboarding.
- **Editing deployed migrations:** Never modify migrations after they've run in production. Create new migrations for schema changes.
- **Direct password conversion:** Can't decrypt phpass to re-hash as bcrypt. Must upgrade transparently on login.
- **Non-idempotent seeders:** Seeders should use `updateOrCreate` or check existence to allow safe re-runs during development.
- **Ignoring database indexes:** 160K+ comments requires indexes on post_id, parent_id, created_at for performance.

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| WordPress password validation | Custom phpass hasher | `mikemclin/laravel-wp-password` | Handles edge cases, maintained, tested with multiple WP versions |
| WordPress data models | Manual table queries | `corcel/corcel` (optional) | Pre-built relationships, meta handling, taxonomy queries |
| Shortcode parsing | Regex replacements | `webwizo/laravel-shortcodes` OR convert during migration | Nested shortcodes, escaping, attribute parsing are complex |
| Large dataset imports | foreach loops | `chunk()` with batch inserts | Prevents memory exhaustion, ~100x faster |
| Database transaction safety | Manual try/catch | `DB::transaction()` with retries | Handles deadlocks, automatic rollback |
| Serialized data handling | `unserialize()` directly | Attribute casts with validation | Prevents object injection, type safety |

**Key insight:** WordPress has 20 years of edge cases in password hashing, serialization formats, and shortcode nesting. Community packages have already solved these problems and are battle-tested across thousands of migrations.

## Common Pitfalls

### Pitfall 1: Serialized Data Corruption
**What goes wrong:** WordPress stores complex data (user roles, widget settings, postmeta) as PHP serialized strings. Simple find/replace in SQL corrupts byte counts, breaking unserialize().
**Why it happens:** Serialized format includes string length prefixes: `s:13:"administrator"`. Changing the string without updating the length breaks parsing.
**How to avoid:** Never manipulate serialized data in SQL. Always unserialize in PHP, transform, re-serialize. Use Laravel's `array` cast for automatic JSON conversion.
**Warning signs:** `unserialize()` returning false, widgets disappearing after migration, user roles being empty arrays.

### Pitfall 2: WordPress Datetime Format Inconsistency
**What goes wrong:** WordPress stores dates in local timezone as strings without timezone info. Migration to Carbon/DateTime can shift dates.
**Why it happens:** WordPress uses `Y-m-d H:i:s` format (e.g., "2024-03-15 14:30:00") in site's configured timezone. Laravel expects UTC timestamps.
**How to avoid:** Set database timezone explicitly in migrations, use Laravel's `datetime` cast, verify timezone in `config/app.php` matches WordPress setting.
**Warning signs:** Post dates off by hours, scheduled posts publishing early/late, comment timestamps misaligned.

### Pitfall 3: Taxonomy Term ID vs Term Taxonomy ID Confusion
**What goes wrong:** WordPress has TWO IDs for taxonomies - `term_id` (the term itself) and `term_taxonomy_id` (the term+taxonomy combo). Using the wrong one breaks relationships.
**Why it happens:** WordPress allows same term name in multiple taxonomies (tag "Laravel" + category "Laravel"). The `term_relationships` table joins on `term_taxonomy_id`, not `term_id`.
**How to avoid:** Always use `term_taxonomy_id` for relationships. Query through `wp_term_taxonomy` table to get taxonomy type.
**Warning signs:** Tags appearing as categories, missing taxonomy assignments, duplicate term names causing incorrect associations.

### Pitfall 4: N+1 Query Explosion with Large Datasets
**What goes wrong:** Loading 3,870 posts with comments (160K) without eager loading causes millions of queries.
**Why it happens:** Eloquent lazy-loads relationships by default. `foreach ($posts as $post) { $post->comments }` makes 3,870 queries.
**How to avoid:** Always use `with()` for eager loading: `Post::with('comments', 'tags', 'categories')`. Use Laravel Debugbar to monitor query counts.
**Warning signs:** Seeder taking hours instead of minutes, database connection pool exhaustion, `max_execution_time` errors.

### Pitfall 5: Foreign Key Constraints Blocking Migration Order
**What goes wrong:** Seeding users after posts fails because post.user_id foreign key expects user to exist.
**Why it happens:** Foreign key constraints enforce referential integrity. Can't insert child before parent.
**How to avoid:** Either (1) seed in dependency order (users → posts → comments), (2) disable foreign key checks temporarily with `Schema::disableForeignKeyConstraints()`, or (3) use nullable foreign keys during migration.
**Warning signs:** `SQLSTATE[23000]: Integrity constraint violation`, seeder order mattering unexpectedly, need to run seeders multiple times.

### Pitfall 6: Enum Type Modification Not Supported
**What goes wrong:** WordPress uses `post_status` enum like ('publish', 'draft', 'pending'). Laravel migrations can't modify enums with Schema Builder.
**Why it happens:** Doctrine DBAL (used by Laravel migrations) doesn't support MySQL ENUM type modifications.
**How to avoid:** Either (1) use `DB::statement()` with raw SQL `ALTER TABLE`, (2) use string column instead of enum, or (3) create new column, migrate data, drop old.
**Warning signs:** "Unknown database type enum requested" error when running migrations, `change()` failing on enum columns.

### Pitfall 7: Memory Exhaustion on Large Queries
**What goes wrong:** `WpComment::all()` on 160,569 records loads entire dataset into memory (500MB+), crashes PHP.
**Why it happens:** Eloquent's `all()` and `get()` fetch complete result sets. PHP's memory limit (often 128-256MB) can't hold it.
**How to avoid:** Always use `chunk()` or `cursor()` for large datasets. Never use `all()` on unbounded queries.
**Warning signs:** "Allowed memory size exhausted" fatal error, PHP process killed by OOM, server becoming unresponsive during migration.

### Pitfall 8: WordPress URL Structure Dependencies
**What goes wrong:** WordPress stores full URLs in post_content (images, links). Migration breaks these if URL structure changes.
**Why it happens:** WordPress serializes full URLs in shortcodes, image src attributes, and internal links. These become broken links after migration.
**How to avoid:** Use find/replace during content migration to update domain/paths. Convert absolute URLs to relative where possible. Store media references by ID, not URL.
**Warning signs:** Broken images in post content, links pointing to old WordPress URLs, shortcodes with hardcoded domain names.

## Code Examples

Verified patterns from official sources:

### Multi-Database Migration Configuration
```php
// config/database.php
// Source: https://laravel.com/docs/11.x/database
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'shoemoney26'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'strict' => true,
    ],
    'wordpress' => [
        'driver' => 'mysql',
        'host' => env('WP_DB_HOST', '127.0.0.1'),
        'port' => env('WP_DB_PORT', '3306'),
        'database' => env('WP_DB_DATABASE', 'shoemoney_wp'),
        'username' => env('WP_DB_USERNAME', 'root'),
        'password' => env('WP_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'wp2_',
        'strict' => false, // WordPress uses non-strict mode
    ],
],
```

### Setting Model Connection
```php
// app/Models/WordPress/WpPost.php
// Source: https://laravel.com/docs/11.x/eloquent
namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpPost extends Model
{
    protected $connection = 'wordpress';
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    const CREATED_AT = 'post_date';
    const UPDATED_AT = 'post_modified';
}
```

### Chunked Seeding with Progress
```php
// database/seeders/PostSeeder.php
// Source: Laravel community best practices + https://markshust.com/2017/10/25/migrate-large-amounts-data-laravel-artisan-console-and-chunk/
use App\Models\WordPress\WpPost;
use App\Models\Post;

public function run(): void
{
    $total = WpPost::where('post_status', 'publish')
        ->where('post_type', 'post')
        ->count();

    $bar = $this->command->getOutput()->createProgressBar($total);
    $bar->start();

    WpPost::where('post_status', 'publish')
        ->where('post_type', 'post')
        ->chunk(100, function ($wpPosts) use ($bar) {
            $posts = $wpPosts->map(fn ($wp) => [
                'id' => $wp->ID,
                'user_id' => $wp->post_author,
                'title' => $wp->post_title,
                'slug' => $wp->post_name,
                'content' => $wp->post_content,
                'excerpt' => $wp->post_excerpt,
                'published_at' => $wp->post_date,
                'created_at' => $wp->post_date,
                'updated_at' => $wp->post_modified,
            ])->toArray();

            Post::insert($posts);
            $bar->advance(count($posts));
        });

    $bar->finish();
    $this->command->newLine();
}
```

### Migration with Foreign Keys
```php
// database/migrations/create_posts_table.php
// Source: https://laravel.com/docs/11.x/migrations
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->text('excerpt')->nullable();
    $table->timestamp('published_at')->nullable();
    $table->timestamps();

    $table->index('published_at');
    $table->index(['user_id', 'published_at']);
});
```

### Polymorphic Taxonomy Relationship
```php
// database/migrations/create_taggables_table.php
// Source: https://laravel.com/docs/11.x/eloquent-relationships
Schema::create('taggables', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->morphs('taggable'); // Creates taggable_id + taggable_type
    $table->timestamps();

    $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
});

// app/Models/Post.php
public function tags()
{
    return $this->morphToMany(Tag::class, 'taggable');
}

// app/Models/Page.php
public function tags()
{
    return $this->morphToMany(Tag::class, 'taggable');
}
```

### Comment Threading with Self-Reference
```php
// database/migrations/create_comments_table.php
// Source: https://laravel.com/docs/11.x/eloquent-relationships
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
    $table->string('author_name')->nullable();
    $table->string('author_email')->nullable();
    $table->text('content');
    $table->timestamps();

    $table->index('post_id');
    $table->index('parent_id');
    $table->index('created_at');
});

// app/Models/Comment.php
public function parent()
{
    return $this->belongsTo(Comment::class, 'parent_id');
}

public function replies()
{
    return $this->hasMany(Comment::class, 'parent_id');
}
```

### Attribute Casting for WordPress Data
```php
// app/Models/User.php
// Source: https://laravel.com/docs/11.x/eloquent-mutators
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'meta' => 'array', // For serialized WordPress user meta
    ];
}

// Accessor to extract WordPress roles from meta
protected function wpRoles(): Attribute
{
    return Attribute::make(
        get: function () {
            $meta = $this->meta['wp_capabilities'] ?? [];
            return is_array($meta) ? array_keys($meta) : [];
        }
    );
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Accessors/Mutators as methods | `Attribute::make()` with closures | Laravel 9 (2022) | Cleaner syntax, better IDE support, combined get/set |
| Magic `get{Attribute}Attribute()` | Protected methods returning Attribute | Laravel 9 (2022) | More explicit, typed return values |
| WordPress MD5 passwords | phpass with portable hashing | WordPress 2.5 (2008) | Better security but still weak by modern standards |
| phpass passwords | bcrypt hashing | WordPress 6.8 (2025) | Significantly stronger, aligns with modern PHP |
| REST API for WP data | Direct database access (Corcel) | Ongoing | 10-100x faster, no HTTP overhead |
| `all()` for large datasets | `chunk()` or `cursor()` | Always available | Prevents memory exhaustion |
| Enum columns in migrations | String columns with validation | Laravel/DBAL limitation | Enum modification not supported in migrations |

**Deprecated/outdated:**
- **WordPress MD5 passwords:** Fully deprecated. Even phpass is being replaced by bcrypt in WP 6.8+
- **Laravel 8.x accessor syntax:** Still works but Laravel 9+ `Attribute::make()` preferred
- **Corcel for fresh migrations:** Better to build native Laravel models and import data once rather than keep WordPress schema long-term
- **Migration-based data imports:** Community consensus is seeders for one-time data, migrations for schema only

## Open Questions

Things that couldn't be fully resolved:

1. **Shortcode Conversion Strategy**
   - What we know: Several packages exist (`webwizo/laravel-shortcodes`, `vedmant/laravel-shortcodes`) for rendering WordPress-style shortcodes in Laravel
   - What's unclear: Whether to preserve shortcodes in content or convert them to HTML/Blade during migration. Conversion is one-time but rigid; preservation requires package dependency.
   - Recommendation: Audit most common shortcodes in 3,870 posts first. If < 5 types, convert to HTML during migration. If complex/nested, preserve with package.

2. **WordPress Metadata Storage Pattern**
   - What we know: WordPress uses EAV pattern (entity-attribute-value) via `wp_postmeta`. Laravel prefers JSON columns or normalized tables.
   - What's unclear: Best practice for migrating diverse postmeta (SEO fields, featured images, custom fields). Options: (1) JSON column, (2) normalize common fields to columns + JSON for rest, (3) keep EAV pattern
   - Recommendation: Normalize frequently-queried meta (featured_image_id, seo_title, seo_description) to columns. Store remaining meta as JSON. This balances query performance with flexibility.

3. **User Role Mapping Complexity**
   - What we know: WordPress stores roles as serialized array in `wp_usermeta` with capability keys. Laravel uses separate `roles` and `permissions` tables.
   - What's unclear: How to map WordPress capabilities (granular) to Laravel roles/permissions (simpler). Direct mapping may be too permissive or restrictive.
   - Recommendation: Create 1:1 role mapping for common roles (Administrator → admin, Editor → editor, Author → author). Audit custom capabilities during migration and map to Laravel permissions manually.

4. **Post Revision Handling**
   - What we know: WordPress stores every post revision as separate row in `wp_posts` with `post_type = 'revision'` and `post_parent = original_post_id`
   - What's unclear: Whether to migrate revisions (audit trail value) or skip them (database bloat)
   - Recommendation: Skip revisions for initial migration. WordPress revisions are rarely accessed and would 3-5x the posts table size. If audit trail is critical, consider separate archive table.

5. **Database Timezone Configuration**
   - What we know: WordPress stores dates in site timezone. Laravel prefers UTC storage with app-level timezone conversion.
   - What's unclear: Whether to store migrated dates as-is (preserves WordPress behavior) or convert to UTC (Laravel best practice)
   - Recommendation: Convert all dates to UTC during migration, set `config/app.php` timezone to match WordPress site timezone for display. This follows Laravel conventions and prevents future confusion.

## Sources

### Primary (HIGH confidence)
- Laravel 11.x Database Configuration - https://laravel.com/docs/11.x/database
- Laravel 11.x Migrations - https://laravel.com/docs/11.x/migrations
- Laravel 11.x Eloquent ORM - https://laravel.com/docs/11.x/eloquent
- Laravel 11.x Eloquent Relationships - https://laravel.com/docs/11.x/eloquent-relationships
- Laravel 11.x Eloquent Mutators & Casting - https://laravel.com/docs/11.x/eloquent-mutators
- Laravel 11.x Database Seeding - https://laravel.com/docs/11.x/seeding

### Secondary (MEDIUM confidence)
- [Migrating WordPress User Passwords to Laravel 11](https://escapehatch.com/migrating-wordpress-user-passwords-to-laravel-11-application/)
- [Migrating WordPress users and passwords to Laravel](https://ejntaylor.com/migrating-wordpress-users-to-laravel/)
- [GitHub: roots/wp-password-bcrypt](https://github.com/roots/wp-password-bcrypt)
- [Packagist: mikemclin/laravel-wp-password](https://packagist.org/packages/mikemclin/laravel-wp-password)
- [GitHub: corcel/corcel](https://github.com/corcel/corcel)
- [Using Corcel in Laravel to CRUD WordPress Data](https://adevait.com/laravel/using-corcel-in-laravel)
- [Migrate large amounts of data in Laravel with Artisan Console and Chunk](https://markshust.com/2017/10/25/migrate-large-amounts-data-laravel-artisan-console-and-chunk/)
- [Laravel Multiple Database Connections](https://fideloper.com/laravel-multiple-database-connections)
- [Understanding WordPress Taxonomy Table Relationships](https://strangework.com/2014/08/08/understanding-wordpress-taxonomy-table-relationships/)

### Tertiary (LOW confidence)
- [Migrations or Seeders? The Ultimate Question (Medium)](https://medium.com/@codebyjeff/migrations-or-seeders-the-ultimate-question-25d7bf73636) - Community perspective, not official
- [Laravel Shortcodes packages](https://github.com/webwizo/laravel-shortcodes) - Multiple packages, no clear "winner"
- [WordPress 6.8 will use bcrypt for password hashing](https://make.wordpress.org/core/2025/02/17/wordpress-6-8-will-use-bcrypt-for-password-hashing/) - Announced but not yet released as of research date

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Laravel documentation is authoritative, versions confirmed via official docs
- Architecture: HIGH - Patterns verified in Laravel 11.x documentation with working code examples
- WordPress integration: MEDIUM - Community packages well-tested but not official Laravel
- Password migration: MEDIUM - Multiple sources confirm approach, package exists but last updated 2018
- Pitfalls: MEDIUM - Based on community experience reports and WordPress codex, not empirical testing

**Research date:** 2026-01-24
**Valid until:** 2026-02-24 (30 days - Laravel stable, WordPress ecosystem slow-moving)
