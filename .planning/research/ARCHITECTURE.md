# Architecture Patterns: Laravel/Livewire Blog System

**Domain:** Multi-author Blog Platform
**Researched:** 2026-01-24
**Confidence:** HIGH

## Recommended Architecture

Laravel/Livewire blog systems follow a **layered MVC architecture** with event-driven components and service abstraction. The architecture separates concerns across distinct layers while maintaining Laravel's conventions.

```
┌─────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Livewire   │  │    Blade     │  │  Tailwind    │  │
│  │  Components  │  │  Templates   │  │     CSS      │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────────────────────────────────────┐
│                   Application Layer                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Controllers  │  │   Policies   │  │    Routes    │  │
│  │ (HTTP/Admin) │  │ (AuthZ)      │  │              │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────────────────────────────────────┐
│                    Business Layer                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Services   │  │   Events     │  │  Observers   │  │
│  │   (Logic)    │  │  (Domain)    │  │ (Lifecycle)  │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────────────────────────────────────┐
│                   Persistence Layer                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  Eloquent    │  │ Repositories │  │  Migrations  │  │
│  │   Models     │  │  (Optional)  │  │              │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                          │
┌─────────────────────────────────────────────────────────┐
│                   Infrastructure Layer                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │   Database   │  │    Algolia   │  │    Queue     │  │
│  │    (MySQL)   │  │    Scout     │  │   Workers    │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
```

## Component Boundaries

### Core Domain Components

| Component | Responsibility | Communicates With | Location |
|-----------|---------------|-------------------|----------|
| **Post Model** | Post entity, relationships, scopes | Categories, Tags, Comments, Users, Search | `app/Models/Post.php` |
| **User Model** | Author entity, authentication | Posts, Comments, Admin | `app/Models/User.php` |
| **Category Model** | Post categorization | Posts | `app/Models/Category.php` |
| **Tag Model** | Post tagging (many-to-many) | Posts via pivot | `app/Models/Tag.php` |
| **Comment Model** | User comments on posts | Posts, Users | `app/Models/Comment.php` |

### Livewire Components (Presentation)

| Component | Responsibility | Communicates With | Location |
|-----------|---------------|-------------------|----------|
| **PostList** | Display paginated posts | Post Model, Search | `resources/views/components/post-list.php` |
| **PostDetail** | Single post view with comments | Post, Comment Models | `resources/views/pages/post-detail.php` |
| **CommentForm** | Comment submission | Comment Model, Policies | `resources/views/components/comment-form.php` |
| **SearchBar** | Search interface | Scout/Algolia | `resources/views/components/search-bar.php` |
| **AdminPostEditor** | Post CRUD interface | Post Service, Media | `resources/views/pages/admin/post-editor.php` |
| **CategoryManager** | Category CRUD | Category Service | `resources/views/pages/admin/category-manager.php` |

**Pattern:** Single-file components (default in Livewire 4) for small/medium complexity, multi-file for complex admin interfaces.

### Service Layer (Business Logic)

| Service | Responsibility | Communicates With | Location |
|---------|---------------|-------------------|----------|
| **PostService** | Post CRUD, publishing workflow | Post Model, Events | `app/Services/PostService.php` |
| **CommentModerationService** | Comment approval/rejection | Comment Model, Policies | `app/Services/CommentModerationService.php` |
| **SearchIndexService** | Algolia sync orchestration | Scout, Post Model | `app/Services/SearchIndexService.php` |
| **WordPressMigrationService** | WP data import | Migration Models | `app/Services/WordPressMigrationService.php` |

**When to use:** Complex business logic, multi-step workflows, or logic shared across controllers/components.

### Observer Layer (Lifecycle Events)

| Observer | Responsibility | Triggers On | Location |
|----------|---------------|-------------|----------|
| **PostObserver** | Auto-slug generation, search sync | created, updated, deleted | `app/Observers/PostObserver.php` |
| **CommentObserver** | Notification dispatch | created, approved | `app/Observers/CommentObserver.php` |
| **UserObserver** | Profile setup | created | `app/Observers/UserObserver.php` |

**Events Handled:** `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `restoring`, `restored`

### Policy Layer (Authorization)

| Policy | Responsibility | Guards Against | Location |
|--------|---------------|----------------|----------|
| **PostPolicy** | Author-only editing, admin override | Unauthorized edits | `app/Policies/PostPolicy.php` |
| **CommentPolicy** | Comment ownership, moderation | Spam, unauthorized deletes | `app/Policies/CommentPolicy.php` |
| **CategoryPolicy** | Admin-only management | Unauthorized category changes | `app/Policies/CategoryPolicy.php` |

**Pattern:** Gate/policy checks in controllers before service calls, automatic in Livewire via `authorize()`.

### Admin Panel (Custom CRUD)

| Module | Type | Responsibility | Location |
|--------|------|---------------|----------|
| **Dashboard** | Overview page | Stats, recent activity | `resources/views/pages/admin/dashboard.php` |
| **Post Manager** | CRUD interface | Create/edit/delete posts | `resources/views/pages/admin/posts/` |
| **Comment Moderation** | Approval queue | Approve/reject comments | `resources/views/pages/admin/comments.php` |
| **User Management** | User CRUD | Manage authors, roles | `resources/views/pages/admin/users.php` |
| **Category/Tag Manager** | Taxonomy CRUD | Organize content | `resources/views/pages/admin/taxonomies.php` |

**Architecture Decision:** Custom-built admin using Livewire components (not a package like Nova/Backpack) for full control and brownfield integration.

### Search Integration (Algolia Scout)

| Component | Responsibility | Type | Location |
|-----------|---------------|------|----------|
| **Searchable Trait** | Auto-sync to Algolia | Model trait | `Post::class` uses Scout |
| **SearchIndexSettings** | Configure searchable fields | Config | `config/scout.php` |
| **SearchController** | Handle search queries | HTTP Controller | `app/Http/Controllers/SearchController.php` |
| **SearchBar Component** | User interface | Livewire | `resources/views/components/search-bar.php` |

**Pattern:** Observer-based auto-sync on model events, queued for production performance.

## Data Flow

### Public-Facing Blog Flow

```
1. User Request
   └─> Route::livewire('/posts/{slug}')
       └─> PostDetail Component (Livewire)
           ├─> Post::with(['category', 'tags', 'author', 'comments'])->where('slug', $slug)
           ├─> CommentPolicy::viewAny() check
           └─> Render Blade template with data

2. Comment Submission
   └─> CommentForm Component (Livewire)
       ├─> CommentPolicy::create() check
       ├─> CommentService::create($data)
       │   ├─> Comment::create()
       │   └─> CommentObserver::created() → dispatch(CommentSubmitted::class)
       └─> Flash success message
```

### Admin Content Management Flow

```
1. Admin Creates Post
   └─> Route::livewire('/admin/posts/create')
       └─> AdminPostEditor Component (Livewire)
           ├─> PostPolicy::create() check
           ├─> PostService::createDraft($data)
           │   ├─> Post::create(['status' => 'draft'])
           │   └─> PostObserver::created()
           │       ├─> Generate slug from title
           │       └─> Skip Algolia sync (draft)
           └─> Redirect to edit page

2. Admin Publishes Post
   └─> AdminPostEditor Component action
       ├─> PostPolicy::publish() check
       ├─> PostService::publish($post)
       │   ├─> $post->update(['status' => 'published', 'published_at' => now()])
       │   └─> PostObserver::updated()
       │       ├─> Sync to Algolia via Scout (queued)
       │       └─> dispatch(PostPublished::class)
       └─> Flash success message
```

### Search Flow

```
1. User Searches
   └─> SearchBar Component (Livewire)
       ├─> POST /search?q=laravel
       └─> SearchController::index($request)
           ├─> Post::search($query)->where('published', true)->paginate(15)
           │   └─> Algolia API request (async)
           └─> Return results to SearchResults Component

2. Background Index Sync
   └─> PostObserver::updated()
       ├─> $post->searchable() (via Scout trait)
       └─> Queue: SyncPostToAlgolia job
           ├─> $post->toSearchableArray() → ['title', 'content', 'author', 'tags']
           └─> Algolia SDK push to 'posts_index'
```

### WordPress Migration Flow

```
1. Run Migration Command
   └─> php artisan migrate:wordpress
       └─> WordPressMigrationService::import()
           ├─> Extract from WordPress DB
           │   ├─> wp_posts → Post::create()
           │   ├─> wp_users → User::create()
           │   ├─> wp_terms → Category/Tag::create()
           │   └─> wp_comments → Comment::create()
           ├─> Map relationships
           │   ├─> post_tag pivot
           │   └─> post_category
           └─> PostObserver::created() fires for each
               └─> Queue Algolia sync
```

## Database Architecture

### Core Schema (Normalized)

```sql
-- Users (multi-author support)
users
├─ id (PK)
├─ name
├─ email (unique)
├─ password
├─ role (enum: admin, author, subscriber)
└─ timestamps

-- Posts (core content)
posts
├─ id (PK)
├─ user_id (FK → users)
├─ category_id (FK → categories)
├─ title
├─ slug (unique)
├─ content (longtext)
├─ excerpt
├─ featured_image
├─ status (enum: draft, published, archived)
├─ published_at
├─ timestamps
└─ soft_deletes

-- Categories (one-to-many with posts)
categories
├─ id (PK)
├─ name
├─ slug (unique)
├─ description
└─ timestamps

-- Tags (many-to-many with posts)
tags
├─ id (PK)
├─ name
├─ slug (unique)
└─ timestamps

-- Post-Tag Pivot
post_tag
├─ id (PK)
├─ post_id (FK → posts, cascade)
├─ tag_id (FK → tags, cascade)
└─ unique [post_id, tag_id]

-- Comments (nested via parent_id)
comments
├─ id (PK)
├─ post_id (FK → posts, cascade)
├─ user_id (FK → users, cascade)
├─ parent_id (FK → comments, nullable)
├─ body (text)
├─ status (enum: pending, approved, rejected)
├─ timestamps
└─ soft_deletes
```

**Key Design Decisions:**
- **Normalized tags**: Separate table with pivot (not JSON) for efficient querying
- **Soft deletes**: Recovery capability for posts/comments
- **Status enums**: Explicit state management (not boolean)
- **Nested comments**: `parent_id` for threaded discussions (optional complexity)

### Eloquent Relationships

```php
// Post.php
public function category() { return $this->belongsTo(Category::class); }
public function tags() { return $this->belongsToMany(Tag::class); }
public function author() { return $this->belongsTo(User::class, 'user_id'); }
public function comments() { return $this->hasMany(Comment::class); }

// User.php
public function posts() { return $this->hasMany(Post::class); }
public function comments() { return $this->hasMany(Comment::class); }

// Comment.php
public function post() { return $this->belongsTo(Post::class); }
public function user() { return $this->belongsTo(User::class); }
public function replies() { return $this->hasMany(Comment::class, 'parent_id'); }
public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }
```

## Patterns to Follow

### Pattern 1: Livewire Single-File Components (Default)
**What:** Combine PHP logic and Blade template in one file with `.php` extension.

**When:** Small to medium complexity components (most blog UI components).

**Example:**
```php
<?php
// resources/views/components/post-card.php

use App\Models\Post;
use Livewire\Volt\Component;

new class extends Component {
    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post;
    }
}; ?>

<div class="post-card">
    <h2>{{ $post->title }}</h2>
    <p>{{ Str::limit($post->excerpt, 150) }}</p>
    <a href="{{ route('posts.show', $post->slug) }}">Read more</a>
</div>
```

**Benefits:** Colocation, less file switching, faster prototyping.

### Pattern 2: Service Layer for Complex Business Logic
**What:** Extract multi-step workflows from controllers into dedicated service classes.

**When:** Publishing workflow, batch operations, multi-model orchestration.

**Example:**
```php
// app/Services/PostService.php
namespace App\Services;

use App\Models\Post;
use App\Events\PostPublished;

class PostService
{
    public function publish(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        event(new PostPublished($post));

        return $post->fresh();
    }

    public function createDraft(array $data): Post
    {
        return Post::create([
            ...$data,
            'status' => 'draft',
            'user_id' => auth()->id(),
        ]);
    }
}
```

**Benefits:** Testable, reusable, keeps controllers thin.

### Pattern 3: Observer-Based Lifecycle Automation
**What:** Use Eloquent observers to handle side effects on model events.

**When:** Auto-slug generation, search indexing, audit logging, notifications.

**Example:**
```php
// app/Observers/PostObserver.php
namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Str;

class PostObserver
{
    public function creating(Post $post): void
    {
        if (empty($post->slug)) {
            $post->slug = Str::slug($post->title);
        }
    }

    public function updated(Post $post): void
    {
        // Only sync published posts to search
        if ($post->status === 'published') {
            $post->searchable();
        } elseif ($post->wasChanged('status') && $post->status !== 'published') {
            $post->unsearchable();
        }
    }
}

// Register in AppServiceProvider::boot()
Post::observe(PostObserver::class);
```

**Benefits:** Automatic, decoupled from controllers, centralized lifecycle logic.

### Pattern 4: Policy-Based Authorization
**What:** Define authorization rules in policy classes, enforce in controllers/components.

**When:** Multi-author permissions, role-based access, ownership checks.

**Example:**
```php
// app/Policies/PostPolicy.php
namespace App\Policies;

use App\Models\{User, Post};

class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        // Authors can edit their own posts, admins can edit any
        return $user->id === $post->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->role === 'admin';
    }
}

// In Livewire component or controller
$this->authorize('update', $post);
```

**Benefits:** Reusable, testable, centralized authorization logic.

### Pattern 5: Algolia Scout Auto-Sync with Queuing
**What:** Use Scout's `Searchable` trait with queue workers for async indexing.

**When:** Production environment with high write volume.

**Example:**
```php
// app/Models/Post.php
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => strip_tags($this->content),
            'excerpt' => $this->excerpt,
            'author' => $this->author->name,
            'category' => $this->category->name,
            'tags' => $this->tags->pluck('name')->toArray(),
            'published_at' => $this->published_at->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }
}

// config/scout.php
'queue' => env('SCOUT_QUEUE', true),
```

**Benefits:** Non-blocking, scalable, automatic sync on model changes.

### Pattern 6: Route Model Binding with Slugs
**What:** Use route model binding with slug instead of ID for SEO-friendly URLs.

**When:** All public-facing post routes.

**Example:**
```php
// app/Models/Post.php
public function getRouteKeyName(): string
{
    return 'slug';
}

// routes/web.php
Route::livewire('/posts/{post:slug}', 'post-detail')
    ->name('posts.show');

// Livewire component receives Post $post automatically
```

**Benefits:** SEO-friendly, clean URLs, automatic 404 handling.

## Anti-Patterns to Avoid

### Anti-Pattern 1: Deep Livewire Component Nesting
**What:** Nesting Livewire components more than 2 levels deep.

**Why bad:** Performance degradation (DOM diffing overhead), event propagation complexity, state management issues.

**Instead:** Use Blade components for presentational nesting, keep Livewire components flat. Example:
```php
// BAD: Livewire → Livewire → Livewire → Livewire (4 levels)
// GOOD: Livewire → Blade → Blade → Livewire (islands pattern)
```

**Source:** [Laravel Livewire Best Practices](https://github.com/michael-rubel/livewire-best-practices)

### Anti-Pattern 2: Passing Large Objects to Livewire Properties
**What:** Binding entire Eloquent models with many relationships as public properties.

**Why bad:** Serialization overhead, slow component lifecycle, network bloat.

**Instead:** Pass only required primitive values or use computed properties:
```php
// BAD
public Post $post; // Includes all relationships, heavy

// GOOD
public string $title;
public string $excerpt;
public int $postId;

#[Computed]
public function post(): Post
{
    return Post::with(['author', 'category'])->find($this->postId);
}
```

**Source:** [Livewire Best Practices - Avoid Passing Large Objects](https://github.com/michael-rubel/livewire-best-practices)

### Anti-Pattern 3: Business Logic in Blade Templates
**What:** Complex conditional logic, database queries, or calculations in Blade views.

**Why bad:** Untestable, violates separation of concerns, performance issues (N+1 queries).

**Instead:** Move logic to component methods, services, or computed properties:
```php
// BAD in Blade
@if($post->status === 'published' && $post->published_at->isPast() && auth()->user()->can('view', $post))

// GOOD in Component
#[Computed]
public function isViewable(): bool
{
    return $this->post->status === 'published'
        && $this->post->published_at->isPast()
        && auth()->user()->can('view', $this->post);
}

// Blade
@if($this->isViewable)
```

### Anti-Pattern 4: Direct Model Manipulation in Controllers
**What:** Complex CRUD logic, validation, and side effects directly in controller methods.

**Why bad:** Difficult to test, not reusable, controllers become bloated.

**Instead:** Extract to service classes for complex operations:
```php
// BAD
public function publish(Post $post)
{
    $post->status = 'published';
    $post->published_at = now();
    $post->save();
    Cache::forget("post-{$post->id}");
    event(new PostPublished($post));
    // ... more logic
}

// GOOD
public function publish(Post $post)
{
    $this->authorize('publish', $post);
    $this->postService->publish($post);
    return redirect()->back()->with('success', 'Post published!');
}
```

### Anti-Pattern 5: Repository Pattern Overuse
**What:** Creating repositories for every model regardless of complexity.

**Why bad:** Unnecessary abstraction, Laravel's Eloquent already provides repository pattern, adds boilerplate without value.

**Instead:** Use repositories only when:
- You need to swap data sources (rare)
- Complex query logic needs centralization
- Testing requires heavy mocking

For most blog operations, Eloquent directly in services is sufficient.

**Source:** [Why I Prefer Service Pattern over Repository Pattern in Laravel](https://rawbinn.com/blog/repository-and-service-design-pattern-in-laravel)

### Anti-Pattern 6: Storing Sensitive Data in Livewire Public Properties
**What:** Exposing API keys, passwords, or private user data as public properties.

**Why bad:** Public properties are serialized to client-side HTML, visible in browser DevTools.

**Instead:** Use protected properties or the `#[Locked]` attribute:
```php
// BAD
public string $apiKey; // Sent to client!

// GOOD
protected string $apiKey; // Server-side only

// Or for Laravel 10+
#[Locked]
public string $userId; // Cannot be modified client-side
```

**Source:** [Livewire Best Practices - Security](https://github.com/michael-rubel/livewire-best-practices#security-considerations)

## Scalability Considerations

| Concern | At 100 users | At 10K users | At 1M users |
|---------|--------------|--------------|-------------|
| **Database Queries** | Direct Eloquent queries | Eager loading (`with()`), query caching | Read replicas, query optimization, DB indexes |
| **Search** | Database `LIKE` queries acceptable | Algolia with auto-sync | Algolia with queued sync, replica indices |
| **Comment Moderation** | Manual approval in admin panel | Automated spam filters (Akismet) | ML-based moderation, worker queues |
| **Image Uploads** | Local storage | Cloud storage (S3) with CDN | Image optimization service (Cloudinary/Imgix) |
| **Cache Strategy** | File cache | Redis cache for views, queries | Redis cluster, cache warming |
| **Admin Panel** | Direct DB writes | Background jobs for heavy operations | Admin read replicas, optimistic UI updates |
| **Session Storage** | File-based | Redis/Memcached | Redis cluster with session affinity |

## Build Order (Dependency-Based)

Recommended implementation sequence based on component dependencies:

### Phase 1: Foundation (No dependencies)
1. Database migrations (users, posts, categories, tags, comments)
2. Eloquent models with relationships
3. Seeders for test data

### Phase 2: Core Features (Depends on Phase 1)
1. Authentication (Laravel Breeze/Jetstream)
2. Policy classes (PostPolicy, CommentPolicy)
3. Observer classes (PostObserver for auto-slug)

### Phase 3: Public Frontend (Depends on Phase 1-2)
1. Livewire components (PostList, PostDetail, CommentForm)
2. Public routes (Route::livewire)
3. Blade layouts and Tailwind styling

### Phase 4: Search Integration (Depends on Phase 1-3)
1. Algolia Scout configuration
2. SearchBar Livewire component
3. Batch import existing posts (`scout:import`)

### Phase 5: Admin Panel (Depends on Phase 1-4)
1. Admin routes with middleware
2. AdminPostEditor Livewire component
3. Category/Tag management
4. Comment moderation interface

### Phase 6: WordPress Migration (Depends on Phase 1-5)
1. WordPress DB connection configuration
2. Migration service class
3. Artisan command for import
4. Data mapping and validation

**Rationale:** Each phase builds on previous phases. Search and admin can be developed in parallel after Phase 3. WordPress migration is last because it requires all domain models to be functional.

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── SearchController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       └── PostController.php
│   └── Middleware/
│       └── AdminOnly.php
├── Models/
│   ├── Post.php
│   ├── User.php
│   ├── Category.php
│   ├── Tag.php
│   └── Comment.php
├── Policies/
│   ├── PostPolicy.php
│   └── CommentPolicy.php
├── Observers/
│   ├── PostObserver.php
│   └── CommentObserver.php
├── Services/
│   ├── PostService.php
│   ├── CommentModerationService.php
│   └── WordPressMigrationService.php
└── Events/
    ├── PostPublished.php
    └── CommentSubmitted.php

resources/
└── views/
    ├── components/        # Blade components
    │   ├── post-card.php
    │   ├── comment-form.php
    │   └── search-bar.php
    ├── pages/            # Full Livewire pages
    │   ├── post-list.php
    │   ├── post-detail.php
    │   └── admin/
    │       ├── dashboard.php
    │       ├── post-editor.php
    │       └── comment-moderation.php
    └── layouts/
        ├── app.blade.php
        └── admin.blade.php

routes/
├── web.php           # Public routes
├── admin.php         # Admin routes (separate file)
└── console.php

config/
├── scout.php         # Algolia configuration
└── livewire.php      # Component namespaces
```

## Configuration Files

### config/scout.php (Algolia)
```php
'algolia' => [
    'id' => env('ALGOLIA_APP_ID', ''),
    'secret' => env('ALGOLIA_SECRET', ''),
    'index-settings' => [
        Post::class => [
            'searchableAttributes' => [
                'title',
                'content',
                'excerpt',
                'author',
            ],
            'attributesForFaceting' => [
                'filterOnly(category)',
                'filterOnly(status)',
            ],
            'customRanking' => ['desc(published_at)'],
        ],
    ],
],
'queue' => env('SCOUT_QUEUE', true),
```

### config/livewire.php (Namespaces)
```php
'view_namespaces' => [
    'pages' => resource_path('views/pages'),
    'admin' => resource_path('views/pages/admin'),
],
```

## Sources

**Livewire Architecture (HIGH confidence):**
- [Livewire 4 Components Documentation](https://livewire.laravel.com/docs/4.x/components)
- [Laravel Livewire Best Practices GitHub](https://github.com/michael-rubel/livewire-best-practices)
- [Building a Blog with Laravel, Livewire, and Laravel Breeze](https://neon.com/guides/laravel-livewire-blog)

**Laravel 12 Features (HIGH confidence):**
- [Laravel 12 Release Notes](https://laravel.com/docs/12.x/releases)
- [Laravel 12 Starter Kits Explained](https://developer.vonage.com/en/blog/laravel-12-starter-kits-explained-react-vue-and-livewire)

**Service/Repository Patterns (MEDIUM confidence):**
- [Structuring Laravel with Repository Pattern and Services](https://dev.to/blamsa0mine/structuring-a-laravel-project-with-the-repository-pattern-and-services-11pm)
- [Why Service Pattern over Repository in Laravel](https://rawbinn.com/blog/repository-and-service-design-pattern-in-laravel)

**Algolia Scout (HIGH confidence):**
- [Laravel Scout Documentation](https://laravel.com/docs/12.x/scout)
- [Algolia Scout Extended Introduction](https://www.algolia.com/doc/framework-integration/laravel/getting-started/introduction-to-scout-extended)

**Observers & Events (HIGH confidence):**
- [Laravel Events Documentation](https://laravel.com/docs/12.x/events)
- [Laravel Observers: The Cleanest Way to Handle Model Events](https://backpackforlaravel.com/articles/tutorials/laravel-observers-the-cleanest-way-to-handle-model-events)
- [The Observer Pattern in Laravel: A Comprehensive Guide](https://masteryoflaravel.medium.com/the-observer-pattern-in-laravel-a-comprehensive-guide-7403b83d075c)

**WordPress Migration (MEDIUM confidence):**
- [Ultimate Guide to Migrating WordPress to Laravel](https://dmwebsoft.com/the-ultimate-guide-to-migrating-from-wordpress-to-laravel-step-by-step)
- [How to Integrate WordPress into Laravel - 2026](https://www.aalpha.net/blog/how-to-integrate-wordpress-into-laravel/)

**Database Schema (HIGH confidence):**
- [Laravel Eloquent Relationships Tutorial](https://youngpetals.com/laravel-eloquent-relationships-tutorial/)
- [Designing a Blog Database Schema](https://www.dragonflydb.io/databases/schema/blog)

**Admin Panel Patterns (MEDIUM confidence):**
- [10 Best Laravel Admin Panels in 2026](https://colorlib.com/wp/laravel-admin-panels/)
- [How to Choose the Best Laravel Admin Panel](https://retool.com/blog/best-laravel-admin-panels)
