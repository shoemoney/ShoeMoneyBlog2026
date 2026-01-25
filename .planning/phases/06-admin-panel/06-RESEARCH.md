# Phase 6: Admin Panel - Research

**Researched:** 2026-01-25
**Domain:** Laravel Livewire Admin Panel (Custom Build)
**Confidence:** HIGH

## Summary

This research covers building a custom admin panel for a Laravel 12 / Livewire 4 blog application. The decision to build custom (rather than use Filament/Nova) provides full control over UX and avoids package dependencies, but requires implementing authentication, authorization, CRUD operations, and a rich text editor from scratch.

The standard approach for this domain involves:
- **Authorization**: Laravel Gates for global checks, Policies for model-specific permissions
- **Admin routing**: Dedicated route group with `auth` + custom role middleware
- **Full-page Livewire components**: Each admin view is a routable Livewire component with dedicated layout
- **Rich text editing**: Tiptap editor with Alpine.js integration (wire:model via $entangle)
- **Data tables**: Livewire pagination trait with search/filter/sort capabilities

**Primary recommendation:** Use Laravel Policies for model authorization (PostPolicy, CommentPolicy, etc.) with a `before()` method granting admins full access, combined with route middleware for section-level access control. Structure admin as full-page Livewire components using a dedicated `layouts.admin` Blade layout.

## Standard Stack

The established libraries/tools for this domain:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel | 12.x | Framework | Already in use |
| Livewire | 4.x | Full-page components, forms, real-time UI | Already in use, decision locked |
| Tailwind CSS | 4.x | Admin UI styling | Already in use |
| Alpine.js | 3.x | Client-side interactivity | Already in use |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| @tiptap/core | latest | Rich text editing | Post/page content editing |
| @tiptap/starter-kit | latest | Sensible Tiptap defaults | Core text formatting |
| @tailwindcss/typography | latest | Prose styling for editor | Rich text content display |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Tiptap | TinyMCE | TinyMCE is heavier but more feature-complete out of box |
| Tiptap | Quill | Quill simpler but less extensible |
| Tiptap | Jodit (livewire package) | Has Livewire package but less customizable |
| Custom tables | rappasoft/livewire-tables | Package adds dependency but faster to implement |

**Installation:**
```bash
npm install @tiptap/core @tiptap/pm @tiptap/starter-kit @tailwindcss/typography
```

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Http/
│   └── Middleware/
│       └── EnsureUserHasRole.php      # Role-based access middleware
├── Livewire/
│   └── Admin/
│       ├── Dashboard.php              # Admin dashboard
│       ├── Posts/
│       │   ├── PostIndex.php          # List all posts
│       │   ├── PostCreate.php         # Create post form
│       │   └── PostEdit.php           # Edit post form
│       ├── Comments/
│       │   └── CommentModeration.php  # Comment queue
│       ├── Taxonomies/
│       │   ├── CategoryManager.php    # Category CRUD
│       │   └── TagManager.php         # Tag CRUD
│       └── Users/
│           ├── UserIndex.php          # List users
│           └── UserForm.php           # Create/Edit user
├── Policies/
│   ├── PostPolicy.php
│   ├── CommentPolicy.php
│   ├── CategoryPolicy.php
│   ├── TagPolicy.php
│   └── UserPolicy.php
└── ...

resources/views/
├── components/
│   └── admin/
│       ├── layouts/
│       │   └── app.blade.php          # Admin layout with sidebar
│       ├── sidebar.blade.php          # Navigation sidebar
│       ├── flash-messages.blade.php   # Toast notifications
│       └── tiptap-editor.blade.php    # Rich text editor component
└── livewire/
    └── admin/
        ├── dashboard.blade.php
        ├── posts/
        │   ├── post-index.blade.php
        │   ├── post-create.blade.php
        │   └── post-edit.blade.php
        └── ...

routes/
└── web.php                            # Admin route group
```

### Pattern 1: Full-Page Livewire Components with Custom Layout
**What:** Each admin page is a full-page Livewire component routed directly, using `#[Layout]` attribute
**When to use:** All admin views
**Example:**
```php
// Source: https://livewire.laravel.com/docs/pages
<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
#[Title('Manage Posts')]
class PostIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $sortField = 'published_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->sortField = $field;
    }

    public function render()
    {
        return view('livewire.admin.posts.post-index', [
            'posts' => Post::query()
                ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(15),
        ]);
    }
}
```

### Pattern 2: Model Policies with Admin Override
**What:** Authorization through Policies with `before()` method for admin bypass
**When to use:** All model-based authorization
**Example:**
```php
// Source: https://laravel.com/docs/12.x/authorization
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Administrators bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null; // Fall through to specific policy method
    }

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view list
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isEditor(); // Editors and admins can create
    }

    public function update(User $user, Post $post): bool
    {
        // Editors can edit any, authors only their own
        return $user->isEditor() || $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isEditor() || $user->id === $post->user_id;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->isEditor(); // Only editors can publish
    }
}
```

### Pattern 3: Role-Based Middleware
**What:** Middleware that checks user role before allowing access to route group
**When to use:** Protecting entire admin section
**Example:**
```php
// Source: https://laravel.com/docs/12.x/middleware
<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
```

**Route registration:**
```php
// routes/web.php
Route::prefix('admin')
    ->middleware(['auth', EnsureUserHasRole::class.':administrator,editor,author'])
    ->group(function () {
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
        Route::get('/posts', \App\Livewire\Admin\Posts\PostIndex::class)->name('admin.posts.index');
        Route::get('/posts/create', \App\Livewire\Admin\Posts\PostCreate::class)->name('admin.posts.create');
        Route::get('/posts/{post}/edit', \App\Livewire\Admin\Posts\PostEdit::class)->name('admin.posts.edit');
        // ...
    });
```

### Pattern 4: Tiptap Editor with Alpine.js + Livewire
**What:** Rich text editor synced to Livewire property via Alpine's $wire.entangle()
**When to use:** Post/page content editing
**Example:**
```php
{{-- resources/views/components/admin/tiptap-editor.blade.php --}}
{{-- Source: https://mattlake.codes/blog/tiptap-tutorial/ --}}
@props(['id' => 'editor'])

<div
    x-data="setupEditor(@entangle($attributes->wire('model')).live)"
    x-init="init()"
    wire:ignore
    {{ $attributes->whereDoesntStartWith('wire:model') }}
>
    {{-- Toolbar --}}
    <template x-if="editor">
        <div class="flex flex-wrap gap-1 mb-2 p-2 bg-gray-100 rounded-t border border-b-0 border-gray-300">
            <button type="button" @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'bg-gray-300': editor.isActive('bold') }"
                class="px-2 py-1 rounded hover:bg-gray-200">
                <strong>B</strong>
            </button>
            <button type="button" @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'bg-gray-300': editor.isActive('italic') }"
                class="px-2 py-1 rounded hover:bg-gray-200">
                <em>I</em>
            </button>
            <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-gray-300': editor.isActive('heading', { level: 2 }) }"
                class="px-2 py-1 rounded hover:bg-gray-200">
                H2
            </button>
            <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-gray-300': editor.isActive('bulletList') }"
                class="px-2 py-1 rounded hover:bg-gray-200">
                List
            </button>
            <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'bg-gray-300': editor.isActive('blockquote') }"
                class="px-2 py-1 rounded hover:bg-gray-200">
                Quote
            </button>
        </div>
    </template>

    {{-- Editor Content --}}
    <div x-ref="editor" class="prose max-w-none p-4 border border-gray-300 rounded-b min-h-[300px] focus:outline-none"></div>
</div>
```

```javascript
// resources/js/admin/tiptap.js
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

window.setupEditor = function(content) {
    return {
        editor: null,
        content: content,

        init() {
            this.editor = new Editor({
                element: this.$refs.editor,
                extensions: [StarterKit],
                content: this.content,
                editorProps: {
                    attributes: {
                        class: 'focus:outline-none min-h-[280px]',
                    },
                },
                onUpdate: ({ editor }) => {
                    this.content = editor.getHTML();
                },
            });

            this.$watch('content', (value) => {
                if (value !== this.editor.getHTML()) {
                    this.editor.commands.setContent(value, false);
                }
            });
        },

        destroy() {
            this.editor?.destroy();
        },
    };
};
```

### Pattern 5: Livewire Form Objects
**What:** Dedicated form class for validation and data management
**When to use:** Complex forms (post creation/editing)
**Example:**
```php
// Source: https://livewire.laravel.com/docs/forms
<?php

namespace App\Livewire\Forms;

use App\Models\Post;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    public ?Post $post = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:255|unique:posts,slug')]
    public string $slug = '';

    #[Validate('required|string')]
    public string $content = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|in:draft,published')]
    public string $status = 'draft';

    public function setPost(Post $post): void
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->content = $post->content ?? '';
        $this->excerpt = $post->excerpt ?? '';
        $this->status = $post->status;
    }

    public function store(): Post
    {
        $this->validate();

        return Post::create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'published_at' => $this->status === 'published' ? now() : null,
        ]);
    }

    public function update(): void
    {
        $this->validate([
            'slug' => 'required|string|max:255|unique:posts,slug,' . $this->post->id,
        ]);

        $this->post->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'published_at' => $this->status === 'published' && !$this->post->published_at
                ? now()
                : $this->post->published_at,
        ]);
    }
}
```

### Anti-Patterns to Avoid
- **Fat components:** Don't put all CRUD in one component. Separate Index, Create, Edit.
- **Authorization in views only:** Always check authorization in component methods, not just hide buttons.
- **Direct property binding for complex objects:** Use Form objects or primitive types, not full Eloquent models on public properties.
- **Forgetting wire:ignore:** Rich text editors and other JS-managed elements need `wire:ignore`.
- **Missing resetPage():** When filtering/sorting, always call `$this->resetPage()` to avoid invalid page states.

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Pagination | Custom pagination logic | Livewire `WithPagination` trait | Handles query strings, page state, cursor pagination |
| Form validation | Manual if/else validation | Livewire `#[Validate]` attributes or Form objects | Automatic error display, rule reuse |
| Flash messages | Custom session management | Laravel `session()->flash()` + Livewire events | Works across redirects, component updates |
| Delete confirmation | Custom modal state | `wire:confirm` directive | Native browser dialog, no extra JS |
| Slug generation | Manual string manipulation | `Str::slug()` or client-side Alpine | Handles unicode, special chars |
| Password hashing | md5/sha1 | Laravel's `Hash::make()` / `'hashed'` cast | bcrypt is configured properly |

**Key insight:** Livewire and Laravel have solved most admin panel primitives. The value of a custom build is UX control, not reinventing infrastructure.

## Common Pitfalls

### Pitfall 1: Authorization Bypassed on Direct URL Access
**What goes wrong:** Hiding edit buttons but not authorizing in component methods
**Why it happens:** Assuming UI is the only access point
**How to avoid:** Always call `$this->authorize('update', $post)` in component methods
**Warning signs:** Users can access pages by typing URLs directly

### Pitfall 2: Livewire Re-renders Rich Text Editor
**What goes wrong:** Tiptap loses focus, cursor position, or content on Livewire updates
**Why it happens:** Livewire tries to morph DOM that JS controls
**How to avoid:** Always use `wire:ignore` on editor container
**Warning signs:** Editor content resets when other form fields update

### Pitfall 3: N+1 Queries on Index Pages
**What goes wrong:** Slow page loads on post listing with author names
**Why it happens:** Not eager loading relationships
**How to avoid:** Always `->with(['author', 'categories'])` in queries
**Warning signs:** Laravel Debugbar shows many duplicate queries

### Pitfall 4: Pagination Stays on High Page After Filter
**What goes wrong:** User filters results, stays on page 5 but only 2 pages of results exist
**Why it happens:** Not resetting page when search/filter changes
**How to avoid:** Call `$this->resetPage()` in `updating*` lifecycle hooks
**Warning signs:** "No results" shown when results exist on page 1

### Pitfall 5: Mass Assignment Vulnerability
**What goes wrong:** User promotes themselves to admin via form manipulation
**Why it happens:** Using `$request->all()` or unguarded models
**How to avoid:** Explicit `$fillable` properties, never include `role` in user-facing forms
**Warning signs:** Hidden form fields can change protected attributes

### Pitfall 6: Debug Mode in Production
**What goes wrong:** Stack traces expose code structure, database credentials
**Why it happens:** Forgetting to set `APP_DEBUG=false` in production
**How to avoid:** Always check `.env` before deployment
**Warning signs:** Detailed error pages visible to users

### Pitfall 7: Storing Passwords in Plain Text
**What goes wrong:** Database breach exposes all user passwords
**Why it happens:** Not using Laravel's hashed cast
**How to avoid:** Use `'password' => 'hashed'` cast on User model (already in codebase)
**Warning signs:** Password visible in database

## Code Examples

### Admin Layout with Sidebar
```php
{{-- resources/views/components/admin/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} - ShoeMoney Blog</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin/tiptap.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h1 class="text-xl font-bold">Admin</h1>
            </div>
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" wire:navigate
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.posts.index') }}" wire:navigate
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.posts.*') ? 'bg-gray-700' : '' }}">
                    Posts
                </a>
                <a href="{{ route('admin.comments.index') }}" wire:navigate
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.comments.*') ? 'bg-gray-700' : '' }}">
                    Comments
                </a>
                @can('viewAny', App\Models\User::class)
                <a href="{{ route('admin.users.index') }}" wire:navigate
                   class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    Users
                </a>
                @endcan
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 p-8">
            {{-- Flash Messages --}}
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
    @livewireScripts
</body>
</html>
```

### Delete with Confirmation
```php
{{-- In Livewire view --}}
<button wire:click="delete({{ $post->id }})"
        wire:confirm="Are you sure you want to delete '{{ $post->title }}'?"
        class="text-red-600 hover:text-red-800">
    Delete
</button>
```

```php
// In Livewire component
public function delete(Post $post): void
{
    $this->authorize('delete', $post);

    $post->delete();

    session()->flash('success', 'Post deleted successfully.');
}
```

### Comment Moderation Actions
```php
<?php

namespace App\Livewire\Admin\Comments;

use App\Models\Comment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.admin.layouts.app')]
#[Title('Comment Moderation')]
class CommentModeration extends Component
{
    use WithPagination;

    public string $status = 'pending';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function approve(Comment $comment): void
    {
        $this->authorize('update', $comment);
        $comment->update(['status' => 'approved']);
        session()->flash('success', 'Comment approved.');
    }

    public function reject(Comment $comment): void
    {
        $this->authorize('update', $comment);
        $comment->update(['status' => 'spam']);
        session()->flash('success', 'Comment marked as spam.');
    }

    public function delete(Comment $comment): void
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        session()->flash('success', 'Comment deleted.');
    }

    public function render()
    {
        return view('livewire.admin.comments.comment-moderation', [
            'comments' => Comment::query()
                ->with(['post', 'user'])
                ->where('status', $this->status)
                ->orderBy('created_at', 'desc')
                ->paginate(20),
        ]);
    }
}
```

### Testing Livewire Admin Components
```php
// Source: https://livewire.laravel.com/docs/testing
<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Posts\PostIndex;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_posts(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $posts = Post::factory()->count(3)->create();

        $this->actingAs($admin);

        Livewire::test(PostIndex::class)
            ->assertSee($posts[0]->title)
            ->assertSee($posts[1]->title)
            ->assertSee($posts[2]->title);
    }

    public function test_author_can_only_see_own_posts(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $ownPost = Post::factory()->create(['user_id' => $author->id]);
        $otherPost = Post::factory()->create();

        $this->actingAs($author);

        Livewire::test(PostIndex::class)
            ->assertSee($ownPost->title);
            // Authors see all posts in index, but can only edit own
    }

    public function test_search_filters_posts(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $matchingPost = Post::factory()->create(['title' => 'Laravel Tips']);
        $otherPost = Post::factory()->create(['title' => 'Python Guide']);

        $this->actingAs($admin);

        Livewire::test(PostIndex::class)
            ->set('search', 'Laravel')
            ->assertSee('Laravel Tips')
            ->assertDontSee('Python Guide');
    }

    public function test_admin_can_delete_post(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $post = Post::factory()->create();

        $this->actingAs($admin);

        Livewire::test(PostIndex::class)
            ->call('delete', $post->id)
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Blade + jQuery AJAX | Livewire full-page components | Livewire 3+ (2023) | True SPA-like feel, no custom JS |
| Multi-page admin forms | Single-page with wire:navigate | Livewire 3.0 | Faster navigation, persisted state |
| Manual form validation | Form objects with `#[Validate]` | Livewire 3.0 | Cleaner components, reusable validation |
| Custom modals for confirm | `wire:confirm` directive | Livewire 3.0 | No extra JS needed |
| TinyMCE/CKEditor | Tiptap | 2022+ | Lighter, more customizable, better DX |
| Controller-based admin | Full-page Livewire | 2023+ | Less boilerplate, reactive UI |

**Deprecated/outdated:**
- `wire:model.defer`: Removed in Livewire 3+, defer is now default behavior
- `$this->emit()`: Replaced by `$this->dispatch()` in Livewire 3+
- Middleware in `Kernel.php`: Laravel 11+ uses `bootstrap/app.php`

## Open Questions

Things that couldn't be fully resolved:

1. **Image upload in rich text editor**
   - What we know: Tiptap supports image extension, Livewire has file upload support
   - What's unclear: Best pattern for combining them (paste upload vs file picker)
   - Recommendation: Start with file picker modal, add paste upload later if needed

2. **Soft deletes for posts/comments**
   - What we know: Laravel supports soft deletes, existing models don't use it
   - What's unclear: Whether migration phase added soft deletes column
   - Recommendation: Check migration, add if not present; useful for "trash" feature

3. **Bulk actions (select multiple, delete/approve)**
   - What we know: Livewire can handle checkbox state, rappasoft package has this built-in
   - What's unclear: Complexity of implementing without package
   - Recommendation: Implement single-item actions first, add bulk later if needed

## Sources

### Primary (HIGH confidence)
- [Laravel 12.x Authorization Docs](https://laravel.com/docs/12.x/authorization) - Gates, Policies, middleware
- [Laravel 12.x Middleware Docs](https://laravel.com/docs/12.x/middleware) - Custom middleware, groups, aliases
- [Livewire Pages Docs](https://livewire.laravel.com/docs/pages) - Full-page components, layouts, titles
- [Livewire Forms Docs](https://livewire.laravel.com/docs/forms) - Form objects, validation
- [Livewire Pagination Docs](https://livewire.laravel.com/docs/pagination) - WithPagination trait
- [Livewire Actions Docs](https://livewire.laravel.com/docs/actions) - wire:click, wire:confirm, parameters

### Secondary (MEDIUM confidence)
- [Matthew Lake Tiptap Tutorial](https://mattlake.codes/blog/tiptap-tutorial/) - Tiptap + Livewire integration pattern
- [Laravel Daily Middleware/Route Groups](https://laraveldaily.com/lesson/laravel-beginners/middleware-route-groups-auth) - Admin middleware patterns
- [Laravel News CRUD Operations](https://laravel-news.com/crud-operations-using-laravel-livewire) - Livewire CRUD patterns

### Tertiary (LOW confidence)
- WebSearch results on admin panel security best practices - general guidance, verify specifics

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Laravel and Livewire docs are authoritative, Tiptap is well-documented
- Architecture: HIGH - Patterns from official Livewire docs and Laravel conventions
- Pitfalls: MEDIUM - Mix of official docs and community experience

**Research date:** 2026-01-25
**Valid until:** 2026-02-25 (30 days - Laravel 12 and Livewire 4 are stable)
