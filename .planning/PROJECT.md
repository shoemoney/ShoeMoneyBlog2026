# ShoeMoney Blog

## What This Is

A Laravel-powered blog platform migrating 20+ years of content from shoemoney.com away from WordPress. Built for speed, security, and the distinctive ShoeMoney brand — with Livewire for modern interactivity and Algolia for lightning-fast search.

## Core Value

Readers can find and read two decades of blog content quickly, and authors can publish new posts without fighting WordPress.

## Requirements

### Validated

- Laravel 12 project structure — existing
- Tailwind CSS 4 styling — existing
- Vite build pipeline — existing
- User model with authentication scaffolding — existing

### Active

- [ ] Migrate WordPress posts, categories, tags, and comments from SQL export
- [ ] Posts displayed with preserved URL structure (SEO continuity)
- [ ] Categories and tags for content organization
- [ ] Static pages (About, Contact, etc.)
- [ ] Comments with first-time moderation and Gravatar avatars
- [ ] Multiple author support with role-based access
- [ ] Custom admin panel for content management
- [ ] Livewire-powered frontend with ShoeMoney brand identity
- [ ] Algolia search across post titles and content

### Out of Scope

- Plugin/widget system — WordPress baggage, unnecessary complexity
- Post revisions/autosave — not needed for this workflow
- Pingbacks/trackbacks — obsolete, mostly spam vectors
- Complex media library — content is primarily text
- OAuth/social login — email/password sufficient
- Real-time notifications — not a social platform

## Context

**Migration source:** WordPress blog with ~20 years of posts exported to `shoemoney-blog-export.sql` (1.3GB). Reference WordPress code in `WordPress/` directory.

**Existing codebase:** Fresh Laravel 12 scaffolding. Codebase mapped in `.planning/codebase/`.

**Security motivation:** WordPress site repeatedly hacked. Laravel provides better security posture and the team already has Laravel expertise.

**Content structure (WordPress):**
- `wp_posts` — posts and pages
- `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` — categories and tags
- `wp_comments` — reader comments
- `wp_users`, `wp_usermeta` — authors

## Constraints

- **URL Preservation**: Existing post URLs must continue working — 20 years of SEO and inbound links
- **Tech Stack**: Laravel 12, Livewire, Tailwind CSS, Algolia (as discussed)
- **Database**: MySQL (matches existing WordPress data)
- **Data Migration**: Must handle large SQL import (1.3GB) without timeouts

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Livewire over Inertia/Vue | Simpler mental model, stays in PHP, good for content sites | — Pending |
| Algolia over Meilisearch | User preference, proven at scale, excellent Laravel integration | — Pending |
| Custom admin over Filament | Full control over UX, avoid package dependencies | — Pending |
| Preserve WordPress URLs | Protect 20 years of SEO value | — Pending |

---
*Last updated: 2026-01-24 after initialization*
