# Technology Stack

**Project:** ShoeMoney Blog (WordPress to Laravel Migration)
**Researched:** 2026-01-24
**Overall Confidence:** HIGH

## Recommended Stack

### Core Framework

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Laravel | 12.x | Application framework | Already in use. Latest stable release (Feb 2025) with no breaking changes from 11.x. Industry standard for PHP web apps with excellent ecosystem support. |
| PHP | ^8.2 | Runtime | Laravel 12 requires PHP 8.2+. Use 8.4 if possible for latest features (property hooks, typed properties) but 8.2 is minimum for broadest package compatibility. |
| MySQL | 8.0+ | Primary database | Already in use for WordPress. Contains existing blog data. Excellent Laravel Eloquent support. |

**Confidence:** HIGH - Verified from official Laravel 12.x documentation and package requirements.

### Frontend Stack

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Livewire | ^4.0.3 | Interactive UI framework | Latest major release (Jan 2026) brings single-file components, Islands architecture for partial page updates. Perfect for blog interactivity (comments, search) without heavy JavaScript. Requires PHP 8.1+, supports Laravel 10-12. |
| Tailwind CSS | 4.x | Styling framework | Already in use. Default with Laravel 12. Utility-first approach pairs perfectly with Livewire components. |
| Alpine.js | (bundled with Livewire 4) | Client-side interactivity | Automatically included with Livewire 4. Lightweight JS for UI enhancements (dropdowns, modals). No separate installation needed. |
| Vite | (default) | Build tool | Already configured. Default Laravel asset bundler. Fast HMR, better DX than Webpack. |
| Flowbite | ^2.x | UI component library | Optional but recommended. Pre-built Tailwind components (buttons, modals, dropdowns) that work with Livewire. Saves time on common UI patterns. Open-source, actively maintained. |

**Confidence:** HIGH - Livewire 4 version verified from Packagist (v4.0.3, Jan 23 2026). Tailwind 4 is default in Laravel 12 per official docs.

### Search & Performance

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Laravel Scout | ^10.23.0 | Search abstraction layer | Official Laravel package for full-text search. Clean API, queue support, multiple driver support. Latest stable Dec 2025. |
| Algolia PHP Client | ^4.37.3 | Algolia integration | Required by Scout's Algolia driver. Fast, hosted search with Laravel-first documentation. Version 4.37.3 (Jan 20 2026) requires PHP 8.1+ (note: NOT compatible with PHP 8.3.0). |
| Redis | 7.x | Cache & queue backend | Use for Scout queue, response caching, session storage. Significantly faster than file-based caching for blog traffic spikes. |

**Confidence:** HIGH - Scout and Algolia versions verified from Packagist. Algolia explicitly requested in project requirements.

**Why Algolia over Meilisearch:**
- User preference (per project context)
- Better Laravel/Scout documentation and examples
- Proven at scale with high-traffic content sites
- Advanced features (typo tolerance, relevance tuning) out-of-box
- Trade-off: Hosted (cost) vs self-hosted (Meilisearch), but hosting complexity avoided

### Content Management

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| league/commonmark | ^2.x | Markdown parsing | Parse markdown to HTML for blog posts. Extensible, PHP 8.2+ compatible. Used by Laravel internally. If storing content as markdown (optional). |
| cviebrock/eloquent-sluggable | ^12.0.0 | SEO-friendly URLs | Auto-generate slugs from post titles. Critical for URL preservation during WordPress migration. Version 12 released Feb 2025 for Laravel 12, requires PHP 8.2+. |
| spatie/laravel-sitemap | ^7.3.8 | XML sitemap generation | Auto-generate sitemaps for SEO. Supports model-based generation (add all posts easily). Version 7.3.8 (Nov 2025) requires PHP 8.2+, Laravel 11-12. |
| spatie/laravel-feed | ^4.4.4 | RSS/Atom feed generation | Generate RSS feeds from blog posts. Implement `Feedable` interface on Post model. Supports RSS, Atom, JSON formats. Version 4.4.4 (Jan 5 2026), requires PHP 8.2+, Laravel 10-12. |

**Confidence:** MEDIUM-HIGH - Versions verified from Packagist. League/commonmark is optional (only if storing content as markdown vs HTML). All other packages are standard for Laravel blogs.

### Authentication & Authorization

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Laravel Breeze | (default scaffolding) | Authentication starter kit | Already likely installed if auth scaffolding exists. Minimal, Livewire-compatible. Use if starting fresh. |
| spatie/laravel-permission | ^6.24.0 | Role-based access control | Industry standard RBAC package (82M+ downloads). Database-driven roles/permissions for multi-author blog. Supports Laravel 8-12, PHP 8.0+. Latest Dec 2025. |

**Confidence:** HIGH - Spatie permission version verified from Packagist. Package is battle-tested standard for RBAC in Laravel.

**Roles needed for blog:**
- Admin (full access)
- Editor (create/edit any posts, moderate comments)
- Author (create/edit own posts)
- Moderator (manage comments only)

### Comments & Moderation

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| forxer/laravel-gravatar | ^5.0.0 | Gravatar avatars | Display user avatars in comments. Latest version (Nov 2025) requires PHP 8.4+, Laravel 12+. Includes Eloquent casts, preset configs. Most actively maintained Gravatar package for Laravel. |
| Custom implementation | - | Comment system | Build custom vs package. Packages like `hootlex/laravel-moderation` exist but are Laravel 5.x era. Custom tables (comments, comment_reports) give full control over moderation workflow. |

**Confidence:** HIGH for Gravatar (verified Packagist). MEDIUM for custom comments (recommended approach but requires implementation).

**Why custom comments over package:**
- Existing packages outdated (Laravel 5.x)
- Simple schema: `comments` table with `post_id`, `user_id`, `parent_id` (for replies), `status` (pending/approved/spam)
- Livewire perfect for comment form + real-time updates
- First-time moderation: WHERE `user_id` NOT IN (SELECT DISTINCT user_id FROM comments WHERE status = 'approved')

### Data Migration

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Custom Artisan Command | - | WordPress import | Build custom vs `leeovery/wordpress-to-laravel` (package exists but may be outdated). Read from `shoemoney-blog-export.sql`, map WP tables to Laravel models. |
| spatie/laravel-missing-page-redirector | ^2.x | 301 redirects for old URLs | Preserve SEO by redirecting old WordPress URLs to new Laravel routes. Package stores redirects in DB, catches 404s, auto-redirects. Critical for 20 years of inbound links. |

**Confidence:** MEDIUM - Custom import recommended (gives full control over 1.3GB SQL file). Missing page redirector is Spatie package (trusted) but version not verified (marked 2.x as likely).

**WordPress to Laravel mapping:**
- `wp_posts` → `posts` table (preserve `post_name` as slug)
- `wp_terms` + `wp_term_taxonomy` → `categories`, `tags` tables
- `wp_comments` → `comments` table
- `wp_users` → `users` table (hash passwords with bcrypt during import)

**Import strategy for large dataset:**
- Chunk reads (1000 records at a time) to avoid memory limits
- Use database transactions
- Queue import jobs for background processing
- Log progress for resume-ability

### DevOps & Monitoring

| Technology | Version | Purpose | Why |
|------------|---------|---------|-----|
| Laravel Horizon | ^5.x | Queue monitoring | Dashboard for Redis queues. Critical for monitoring Scout indexing, import jobs. Real-time metrics, failed job retry. |
| spatie/laravel-backup | ^9.3.7 | Automated backups | Schedule DB + file backups. Version 9.3.7 (Nov 2025) requires PHP 8.3+, Laravel 12.40+. Store backups to S3/DigitalOcean Spaces. Critical for recovering from attacks (security motivation for migration). |
| Laravel Telescope | ^5.x | Debugging & monitoring | Request/query debugging in local/staging. Disable in production or protect with auth middleware. |

**Confidence:** HIGH for Backup (verified Packagist). MEDIUM-HIGH for Horizon/Telescope (standard Laravel packages, versions estimated).

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| spatie/laravel-responsecache | ^7.x | HTTP response caching | Cache rendered blog post pages. Invalidate on post update. Reduces DB queries for high-traffic posts. Optional but recommended for performance. |
| butschster/laravel-meta-tags | ^2.x | SEO meta tag management | Manage meta tags, OpenGraph, Twitter Card from controllers/models. Alternative: build custom (simple `<meta>` tags in Blade). Use if want centralized SEO management. |
| intervention/image | ^3.x | Image manipulation | Resize featured images, generate thumbnails. Only if blog includes image uploads. May not be needed if WordPress images preserved as-is. |

**Confidence:** MEDIUM - Packages exist and are well-maintained, but versions not fully verified. Optional packages depending on feature scope.

## Alternatives Considered

| Category | Recommended | Alternative | Why Not |
|----------|-------------|-------------|---------|
| Frontend Framework | Livewire 4 | Inertia.js + Vue | Project preference for PHP-only stack. Livewire simpler mental model for content sites. Inertia requires Vue/React knowledge. |
| Search Engine | Algolia (via Scout) | Meilisearch | User preference for Algolia. Meilisearch is self-hosted (cheaper but more DevOps). Algolia has better Laravel docs. |
| Search Engine | Algolia | MySQL full-text search | Scout's database driver works but poor relevance for blog search. No typo tolerance, slower for large datasets. |
| Admin Panel | Custom (Livewire) | Filament | Project explicitly wants custom admin. Filament is excellent but opinionated. Custom gives full UX control. |
| Comments | Custom Livewire | Disqus/3rd-party | Custom gives data ownership, no external JS, matches site design. Disqus has ads/tracking concerns. |
| Markdown Parser | league/commonmark | Built-in `Str::markdown()` | League/commonmark more extensible. Laravel's helper uses league/commonmark internally anyway. Either works. |
| Slugs | eloquent-sluggable | Manual implementation | Package handles edge cases (duplicate slugs, special chars). Manual is ~20 lines but error-prone. |
| RBAC | spatie/laravel-permission | Laravel Gates/Policies | Gates work for simple cases. Database-driven RBAC better for multi-role blog with future growth. |
| Gravatar | forxer/laravel-gravatar | Manual URL building | Package handles sizing, defaults, caching. Manual works but reinvents wheel. Package is tiny dependency. |

## Installation

### Core Dependencies

```bash
# Backend packages
composer require laravel/scout
composer require algolia/algoliasearch-client-php
composer require livewire/livewire
composer require spatie/laravel-permission
composer require cviebrock/eloquent-sluggable
composer require forxer/laravel-gravatar
composer require spatie/laravel-sitemap
composer require spatie/laravel-feed

# DevOps & monitoring
composer require laravel/horizon
composer require spatie/laravel-backup
composer require --dev laravel/telescope

# Optional but recommended
composer require spatie/laravel-responsecache
composer require league/commonmark  # If using markdown storage
```

### Frontend Dependencies

```bash
# Flowbite for UI components (optional)
npm install flowbite
```

### Configuration Steps

1. **Publish configs:**
```bash
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --tag="eloquent-sluggable"
```

2. **Run migrations:**
```bash
php artisan migrate  # Creates roles/permissions tables
```

3. **Configure environment variables:**
```env
# Algolia credentials
ALGOLIA_APP_ID=your_app_id
ALGOLIA_SECRET=your_admin_key

# Scout configuration
SCOUT_DRIVER=algolia
SCOUT_QUEUE=true

# Redis for queues/cache
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue driver
QUEUE_CONNECTION=redis
```

4. **Queue worker:**
```bash
php artisan horizon  # For production
# OR for development:
php artisan queue:work redis --tries=3
```

## Version Compatibility Matrix

| Package | Min PHP | Min Laravel | Max Laravel | Notes |
|---------|---------|-------------|-------------|-------|
| Laravel 12 | 8.2 | - | - | No breaking changes from 11.x |
| Livewire | 8.1 | 10.0 | 12.0 | v4.0.3 latest (Jan 2026) |
| Scout | 8.1 | 10.0 | 12.0 | v10.23.0 latest (Dec 2025) |
| Algolia Client | 8.1 | - | - | NOT compatible with PHP 8.3.0 |
| spatie/laravel-permission | 8.0 | 8.12 | 12.0 | v6.24.0 (Dec 2025) |
| eloquent-sluggable | 8.2 | 12.0 | 12.0 | v12.0.0 (Feb 2025) |
| forxer/laravel-gravatar | 8.4 | 12.0 | 12.0 | v5.0.0 (Nov 2025) |
| spatie/laravel-sitemap | 8.2 | 11.0 | 12.0 | v7.3.8 (Nov 2025) |
| spatie/laravel-feed | 8.2 | 10.0 | 12.0 | v4.4.4 (Jan 2026) |
| spatie/laravel-backup | 8.3 | 12.40 | 12.0 | v9.3.7 (Nov 2025) |

**Recommended PHP version: 8.2** (broadest compatibility, especially with Algolia client which excludes 8.3.0)

**Note on PHP 8.4:** Some packages support it (Livewire, Gravatar, spatie/permission), but Algolia client explicitly excludes PHP 8.3.0 and may have issues with 8.4. Stick with PHP 8.2 for safety.

## Architecture Integration Points

### Scout + Algolia Setup

```php
// In Post model
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => strip_tags($this->content),
            'excerpt' => $this->excerpt,
            'slug' => $this->slug,
            'published_at' => $this->published_at->timestamp,
            'author' => $this->author->name,
            'categories' => $this->categories->pluck('name')->toArray(),
            'tags' => $this->tags->pluck('name')->toArray(),
        ];
    }

    public function shouldBeSearchable()
    {
        return $this->published_at !== null; // Only index published posts
    }
}
```

### Livewire Comment Component

```php
// app/Livewire/CommentForm.php
use Livewire\Component;

class CommentForm extends Component
{
    public $post;
    public $content;

    public function submit()
    {
        $this->validate(['content' => 'required|min:10|max:5000']);

        $comment = $this->post->comments()->create([
            'user_id' => auth()->id(),
            'content' => $this->content,
            'status' => auth()->user()->hasApprovedComment() ? 'approved' : 'pending',
        ]);

        $this->content = '';
        $this->dispatch('comment-added');
    }
}
```

### Role-Based Access Control

```php
// In database seeder
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create roles
$admin = Role::create(['name' => 'admin']);
$editor = Role::create(['name' => 'editor']);
$author = Role::create(['name' => 'author']);

// Create permissions
Permission::create(['name' => 'publish posts']);
Permission::create(['name' => 'edit any post']);
Permission::create(['name' => 'moderate comments']);

// Assign permissions to roles
$admin->givePermissionTo(Permission::all());
$editor->givePermissionTo(['publish posts', 'edit any post', 'moderate comments']);
$author->givePermissionTo(['publish posts']);
```

## Sources

### Official Documentation
- [Laravel 12.x Scout Documentation](https://laravel.com/docs/12.x/scout) - HIGH confidence
- [Livewire 4.x Documentation](https://livewire.laravel.com/) - HIGH confidence
- [Algolia Laravel Scout Integration](https://www.algolia.com/doc/framework-integration/laravel/tutorials/getting-started-with-laravel-scout-vuejs) - HIGH confidence
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission/v6/introduction) - HIGH confidence

### Package Repositories (Packagist - verified versions)
- [livewire/livewire v4.0.3](https://packagist.org/packages/livewire/livewire) - HIGH confidence
- [laravel/scout v10.23.0](https://packagist.org/packages/laravel/scout) - HIGH confidence
- [algolia/algoliasearch-client-php v4.37.3](https://packagist.org/packages/algolia/algoliasearch-client-php) - HIGH confidence
- [spatie/laravel-permission v6.24.0](https://packagist.org/packages/spatie/laravel-permission) - HIGH confidence
- [cviebrock/eloquent-sluggable v12.0.0](https://packagist.org/packages/cviebrock/eloquent-sluggable) - HIGH confidence
- [forxer/laravel-gravatar v5.0.0](https://packagist.org/packages/forxer/laravel-gravatar) - HIGH confidence
- [spatie/laravel-sitemap v7.3.8](https://packagist.org/packages/spatie/laravel-sitemap) - HIGH confidence
- [spatie/laravel-feed v4.4.4](https://packagist.org/packages/spatie/laravel-feed) - HIGH confidence
- [spatie/laravel-backup v9.3.7](https://packagist.org/packages/spatie/laravel-backup) - HIGH confidence

### Community Resources
- [Laravel Best Practices for 2026](https://smartlogiceg.com/en/post/laravel-best-practices-for-2026) - MEDIUM confidence
- [Building a Blog with Laravel, Livewire, and Laravel Breeze](https://neon.com/guides/laravel-livewire-blog) - MEDIUM confidence
- [Implementing Flowbite in Laravel 12](https://medium.com/@aakriticodes/implementing-flowbite-in-laravel-12-modern-ui-components-with-tailwind-css-36429ece1379) - MEDIUM confidence
- [Spatie Permissions vs Laravel Policies](https://dev.to/cyber_aurora_/spatie-permissions-vs-laravel-policies-and-gates-handling-role-based-access-1bdn) - MEDIUM confidence
- [WordPress to Laravel Migration Guide](https://freek.dev/906-on-migrating-my-blog-from-wordpress-to-a-laravel-application) - MEDIUM confidence

### Additional References
- [Livewire 4 Release Announcement](https://laravel-news.com/everything-new-in-livewire-4) - MEDIUM confidence
- [Laravel 12 Modern Patterns](https://medium.com/@codermanjeet/5-laravel-game-changing-features-every-artisan-must-master-in-2026-63aa262f3714) - LOW confidence (blog post)
- [Flowbite Laravel Integration](https://flowbite.com/docs/getting-started/laravel/) - HIGH confidence
