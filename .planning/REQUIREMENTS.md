# Requirements

## v1 Requirements

### Content Management

- [x] **CONT-01**: User can view all migrated WordPress posts with original content, formatting, and metadata preserved
- [x] **CONT-02**: User can browse posts by category or tag, with category/tag pages showing filtered results
- [x] **CONT-03**: User can view static pages (About, Contact, etc.) with distinct styling from blog posts
- [x] **CONT-04**: User can access all existing posts via their original WordPress URLs (no 404s for indexed content)

### Comments

- [x] **COMM-01**: User can view threaded comments on posts with proper nesting and timestamps
- [x] **COMM-02**: User can submit comments on posts with name, email, and comment body fields
- [x] **COMM-03**: First-time commenters are held for moderation; approved commenters post immediately
- [x] **COMM-04**: Comments display Gravatar avatars based on commenter email address

### Search

- [x] **SRCH-01**: User can search posts via typeahead that shows instant results as they type (Algolia-powered)

### Admin Panel

- [x] **ADMN-01**: Admin can create, edit, publish, and delete posts with rich text editor
- [x] **ADMN-02**: Admin can view pending comments, approve/reject them, and delete spam
- [x] **ADMN-03**: Admin can create, edit, and delete categories and tags
- [x] **ADMN-04**: Admin can manage multiple authors with role-based permissions (admin, editor, author)

### Frontend

- [x] **FRNT-01**: User can browse paginated blog listing showing recent posts with excerpts
- [x] **FRNT-02**: User can view category and tag archive pages with filtered post listings
- [x] **FRNT-03**: Frontend reflects ShoeMoney brand identity with custom styling and design
- [x] **FRNT-04**: User can toggle between light and dark mode themes

---

## v2 Requirements (Deferred)

### Search Enhancements
- Full post content search (body text, not just titles)
- Search result highlighting
- Search analytics

### Comment Enhancements
- Email notifications for comment replies
- Comment threading beyond 2 levels

### Content Enhancements
- Post scheduling (publish at future date)
- Draft autosave

---

## Out of Scope

- **Plugin/widget system** — WordPress baggage, unnecessary complexity
- **Post revisions/autosave** — not needed for this workflow
- **Pingbacks/trackbacks** — obsolete, mostly spam vectors
- **Complex media library** — content is primarily text
- **OAuth/social login** — email/password sufficient
- **Real-time notifications** — not a social platform
- **Built-in analytics** — use external tools
- **Custom post types** — blog posts only
- **Comment reactions/likes** — keep it simple

---

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| CONT-01 | Phase 3 | Complete |
| CONT-02 | Phase 3 | Complete |
| CONT-03 | Phase 3 | Complete |
| CONT-04 | Phase 2 | Complete |
| COMM-01 | Phase 4 | Complete |
| COMM-02 | Phase 4 | Complete |
| COMM-03 | Phase 4 | Complete |
| COMM-04 | Phase 4 | Complete |
| SRCH-01 | Phase 5 | Complete |
| ADMN-01 | Phase 6 | Complete |
| ADMN-02 | Phase 6 | Complete |
| ADMN-03 | Phase 6 | Complete |
| ADMN-04 | Phase 6 | Complete |
| FRNT-01 | Phase 3 | Complete |
| FRNT-02 | Phase 3 | Complete |
| FRNT-03 | Phase 3 | Complete |
| FRNT-04 | Phase 7 | Complete |

---
*Last updated: 2026-01-25 after Phase 7 completion*
