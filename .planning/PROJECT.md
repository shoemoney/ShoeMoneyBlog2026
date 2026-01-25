# ShoeMoney Blog

## What This Is

A Laravel-powered blog platform that successfully migrated 20+ years of content from shoemoney.com away from WordPress. Built for speed, security, and the distinctive ShoeMoney brand — with Livewire for modern interactivity and Algolia for lightning-fast search.

## Core Value

Readers can find and read two decades of blog content quickly, and authors can publish new posts without fighting WordPress.

## Current State (v1.0 Shipped)

**Tech Stack:**
- Laravel 12 + Livewire 4
- Tailwind CSS 4 + Alpine.js
- Algolia search (Scout Extended)
- MySQL 8.0
- spatie/laravel-responsecache
- spatie/laravel-backup

**Codebase:**
- 9,693 lines of PHP/Blade/CSS/JS
- 42 plans executed across 7 phases
- 131 commits

**Content:**
- 2,500+ posts migrated
- 160,000+ comments preserved
- ~15,000 tags, ~50 categories
- All original WordPress URLs working

## Requirements

### Validated

- ✓ Laravel 12 project structure — v1.0
- ✓ Tailwind CSS 4 styling — v1.0
- ✓ Vite build pipeline — v1.0
- ✓ User model with authentication — v1.0
- ✓ WordPress posts, categories, tags, comments migrated — v1.0
- ✓ Posts with preserved URL structure (SEO continuity) — v1.0
- ✓ Category and tag archive pages — v1.0
- ✓ Static pages (About, Contact) — v1.0
- ✓ Comments with moderation and Gravatar — v1.0
- ✓ Multiple author support with admin roles — v1.0
- ✓ Custom admin panel for content management — v1.0
- ✓ Livewire frontend with ShoeMoney branding — v1.0
- ✓ Algolia typeahead search — v1.0
- ✓ Response caching with auto-invalidation — v1.0
- ✓ Dark mode toggle — v1.0
- ✓ Automated daily backups — v1.0

### Active (v1.1 Candidates)

- [ ] Post scheduling (publish at future date)
- [ ] Email notifications for comment replies
- [ ] Search result highlighting
- [ ] Mobile app (PWA)

### Out of Scope

- Plugin/widget system — WordPress baggage, unnecessary complexity
- Post revisions/autosave — not needed for this workflow
- Pingbacks/trackbacks — obsolete, mostly spam vectors
- Complex media library — content is primarily text
- OAuth/social login — email/password sufficient
- Real-time notifications — not a social platform
- Comment threading beyond 3 levels — diminishing returns

## Context

**Migration source:** WordPress blog exported to `shoemoney-blog-export.sql` (1.3GB). Migration complete — source files preserved for reference in `WordPress/` directory.

**Security motivation:** WordPress site repeatedly hacked. Laravel provides enterprise-grade security with built-in CSRF, XSS, and SQL injection protection.

**Performance:** Response caching delivers <50ms cached page loads. Database indexes optimize homepage and archive queries.

## Constraints

- **URL Preservation**: All WordPress URLs continue working (verified via automated URL checker)
- **Tech Stack**: Laravel 12, Livewire 4, Tailwind CSS 4, Algolia
- **Database**: MySQL 8.0 with optimized indexes
- **Hosting**: Standard PHP hosting with cron support for backups

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Livewire over Inertia/Vue | Simpler mental model, stays in PHP, good for content sites | ✓ Good |
| Algolia over Meilisearch | User preference, proven at scale, excellent Laravel integration | ✓ Good |
| Custom admin over Filament | Full control over UX, avoid package dependencies | ✓ Good |
| Preserve WordPress URLs | Protect 20 years of SEO value | ✓ Good |
| is_admin boolean over RBAC | Simplicity, only need admin/non-admin distinction | ✓ Good |
| Polymorphic tags/categories | Flexibility for future content types | ✓ Good |
| Response cache with trait | Automatic invalidation on model changes | ✓ Good |
| Class-based dark mode | User override of system preference | ✓ Good |
| S3 backups with local fallback | External storage for disaster recovery | ✓ Good |

---
*Last updated: 2026-01-25 after v1.0 milestone*
