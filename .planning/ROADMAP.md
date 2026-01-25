# Roadmap: ShoeMoney Blog

## Overview

This roadmap migrates 20+ years of WordPress content to a modern Laravel 12 platform with Livewire interactivity and Algolia search. The journey starts with data migration to establish a clean foundation, then builds URL preservation (critical for SEO), public-facing content display, commenting system, search integration, custom admin panel, and final performance optimization. Each phase delivers a coherent capability that can be verified independently.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Data Migration & Models** - Import WordPress database, establish Laravel foundation
- [x] **Phase 2: URL Preservation & Routing** - Map WordPress permalink structure, protect SEO value
- [x] **Phase 3: Public Content Display** - Blog posts, categories, tags, static pages with ShoeMoney branding
- [x] **Phase 4: Comment System** - Threaded comments, submission, moderation workflow, Gravatar avatars
- [ ] **Phase 5: Search Integration** - Algolia-powered typeahead search across posts
- [ ] **Phase 6: Admin Panel** - Content management, comment moderation, user/role management
- [ ] **Phase 7: Performance & Polish** - Caching, optimization, dark mode, launch preparation

## Phase Details

### Phase 1: Data Migration & Models
**Goal**: WordPress database fully imported with all relationships preserved, Eloquent models operational
**Depends on**: Nothing (foundation phase)
**Requirements**: Foundation for CONT-01, CONT-02, CONT-03, COMM-01, ADMN-01, ADMN-02, ADMN-03, ADMN-04
**Success Criteria** (what must be TRUE):
  1. All WordPress posts, users, comments, categories, tags, and taxonomy relationships exist in Laravel database with correct associations
  2. (SKIPPED - user handles manually) Password migration
  3. Post content renders without broken shortcodes (converted to HTML/Blade during migration)
  4. Comment threading preserved with correct parent-child relationships visible in database
  5. User roles mapped from WordPress (Administrator, Editor, Author) to Laravel permission system
**Plans**: 7 plans in 6 waves

Plans:
- [x] 01-01-PLAN.md - Database configuration and WordPress read-only models
- [x] 01-02-PLAN.md - Laravel schema migrations (users, posts, pages, comments, taxonomies)
- [x] 01-03-PLAN.md - Laravel Eloquent models with relationships
- [x] 01-04-PLAN.md - User, category, and tag seeders with role mapping
- [x] 01-05-PLAN.md - Post, page, and taxonomy relationship seeders
- [x] 01-06-PLAN.md - Comment seeder (160K+ records with threading)
- [x] 01-07-PLAN.md - Migration verification and shortcode audit

### Phase 2: URL Preservation & Routing
**Goal**: All WordPress URLs mapped exactly to Laravel routes, no 404s on indexed content, SEO value protected
**Depends on**: Phase 1 (requires migrated posts with slugs)
**Requirements**: CONT-04
**Success Criteria** (what must be TRUE):
  1. Original WordPress post URLs load successfully in Laravel (e.g., `/2015/08/how-i-made-money/` works)
  2. Category and tag archive URLs match WordPress structure (`/category/marketing/`, `/tag/seo/`)
  3. Automated URL verification script confirms 100% of exported WordPress URLs resolve without 404s
  4. Sitemap.xml generated and accessible at `/sitemap.xml` with all published content
  5. Any legacy URL variations redirect to canonical versions with 301 status codes
**Plans**: 7 plans in 4 waves

Plans:
- [x] 02-01-PLAN.md - Routes setup with WordPress URL pattern matching
- [x] 02-02-PLAN.md - PostController and PageController implementation
- [x] 02-03-PLAN.md - CategoryController and TagController implementation
- [x] 02-04-PLAN.md - Sitemap generation with spatie/laravel-sitemap
- [x] 02-05-PLAN.md - URL verification Artisan command
- [x] 02-06-PLAN.md - Trailing slash redirect middleware
- [x] 02-07-PLAN.md - Final verification and human approval

### Phase 3: Public Content Display
**Goal**: Public-facing blog fully functional with posts, archives, static pages, and ShoeMoney brand identity
**Depends on**: Phase 2 (URLs must work before public display)
**Requirements**: CONT-01, CONT-02, CONT-03, FRNT-01, FRNT-02, FRNT-03
**Success Criteria** (what must be TRUE):
  1. User can view paginated blog homepage showing recent posts with titles, excerpts, publish dates, and author names
  2. User can click into individual posts and see full content with preserved formatting, images, and metadata
  3. User can browse category pages (e.g., `/category/marketing/`) showing filtered post listings
  4. User can browse tag pages (e.g., `/tag/seo/`) showing filtered post listings
  5. User can view static pages (About, Contact) with distinct layout from blog posts
  6. Site displays ShoeMoney brand identity (custom styling, colors, typography consistent with brand)
**Plans**: 8 plans in 5 waves

Plans:
- [x] 03-01-PLAN.md - Frontend packages and Tailwind CSS configuration
- [x] 03-02-PLAN.md - ShortcodeProcessor service and model accessors
- [x] 03-03-PLAN.md - Layout, navigation, and footer components
- [x] 03-04-PLAN.md - Blog homepage with post-card component
- [x] 03-05-PLAN.md - Single post view with typography styling
- [x] 03-06-PLAN.md - Category and tag archive views
- [x] 03-07-PLAN.md - Static page view
- [x] 03-08-PLAN.md - Visual verification checkpoint

### Phase 4: Comment System
**Goal**: Readers can view and submit comments with moderation workflow operational
**Depends on**: Phase 3 (posts must display before comments)
**Requirements**: COMM-01, COMM-02, COMM-03, COMM-04
**Success Criteria** (what must be TRUE):
  1. User can view threaded comments on posts with proper nesting (replies indented under parent comments)
  2. User can submit new comment with name, email, and comment body via Livewire form
  3. First-time commenter submissions held for moderation (status: pending)
  4. Approved commenters can post immediately (comments auto-approved based on email match)
  5. Gravatar avatars display next to comments based on commenter email address
**Plans**: 5 plans in 3 waves

Plans:
- [x] 04-01-PLAN.md - Livewire packages and layout configuration
- [x] 04-02-PLAN.md - CommentModerationService with auto-approval logic
- [x] 04-03-PLAN.md - CommentSection component for threaded display
- [x] 04-04-PLAN.md - CommentForm component with spam protection
- [x] 04-05-PLAN.md - Post view integration and visual verification

### Phase 5: Search Integration
**Goal**: Fast, typo-tolerant search operational with Algolia-powered typeahead
**Depends on**: Phase 3 (requires published posts to index)
**Requirements**: SRCH-01
**Success Criteria** (what must be TRUE):
  1. User can type in search bar and see instant results appear as they type (typeahead functionality)
  2. Search results include post titles and relevance-ranked matches from Algolia index
  3. All published posts indexed in Algolia with verified count matching database
  4. New posts auto-sync to Algolia index when published (queued Scout observer working)
  5. Search handles typos gracefully (e.g., "wordpres" finds "WordPress" posts)
**Plans**: 3 plans in 3 waves

Plans:
- [ ] 05-01-PLAN.md - Scout Extended installation and Post model configuration
- [ ] 05-02-PLAN.md - SearchBar Livewire component with typeahead UI
- [ ] 05-03-PLAN.md - Navigation integration, index import, and verification

### Phase 6: Admin Panel
**Goal**: Authors can manage content, moderate comments, and administer users via custom admin interface
**Depends on**: Phase 3 (requires content models and routing), Phase 4 (requires comment models)
**Requirements**: ADMN-01, ADMN-02, ADMN-03, ADMN-04
**Success Criteria** (what must be TRUE):
  1. Admin can create new post, edit content with rich text editor, publish/unpublish, and delete posts
  2. Admin can view pending comments queue, approve/reject individual comments, and delete spam
  3. Admin can create new categories and tags, edit names/slugs, and delete unused taxonomy terms
  4. Admin can create user accounts, assign roles (Admin/Editor/Author), and manage permissions
  5. Admin panel enforces role-based access (Authors can only edit own posts, Admins can edit all)
**Plans**: TBD

Plans:
- [ ] TBD during phase planning

### Phase 7: Performance & Polish
**Goal**: Site optimized for production traffic, dark mode implemented, launch-ready
**Depends on**: Phase 6 (all core features complete)
**Requirements**: FRNT-04
**Success Criteria** (what must be TRUE):
  1. Homepage loads in under 1 second (measured via Lighthouse/PageSpeed)
  2. Response caching enabled for rendered posts (cache invalidates on post update)
  3. User can toggle between light and dark mode themes with preference persisted
  4. Database queries optimized with indexes on frequently-queried columns (no N+1 issues)
  5. Automated backup system configured and tested (daily backups to external storage)
**Plans**: TBD

Plans:
- [ ] TBD during phase planning

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Data Migration & Models | 7/7 | Complete | 2026-01-24 |
| 2. URL Preservation & Routing | 7/7 | Complete | 2026-01-24 |
| 3. Public Content Display | 8/8 | Complete | 2026-01-25 |
| 4. Comment System | 5/5 | Complete | 2026-01-25 |
| 5. Search Integration | 0/3 | Planning complete | - |
| 6. Admin Panel | 0/TBD | Not started | - |
| 7. Performance & Polish | 0/TBD | Not started | - |

---
*Last updated: 2026-01-25*
