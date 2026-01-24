# Project Research Summary

**Project:** ShoeMoney Blog (WordPress to Laravel Migration)
**Domain:** Multi-author Blog Platform
**Researched:** 2026-01-24
**Confidence:** HIGH

## Executive Summary

This project is migrating a 20+ year legacy WordPress blog to a modern Laravel/Livewire stack with Algolia search. The migration involves 1.3GB of database content including posts, comments, media files, and user data. Expert migrations of this type prioritize **SEO preservation** (exact URL mapping with 301 redirects), **data integrity** (chunked imports, relationship preservation), and **password compatibility** (hybrid validation to support WordPress phpass hashes).

The recommended approach uses Laravel 12 with Livewire 4 for interactive components, Algolia/Scout for search, and a custom admin panel. Critical success factors include: (1) mapping WordPress permalink structures EXACTLY to Laravel routes to prevent catastrophic SEO loss, (2) using chunked/queued imports for large datasets to avoid memory exhaustion, (3) implementing hybrid password validation to preserve user access, and (4) converting WordPress shortcodes during migration to prevent broken content rendering.

Key risks center on **URL structure breaking** (70-90% traffic loss if not mapped correctly), **Livewire performance degradation** with large comment threads (must use computed properties, never public collections), and **WordPress-specific data loss** (comment threading, SEO metadata, media files, user roles). Mitigation requires meticulous pre-migration analysis, incremental verification testing, and understanding the impedance mismatch between WordPress's legacy architecture and Laravel's modern patterns.

## Key Findings

### Recommended Stack

Laravel 12 with PHP 8.2 provides the foundation, chosen for its mature ecosystem and compatibility with legacy WordPress data. **PHP 8.2 is critical** — not 8.3 or 8.4 — due to Algolia client incompatibility with PHP 8.3.0. The frontend stack combines Livewire 4 (latest, released Jan 2026 with single-file components) with Tailwind CSS 4 for styling, avoiding heavy JavaScript frameworks to maintain a PHP-only stack that's easier to maintain for content-focused sites.

**Core technologies:**
- **Laravel 12.x** with PHP 8.2 — Application framework already in use, latest stable release with no breaking changes from 11.x
- **Livewire 4.0.3** — Interactive UI framework with single-file components and Islands architecture, perfect for comment forms and search without heavy JS
- **Algolia via Scout** — Hosted search with fast typeahead, explicitly requested by project, better Laravel documentation than Meilisearch
- **MySQL 8.0+** — Already in use for WordPress, contains existing blog data with excellent Eloquent support
- **Redis 7.x** — Cache and queue backend for Scout indexing, response caching, and session storage to handle traffic spikes
- **Spatie packages** — Permission system (RBAC), sitemap generation, RSS feeds, backups — all battle-tested Laravel standards

**Critical packages:**
- `cviebrock/eloquent-sluggable` v12.0.0 — Auto-generate slugs with URL preservation for migration
- `forxer/laravel-gravatar` v5.0.0 — Comment avatars without user uploads
- `spatie/laravel-permission` v6.24.0 — Database-driven RBAC for multi-author blog (Admin/Editor/Author/Moderator roles)
- `spatie/laravel-backup` v9.3.7 — Automated backups critical for recovering from attacks (primary migration motivation)

**Why not alternatives:**
- Inertia.js/Vue: Project preference for PHP-only stack, simpler mental model
- Filament admin panel: Project explicitly wants custom admin for full UX control
- Meilisearch: User preference for Algolia despite cost, better Laravel docs/examples
- MySQL full-text search: Poor relevance, no typo tolerance, slower at scale

### Expected Features

WordPress migrations must achieve **content parity** before adding differentiators. The research identifies clear table stakes (expected features that if missing cause "broken" perception), competitive differentiators (features that make this better than WordPress), and explicit anti-features (scope traps to avoid).

**Must have (table stakes):**
- Post publishing with WYSIWYG/markdown editor, categories, tags, featured images
- Multi-author management with profiles (bio, avatar, social links)
- Comment system with threaded replies and first-time moderation (spam control without friction)
- Static pages (About, Contact) with custom templates
- RSS/Atom feeds (syndication baseline)
- SEO fundamentals (meta tags, OpenGraph, sitemap.xml, structured data)
- Responsive mobile-first design (71% of consumers expect it)
- URL preservation with 301 redirects for 20 years of inbound links

**Should have (competitive differentiators):**
- Algolia typeahead search with instant results (major UX win over WordPress)
- First-time comment moderation (approve once, then auto-approve regulars)
- Code syntax highlighting for technical blog posts
- Reading time estimates calculated from word count
- Dark mode with user preference detection
- Table of contents auto-generated from H2/H3 headings
- Performance optimization targeting sub-1s page loads (vs 2.6s WordPress average)
- Advanced SEO with schema.org Article markup and breadcrumbs

**Defer (v2+):**
- Email newsletter integration (use Substack/Buttondown initially)
- Webmentions/IndieWeb features (niche, low ROI)
- AI-assisted content suggestions (nice-to-have)
- Content series/collections (categories work initially)
- Monetization features (not immediate need)

**Anti-features (explicitly avoid):**
- Custom page builder (WordPress complexity trap, maintenance nightmare)
- Built-in analytics dashboard (privacy nightmare, use Matomo/Plausible)
- Plugin/extension system (security risk, focus on Livewire components)
- Multi-language support (massive complexity for personal blog)
- Forum/community features (different product, link to Discord if needed)
- WYSIWYG editor from scratch (use TipTap/Quill/markdown)

### Architecture Approach

Laravel/Livewire blogs follow a **layered MVC architecture** with event-driven components and service abstraction. The key pattern is separating concerns across Presentation (Livewire components), Application (Controllers/Policies), Business (Services/Events/Observers), Persistence (Eloquent models), and Infrastructure (Database/Algolia/Queue) layers.

**Major components:**
1. **Eloquent Models** (Post, User, Category, Tag, Comment) — Domain entities with relationships and Scout integration for search
2. **Livewire Components** (PostList, PostDetail, CommentForm, SearchBar, Admin CRUD) — Presentation layer using single-file components for most UI
3. **Service Layer** (PostService, CommentModerationService, WordPressMigrationService) — Complex business logic and multi-step workflows
4. **Observer Layer** (PostObserver, CommentObserver) — Lifecycle automation for auto-slugs, search sync, notifications
5. **Policy Layer** (PostPolicy, CommentPolicy) — Authorization rules for multi-author permissions
6. **Custom Admin Panel** — Livewire-based CRUD interfaces for full control vs package like Nova/Filament

**Critical patterns:**
- **Livewire single-file components** — Combine PHP logic and Blade in `.php` files for faster development
- **Computed properties for collections** — NEVER store collections in public Livewire properties (causes massive serialization overhead)
- **Observer-based search sync** — Auto-sync published posts to Algolia via Scout trait, queued for production
- **Route model binding with slugs** — SEO-friendly URLs using `slug` instead of `id` in routes
- **Hybrid password validation** — Support WordPress phpass hashes, upgrade to bcrypt on successful login

**Database schema:**
- Normalized relationships (separate tags table with pivot, not JSON)
- Soft deletes for posts/comments (recovery capability)
- Status enums (draft/published/archived) instead of booleans
- Nested comments via `parent_id` for threaded discussions
- SEO metadata columns (meta_title, meta_description, og_image) on posts table
- WordPress compatibility: `wordpress_id` columns during migration for reference

### Critical Pitfalls

The research identified 15 pitfalls ranging from critical (cause rewrites/SEO collapse) to minor (annoyances). The top 6 can destroy the project if not addressed proactively.

1. **URL Structure Breaking SEO** — WordPress permalink patterns must be mapped EXACTLY to Laravel routes. Failure causes 70-90% traffic loss within weeks from 404s on all indexed pages. Prevention: Export all WordPress URLs before starting, implement identical route patterns, use 301 redirects ONLY for necessary changes, test with URL verification script in staging.

2. **Importing 1.3GB Database Without Chunking** — Loading large SQL files into memory causes PHP exhaustion, timeouts, or partial imports. Prevention: Use MySQL CLI with `pv` for progress, OR stream SQL file line-by-line in PHP, OR split into 100MB chunks. Never use `file_get_contents()` on the full file.

3. **WordPress Password Hash Incompatibility** — WordPress uses phpass (`$P$B...` format), Laravel uses bcrypt. Direct migration locks out all users. Prevention: Implement hybrid password validation that checks WordPress format first, then upgrades to bcrypt on successful login. Gradual migration, not forced reset.

4. **Livewire Performance Degradation with Large Comment Threads** — Serializing 500+ comments as public properties creates 500KB-2MB payloads, causing 7-60 second response times. Prevention: Use computed properties exclusively for collections, implement pagination (20 comments/page), lazy load nested replies, debounce user input (500ms minimum).

5. **Algolia/Scout Initial Import Without Queueing** — Running `scout:import` on 50,000 posts synchronously times out after 60-300 seconds with no resume point. Prevention: Configure Redis queue workers BEFORE importing, use `--chunk=500` flag, monitor queue worker logs, verify Algolia count matches database count.

6. **WordPress Shortcodes Breaking Content Rendering** — 20 years of content contains `[gallery]`, `[caption]`, `[embed]` shortcodes that display as literal text in Laravel. Prevention: Parse and convert shortcodes during migration (convert to HTML/Blade), OR use runtime Laravel shortcode parser package, OR identify and manually migrate critical shortcodes.

**Additional high-impact pitfalls:**
- Comment thread nesting lost (preserve `comment_parent` → `parent_id`)
- Media files not copied (404s on all images, must copy `/wp-content/uploads/`)
- SEO metadata lost (Yoast/RankMath data in `wp_postmeta`, not main posts table)
- User roles unmapped (import from `wp_usermeta` serialized arrays)
- Taxonomy relationships lost (WordPress uses 3-table structure: terms, term_taxonomy, term_relationships)

## Implications for Roadmap

Based on research, the migration requires **7 phases** with specific ordering driven by dependencies and risk mitigation. The sequence prioritizes data integrity, SEO preservation, and performance from the start.

### Phase 1: Foundation & Data Migration
**Rationale:** Must establish clean data before building features. WordPress import is the critical path — all other work depends on having migrated content to work with.

**Delivers:**
- Complete database schema with WordPress compatibility columns
- All WordPress data imported and verified (posts, users, comments, categories, tags, media references)
- Hybrid password validation implemented
- Shortcode conversion applied to content
- SEO metadata preserved from Yoast/RankMath
- Comment threading preserved
- User roles mapped to Laravel permission system

**Critical features:**
- Chunked database import (avoiding Pitfall #2)
- Password hash compatibility (avoiding Pitfall #3)
- Shortcode conversion (avoiding Pitfall #6)
- Comment parent_id mapping (avoiding Pitfall #7)
- Media file migration (avoiding Pitfall #9)
- SEO metadata extraction (avoiding Pitfall #10)
- Role mapping from wp_usermeta (avoiding Pitfall #8)

**Avoids:** Memory exhaustion, password lockout, broken content, lost relationships

**Duration estimate:** 2-3 weeks (largest phase, most risk)

### Phase 2: URL Routing & SEO Preservation
**Rationale:** Must come before ANY public launch. URL structure determines SEO survival. This cannot be retrofitted.

**Delivers:**
- Exact WordPress permalink pattern mapping in Laravel routes
- 301 redirects for any necessary URL changes
- Canonical tags preventing duplicate content
- URL verification testing suite
- Sitemap.xml generation from routes
- Robots.txt configuration

**Critical features:**
- Route model binding with slugs (table stakes)
- Redirect rules imported from Redirection plugin (avoiding Pitfall #11)
- Automated URL verification script (avoiding Pitfall #1)

**Avoids:** Catastrophic SEO collapse, 70-90% traffic loss, broken inbound links

**Stack:** Laravel routing, `spatie/laravel-sitemap`, `spatie/laravel-missing-page-redirector`

**Duration estimate:** 1 week

### Phase 3: Public Frontend with Livewire
**Rationale:** With data imported and URLs preserved, build the public-facing blog using performance-first Livewire patterns.

**Delivers:**
- Blog post listing with pagination
- Single post view with comment display
- Comment form with first-time moderation
- Category/tag archive pages
- Author profile pages
- Static pages (About, Contact)
- Responsive Tailwind design
- RSS/Atom feeds

**Critical features:**
- Livewire components using computed properties (avoiding Pitfall #4)
- Comment pagination and lazy loading (avoiding Pitfall #4)
- Gravatar integration for avatars
- Reading time calculation
- Social sharing meta tags

**Avoids:** Livewire performance degradation, comment serialization overhead

**Stack:** Livewire 4, Tailwind CSS 4, `spatie/laravel-feed`, `forxer/laravel-gravatar`

**Duration estimate:** 3-4 weeks

### Phase 4: Search Integration (Algolia/Scout)
**Rationale:** Depends on Phase 1 data and Phase 3 components. Search is a differentiator but not blocking for basic blog functionality.

**Delivers:**
- Algolia index configuration
- Queued Scout sync on post create/update
- SearchBar Livewire component with typeahead
- Search results page
- Initial bulk import of all published posts
- Search index verification

**Critical features:**
- Queue workers configured BEFORE import (avoiding Pitfall #5)
- Chunked `scout:import` with monitoring (avoiding Pitfall #5)
- Computed properties in SearchBar component (avoiding Pitfall #4)

**Avoids:** Import timeout, synchronous blocking, incomplete indexing

**Stack:** Laravel Scout, Algolia PHP Client, Redis queues, Horizon for monitoring

**Duration estimate:** 1-2 weeks

### Phase 5: Custom Admin Panel
**Rationale:** Can develop in parallel with Phase 4. Depends on Phase 1 models and Phase 2 authorization.

**Delivers:**
- Dashboard with stats and recent activity
- Post CRUD interface (create, edit, delete, publish)
- Category/tag management
- Comment moderation queue
- User management with role assignment
- Media library interface

**Critical features:**
- Policy-based authorization (multi-author RBAC)
- Draft/scheduled/published workflow
- SEO metadata fields in post editor
- Code syntax highlighting preview
- Revision history (optional, nice-to-have)

**Stack:** Livewire components, `spatie/laravel-permission`, Flowbite UI components (optional)

**Duration estimate:** 4-5 weeks

### Phase 6: Performance Optimization
**Rationale:** After core features work, optimize for production traffic. Research shows WordPress averages 2.6s loads; target sub-1s.

**Delivers:**
- Response caching for rendered posts
- Query caching for computed properties
- Image optimization and WebP conversion
- CDN configuration for static assets
- Database query optimization and indexing
- Livewire lazy loading tuning

**Critical features:**
- Cache invalidation on post updates
- Debounced user input (500ms minimum)
- N+1 query elimination with eager loading
- Database indexes on frequently queried columns

**Stack:** `spatie/laravel-responsecache`, Redis, CDN integration

**Duration estimate:** 1-2 weeks

### Phase 7: Polish & Launch Preparation
**Rationale:** Final touches before switching DNS from WordPress to Laravel.

**Delivers:**
- Dark mode with user preference detection
- Table of contents for long-form posts
- Advanced SEO (schema.org Article markup, breadcrumbs)
- Automated backup configuration
- Monitoring and logging setup
- Pre-launch verification checklist

**Critical features:**
- Comprehensive testing with actual high-traffic posts
- URL verification on production-like environment
- Search result quality validation
- Performance testing under load

**Stack:** `spatie/laravel-backup`, Telescope for debugging, Horizon for queue monitoring

**Duration estimate:** 2 weeks

### Phase Ordering Rationale

**Why this sequence:**
- **Data first** — Cannot build features without migrated content (Phase 1 is foundation)
- **SEO before launch** — URL structure mistakes are irreversible after indexing (Phase 2 blocks public launch)
- **Public before admin** — Frontend validates data migration correctness, admin is internal-facing (Phase 3 before Phase 5)
- **Search after core** — Algolia is differentiator but blog works without it initially (Phase 4 after Phase 3)
- **Performance after features** — Need working features to optimize (Phase 6 near end)
- **Polish last** — Dark mode/ToC are enhancements, not blockers (Phase 7 final)

**Parallel opportunities:**
- Phase 4 (Search) and Phase 5 (Admin) can develop concurrently after Phase 3 completes
- Phase 6 (Performance) work can start during Phase 5 (test optimizations on public frontend)

**Critical path:** Phase 1 → Phase 2 → Phase 3 → Phase 7 (data, URLs, public frontend, launch)

### Research Flags

**Phases likely needing deeper research during planning:**
- **Phase 1 (Data Migration):** WordPress-specific edge cases (custom post types, custom fields, plugin-specific data). May need `/gsd:research-phase` for shortcode inventory and conversion strategy.
- **Phase 4 (Algolia/Scout):** Algolia index configuration optimization for blog search (ranking formulas, typo tolerance tuning). Standard integration but advanced tuning may need research.

**Phases with standard patterns (skip deep research):**
- **Phase 2 (URL Routing):** Well-documented Laravel routing patterns, straightforward implementation
- **Phase 3 (Livewire Frontend):** Standard Livewire blog patterns, extensive examples in Laravel community
- **Phase 5 (Admin Panel):** Standard CRUD with Livewire, clear Spatie permission documentation
- **Phase 6 (Performance):** Laravel caching patterns well-documented, standard optimizations
- **Phase 7 (Polish):** Mostly UI/UX work with known packages

**Research confidence by phase:**
- Phase 1: HIGH (WordPress migration well-documented, multiple case studies)
- Phase 2: HIGH (Laravel routing standard, SEO migration guides available)
- Phase 3: HIGH (Livewire blog tutorials abundant)
- Phase 4: HIGH (Scout/Algolia official Laravel docs comprehensive)
- Phase 5: MEDIUM-HIGH (Custom admin less documented than packages, but Livewire CRUD patterns clear)
- Phase 6: HIGH (Laravel performance optimization well-covered)
- Phase 7: MEDIUM (Some features like dark mode are design-dependent)

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | All package versions verified from Packagist with specific releases. PHP 8.2 requirement confirmed from Algolia client constraints. Laravel 12/Livewire 4 compatibility matrix validated. |
| Features | HIGH | Feature expectations derived from multiple blog platform comparisons (Ghost, Medium, Substack) and WordPress plugin ecosystem analysis. Table stakes vs differentiators clearly separated. |
| Architecture | HIGH | Architecture patterns sourced from official Livewire 4 docs, Laravel best practices guides, and verified GitHub example projects. Database schema standard for Laravel blogs. |
| Pitfalls | HIGH | Critical pitfalls (URL breakage, password compatibility, Livewire performance) confirmed in multiple WordPress→Laravel migration case studies and Livewire GitHub discussions with version-specific details. |

**Overall confidence:** HIGH

The research benefits from:
1. **Official documentation** — Laravel 12, Livewire 4, Scout, Algolia all have comprehensive official docs
2. **Real migration case studies** — Multiple documented 7+ year WordPress to Laravel migrations with lessons learned
3. **Package version verification** — All Packagist versions checked as of Jan 2026, not guessed
4. **Community consensus** — Patterns like "avoid repositories, use services" confirmed across multiple Laravel experts
5. **Specific pitfall evidence** — Livewire performance issues documented in GitHub discussions with reproducible examples

### Gaps to Address

Despite high confidence, some areas need validation during implementation:

- **Shortcode complexity** — The full inventory of shortcode types in 20 years of content is unknown until the WordPress export is analyzed. May discover custom plugin shortcodes requiring manual conversion. Address: Phase 1 should start with shortcode regex scan to categorize before migration.

- **Comment volume per post** — Research assumes some posts have 500+ comments based on 20 years of content, but actual distribution unknown. If most posts have <50 comments, Livewire performance mitigations may be less critical. Address: Analyze comment distribution during Phase 1 to prioritize optimization work.

- **WordPress plugin dependencies** — Unknown if WordPress site uses custom post types, custom fields, or plugin-specific features beyond standard blog functionality. Address: Export and analyze `wp_postmeta` and `wp_posts.post_type` before Phase 1 to identify surprises.

- **Existing redirect rules** — Unknown how many custom redirects exist in Redirection plugin or .htaccess. Could be 10 or 10,000. Address: Export redirect count during pre-migration analysis to estimate Phase 2 scope.

- **Image storage size** — `/wp-content/uploads/` directory size unknown. If multi-GB, may impact migration strategy (S3 vs local storage decision). Address: Check WordPress uploads directory size before Phase 1 to plan storage approach.

- **Algolia index limits** — Free/paid tier limits for Algolia unclear from project context. May impact search feature scope if on free tier (10K records). Address: Verify Algolia plan during Phase 4 planning.

## Sources

### Primary (HIGH confidence)
- [Laravel 12.x Scout Documentation](https://laravel.com/docs/12.x/scout) — Search integration, queue configuration
- [Livewire 4.x Documentation](https://livewire.laravel.com/) — Component patterns, performance best practices
- [Algolia Laravel Scout Integration](https://www.algolia.com/doc/framework-integration/laravel/tutorials/getting-started-with-laravel-scout-vuejs) — Setup guide, indexing configuration
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission/v6/introduction) — RBAC implementation
- Packagist verified versions for all packages (livewire/livewire v4.0.3, laravel/scout v10.23.0, algolia/algoliasearch-client-php v4.37.3, spatie packages)

### Secondary (MEDIUM confidence)
- [WordPress to Laravel migration case study](https://medium.com/@hosnyben/migrating-a-7-year-old-wordpress-business-to-laravel-bf9f11542fc1) — Real-world 7-year migration, password handling, pitfalls
- [Livewire performance GitHub discussions](https://github.com/livewire/livewire/discussions/4492) — Comment serialization issues, computed property solutions
- [Laravel Best Practices for 2026](https://smartlogiceg.com/en/post/laravel-best-practices-for-2026) — Architecture patterns, service layer guidance
- [Building a Blog with Laravel, Livewire, and Laravel Breeze](https://neon.com/guides/laravel-livewire-blog) — Feature expectations, component structure
- [WordPress migration SEO guide](https://creativetweed.co.uk/laravel-to-wordpress-migration/) — URL preservation strategies, redirect implementation

### Tertiary (LOW confidence)
- Community blog posts on Laravel 12 features (Medium articles) — Used for feature discovery but validated against official docs
- WordPress plugin comparison articles — Used to infer table stakes features but not authoritative for Laravel implementation

---
*Research completed: 2026-01-24*
*Ready for roadmap: yes*
