# Requirements

## v1 Requirements

### Content Management

- [ ] **CONT-01**: User can view all migrated WordPress posts with original content, formatting, and metadata preserved
- [ ] **CONT-02**: User can browse posts by category or tag, with category/tag pages showing filtered results
- [ ] **CONT-03**: User can view static pages (About, Contact, etc.) with distinct styling from blog posts
- [x] **CONT-04**: User can access all existing posts via their original WordPress URLs (no 404s for indexed content)

### Comments

- [ ] **COMM-01**: User can view threaded comments on posts with proper nesting and timestamps
- [ ] **COMM-02**: User can submit comments on posts with name, email, and comment body fields
- [ ] **COMM-03**: First-time commenters are held for moderation; approved commenters post immediately
- [ ] **COMM-04**: Comments display Gravatar avatars based on commenter email address

### Search

- [ ] **SRCH-01**: User can search posts via typeahead that shows instant results as they type (Algolia-powered)

### Admin Panel

- [ ] **ADMN-01**: Admin can create, edit, publish, and delete posts with rich text editor
- [ ] **ADMN-02**: Admin can view pending comments, approve/reject them, and delete spam
- [ ] **ADMN-03**: Admin can create, edit, and delete categories and tags
- [ ] **ADMN-04**: Admin can manage multiple authors with role-based permissions (admin, editor, author)

### Frontend

- [ ] **FRNT-01**: User can browse paginated blog listing showing recent posts with excerpts
- [ ] **FRNT-02**: User can view category and tag archive pages with filtered post listings
- [ ] **FRNT-03**: Frontend reflects ShoeMoney brand identity with custom styling and design
- [ ] **FRNT-04**: User can toggle between light and dark mode themes

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
| CONT-01 | Phase 3 | Pending |
| CONT-02 | Phase 3 | Pending |
| CONT-03 | Phase 3 | Pending |
| CONT-04 | Phase 2 | Complete |
| COMM-01 | Phase 4 | Pending |
| COMM-02 | Phase 4 | Pending |
| COMM-03 | Phase 4 | Pending |
| COMM-04 | Phase 4 | Pending |
| SRCH-01 | Phase 5 | Pending |
| ADMN-01 | Phase 6 | Pending |
| ADMN-02 | Phase 6 | Pending |
| ADMN-03 | Phase 6 | Pending |
| ADMN-04 | Phase 6 | Pending |
| FRNT-01 | Phase 3 | Pending |
| FRNT-02 | Phase 3 | Pending |
| FRNT-03 | Phase 3 | Pending |
| FRNT-04 | Phase 7 | Pending |

---
*Last updated: 2026-01-24 after roadmap creation*
