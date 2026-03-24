# ShoemoneyVelle

> A modern Laravel blog platform for migrating WordPress content to a clean, fast, secure stack.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?logo=livewire)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-06B6D4?logo=tailwindcss)](https://tailwindcss.com)
[![Algolia](https://img.shields.io/badge/Algolia-Search-003DFF?logo=algolia)](https://algolia.com)

---

## Why This Exists

If you've run a WordPress blog for years and hit a wall with security breaches, plugin conflicts, slow performance, or expensive hosting, this project is for you. ShoemoneyVelle is a complete WordPress-to-Laravel migration template that preserves your content, SEO structure, and URL patterns while giving you a modern, maintainable codebase.

It was built to migrate **shoemoney.com** (20+ years, ~2,500 posts), but everything is designed to be reusable for any WordPress blog migration.

---

## What You Get

**Content management:** Full admin panel with WYSIWYG editor, draft/published workflow, categories, tags, reading time calculation, and SEO meta tags. Pages and posts share a unified `posts` table (differentiated by `post_type`), which simplifies queries and relationships.

**Customizable hero section:** The homepage hero is fully admin-editable — photo, name, title, bio blurb, "As Seen In" press mentions, and project/social links with icons (GitHub, Twitter, YouTube, LinkedIn, RSS, and more). All configured from Settings, no code changes needed.

**Navigation management:** Drag-and-drop menu builder in admin. Supports custom URLs, pages, and categories as link types. URL fields include search-as-you-type autocomplete that finds your posts and pages.

**Sidebar widgets:** Manually curated "Popular Posts" list, a resources section, and a custom links section — all admin-editable with autocomplete URL fields.

**Search:** Algolia-powered instant search with typeahead, keyboard navigation, and typo-tolerant matching. Real-time indexing on publish.

**AI featured images:** Built-in pipeline using Google Gemini models (via OpenRouter) to generate featured images for posts that don't have them. Generates multiple size variants and uploads to Cloudflare R2.

**Dark mode:** System-preference-aware with manual toggle. No flash of unstyled content.

**Performance:** HTTP response caching via `spatie/laravel-responsecache`, automatic cache invalidation on content changes, optimized database indexes, and compiled Blade templates. Cached pages serve in under 50ms.

**Backups:** Automated daily backups to S3 via `spatie/laravel-backup` with configurable retention policies.

---

## Tech Stack

Laravel 12, Livewire 4, Tailwind CSS 4, Alpine.js, Algolia Scout, Cloudflare R2, Intervention Image, and OpenRouter for AI image generation.

---

## Database Architecture

Posts and pages live in a single `posts` table, differentiated by a `post_type` column. The `Page` model extends `Post` with a global scope that filters to `post_type = 'page'`. This keeps relationships, queries, and admin tools unified.

```
┌─────────────┐     ┌──────────────┐
│    users    │     │    posts     │
├─────────────┤     ├──────────────┤
│ id          │◄────│ user_id      │
│ name        │     │ title        │
│ email       │     │ slug         │
│ password    │     │ content      │
│ is_admin    │     │ excerpt      │
│ role        │     │ post_type    │  ← 'post' or 'page'
│ created_at  │     │ status       │
└─────────────┘     │ published_at │
                    │ reading_time │
                    └──────┬───────┘
                           │
          ┌────────────────┼───────────────────┐
          ▼                ▼                   ▼
   ┌────────────┐  ┌──────────────────┐  ┌──────────┐
   │categorizables│  │ featured_images │  │ taggables │
   ├────────────┤  ├──────────────────┤  ├──────────┤
   │category_id │  │ imageable_id     │  │ tag_id   │
   │*_type      │  │ imageable_type   │  │ *_type   │
   │*_id        │  │ raw_url          │  │ *_id     │
   └─────┬──────┘  │ small/med/lg_url │  └────┬─────┘
         ▼         │ prompt_used      │       ▼
   ┌──────────┐   │ status           │  ┌──────┐
   │categories│   └──────────────────┘  │ tags │
   ├──────────┤                          ├──────┤
   │ name     │                          │ name │
   │ slug     │                          │ slug │
   └──────────┘                          └──────┘

┌──────────────┐     ┌──────────┐
│  menu_items  │     │ settings │
├──────────────┤     ├──────────┤
│ label        │     │ key      │  ← key/value store for all
│ url          │     │ value    │    site configuration
│ type         │     │ type     │
│ linkable_id  │     │ group    │
│ linkable_type│     └──────────┘
│ position     │
│ is_active    │
└──────────────┘
```

Key design decisions: polymorphic relationships for categories and tags (easily extensible to other content types), a key/value `settings` table for all site configuration (hero, sidebar, footer, social links), and polymorphic featured images with multiple size variants.

---

## Installation

### Requirements

PHP 8.2+, Composer 2.x, Node.js 18+ with npm, MySQL 8.0+, and optionally Redis for caching.

### Setup

```bash
# Clone and install
git clone https://github.com/shoemoney/shoemoneyvelle.git
cd shoemoneyvelle
composer install
npm install

# Configure
cp .env.example .env
php artisan key:generate
# Edit .env with your database, Algolia, and other credentials

# Database
php artisan migrate
php artisan db:seed

# Frontend assets
npm run build

# Search index
php artisan scout:import "App\Models\Post"

# Start
php artisan serve
```

### WordPress Migration

The seeder system is designed to import from a WordPress SQL export. The migration handles: posts and pages (preserving slugs and publish dates), categories and tags with their relationships, users with role mapping, and shortcode conversion to clean HTML. Unknown shortcodes are stripped rather than rendering ugly brackets.

What gets left behind from WordPress: `wp_options` (serialized plugin settings), `wp_postmeta` (key-value chaos from plugins), `wp_usermeta`, `wp_termmeta`, `wp_commentmeta`, and `wp_links` (deprecated since 2012).

---

## Configuration

### Environment Variables

The `.env.example` file documents all required and optional variables. The key ones:

**Database:** Standard Laravel `DB_*` variables for MySQL.

**Algolia Search:** `ALGOLIA_APP_ID`, `ALGOLIA_SECRET` (admin key for indexing), `ALGOLIA_SEARCH` (search-only key for frontend). Get these from [algolia.com](https://www.algolia.com) under Settings > API Keys.

**AI Image Generation:** `OPENROUTER_API_KEY` from [openrouter.ai](https://openrouter.ai), plus `GENERAL_MODEL` and `IMAGE_MODEL` for the Gemini models used for prompt generation and image creation.

**Backups:** AWS S3 credentials (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BACKUP_BUCKET`) for automated backup storage.

---

## Admin Panel

### Site Settings

All site-wide configuration lives in **Settings > Site Settings**:

**General:** Site name, tagline, logo URL, meta description, posts per page.

**Hero Section:** Photo URL, display name, title/role, bio blurb. Plus two dynamic lists — "As Seen In" press mentions (name + URL pairs displayed as dot-separated links) and project/social links (icon + label + URL rendered as pill-shaped buttons with SVG icons). Supported icons: github, twitter, youtube, linkedin, facebook, instagram, tiktok, mastodon, discord, twitch, reddit, website, blog, email, rss, and a generic link icon.

**Social Links:** Twitter, Facebook, YouTube, LinkedIn profile URLs.

**Sidebar Widgets:** Toggle and configure three widget types — Popular Posts (manually curated list with autocomplete search), Resources (name + URL + description), and a custom links section. All URL fields support search-as-you-type that finds published posts and pages.

**Footer:** Custom footer text and configurable footer links (label + URL + new-tab toggle), also with autocomplete.

**Code Injection:** Analytics code, custom header code, and custom footer code fields for adding tracking scripts or custom markup.

### Navigation Manager

Build your site navigation from **Settings > Navigation**. Three link types: custom URL (with autocomplete search for posts/pages), page link, and category link. Drag items to reorder, toggle active/inactive state.

### Content Management

Standard CRUD for posts and pages, category and tag management, user administration, and a dashboard with content statistics.

---

## AI Featured Images

For posts missing featured images, the built-in pipeline uses Google Gemini to generate them:

1. Gemini Flash reads the post title + content and writes a detailed image prompt
2. Gemini Pro Image generates the image using that prompt
3. Intervention Image creates 4 size variants (400/600/800/1200px wide)
4. Variants upload to Cloudflare R2

```bash
# Preview what would be generated
php artisan images:generate --limit=5 --dry-run

# Generate for a single post (synchronous)
php artisan images:generate --limit=1 --type=posts --sync

# Batch generate via queue
php artisan images:generate --limit=50 --type=posts

# Retry failures
php artisan images:generate --failed-only --sync

# Force regenerate existing
php artisan images:generate --limit=10 --force --sync
```

---

## Deployment

### Production Checklist

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan migrate --force
php artisan scout:import "App\Models\Post"
npm run build
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Cron Jobs

```bash
# Laravel scheduler (runs backups + cleanup)
* * * * * cd /path/to/shoemoneyvelle && php artisan schedule:run >> /dev/null 2>&1

# Queue worker
* * * * * cd /path/to/shoemoneyvelle && php artisan queue:work --memory=512
```

### Security Headers (nginx)

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## Security

Built-in protections: Eloquent ORM with prepared statements (SQL injection), Blade auto-escaping (XSS), CSRF token verification on all state-changing requests, bcrypt password hashing, admin middleware (`EnsureUserIsAdmin`), honeypot spam protection via `spatie/laravel-honeypot`, and rate limiting.

All `/admin/*` routes require authentication and admin status.

---

## Performance

Response caching via `spatie/laravel-responsecache` serves cached pages in under 50ms. Cache is automatically invalidated when posts are created, updated, or deleted. Database queries are optimized with compound indexes on `(status, published_at)` and reverse lookup indexes on polymorphic pivot tables.

---

## License

MIT License — see [LICENSE](LICENSE) for details.

---

## Credits

Built with: [Laravel](https://laravel.com), [Livewire](https://livewire.laravel.com), [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev), [Algolia](https://www.algolia.com), [spatie/laravel-responsecache](https://github.com/spatie/laravel-responsecache), [spatie/laravel-backup](https://github.com/spatie/laravel-backup), [Intervention Image](https://image.intervention.io), and [OpenRouter](https://openrouter.ai).
