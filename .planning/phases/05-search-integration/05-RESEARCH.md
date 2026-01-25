# Phase 5: Search Integration - Research

**Researched:** 2026-01-25
**Domain:** Laravel Scout with Algolia + Livewire Typeahead
**Confidence:** HIGH

## Summary

This phase implements typo-tolerant search using Algolia as the search backend with Laravel Scout Extended as the integration layer, and Livewire 4 for the frontend typeahead component. The stack is well-established and thoroughly documented.

The project already has Livewire 4.0.3 installed with existing class-based components (CommentForm, CommentSection). The Post model has all necessary fields for indexing (title, content, excerpt, slug, published_at, status). Scout Extended provides zero-downtime reimports and automatic index synchronization through model observers.

Key considerations: ~2,500 posts require initial import (well within Algolia's batch limits), typo tolerance is enabled by default in Algolia, and Livewire's `wire:model.live.debounce` provides the typeahead mechanism with minimal code.

**Primary recommendation:** Install Scout Extended, add Searchable trait to Post model, configure searchableAttributes for title/content/excerpt/slug, create a simple Livewire Search component using `wire:model.live.debounce.300ms` with Scout's `::search()` method.

## Standard Stack

The established libraries/tools for this domain:

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| algolia/scout-extended | ^3.0 | Algolia integration for Laravel Scout | Official Algolia package, adds zero-downtime reimports, index optimization, settings management |
| laravel/scout | (bundled) | Full-text search abstraction for Eloquent | Official Laravel package, Scout Extended depends on it |
| livewire/livewire | 4.0.3 (installed) | Reactive UI components | Already installed, handles typeahead with debouncing |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Alpine.js | (bundled with Livewire) | Client-side interactivity | Keyboard navigation in search results dropdown |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Scout Extended | Base Laravel Scout + algolia/algoliasearch-client-php | Lose zero-downtime reimports, scout:optimize, settings management via Artisan |
| Livewire typeahead | Algolia InstantSearch.js | More JavaScript complexity, requires additional build tooling, but provides more advanced UI options |

**Installation:**
```bash
composer require "algolia/scout-extended:^3.0"
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

## Architecture Patterns

### Recommended Project Structure
```
app/
├── Models/
│   └── Post.php              # Add Searchable trait, toSearchableArray, shouldBeSearchable
├── Livewire/
│   └── Search/
│       └── SearchBar.php     # Typeahead search component
config/
├── scout.php                 # Scout configuration with Algolia credentials
├── scout-posts.php           # Index-specific settings (searchableAttributes, etc.)
resources/views/
├── livewire/
│   └── search/
│       └── search-bar.blade.php  # Typeahead UI with dropdown results
```

### Pattern 1: Searchable Model Configuration
**What:** Configure Post model for Algolia indexing with conditional indexing
**When to use:** Every model that needs full-text search
**Example:**
```php
// Source: https://laravel.com/docs/12.x/scout
namespace App\Models;

use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    // Define which fields are indexed
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => strip_tags($this->content),
            'excerpt' => $this->excerpt,
            'slug' => $this->slug,
            'published_at' => $this->published_at?->timestamp,
        ];
    }

    // Only index published posts
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    // Custom index name (optional, adds environment prefix)
    public function searchableAs(): string
    {
        return config('scout.prefix') . 'posts';
    }
}
```

### Pattern 2: Livewire Typeahead Component
**What:** Real-time search with debounced input using Scout's search method
**When to use:** Any instant-results search interface
**Example:**
```php
// Source: https://livewire.laravel.com/docs/3.x/wire-model + Laravel Scout docs
namespace App\Livewire\Search;

use App\Models\Post;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';

    public function render()
    {
        $results = [];

        if (strlen($this->query) >= 2) {
            $results = Post::search($this->query)
                ->take(5)
                ->get();
        }

        return view('livewire.search.search-bar', [
            'results' => $results,
        ]);
    }
}
```

```blade
{{-- Blade template with debounced typeahead --}}
<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <input
        type="search"
        wire:model.live.debounce.300ms="query"
        @focus="open = true"
        placeholder="Search posts..."
        class="w-full px-4 py-2 border rounded-lg"
    >

    @if(strlen($query) >= 2 && count($results) > 0)
        <div class="absolute z-50 w-full mt-1 bg-white border rounded-lg shadow-lg" x-show="open">
            @foreach($results as $post)
                <a href="{{ $post->url }}" class="block px-4 py-2 hover:bg-gray-100">
                    {{ $post->title }}
                </a>
            @endforeach
        </div>
    @endif
</div>
```

### Pattern 3: Index Settings Configuration
**What:** Configure Algolia index settings via Scout Extended config file
**When to use:** After running scout:optimize to generate settings
**Example:**
```php
// config/scout-posts.php (generated by scout:optimize, then customized)
// Source: https://www.algolia.com/doc/framework-integration/laravel/indexing/configure-index
return [
    'searchableAttributes' => [
        'title',
        'content',
        'excerpt',
        'slug',
    ],
    'customRanking' => [
        'desc(published_at)',
    ],
    'attributesForFaceting' => [],
    // Typo tolerance is ON by default - no config needed
];
```

### Anti-Patterns to Avoid
- **Searching on every keystroke without debounce:** Causes excessive API calls; always use `wire:model.live.debounce.300ms` or higher
- **Indexing all model attributes:** Use `toSearchableArray()` to select only searchable fields
- **Indexing unpublished content:** Implement `shouldBeSearchable()` to filter
- **Skipping the queue for indexing:** Set `'queue' => true` in scout.php for production
- **Manual Algolia API calls:** Always use Scout Extended's abstractions

## Don't Hand-Roll

Problems that look simple but have existing solutions:

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Typo tolerance | Custom fuzzy matching algorithm | Algolia's built-in typo tolerance | Algolia handles 1-2 typos automatically, handles phonetic matching, word proximity |
| Search result ranking | Custom relevance scoring | Algolia's ranking formula + customRanking | Battle-tested algorithm, considers typo count, word proximity, attribute position |
| Index synchronization | Manual sync after model changes | Scout model observers | Automatic, handles create/update/delete, queue-able |
| Zero-downtime reimport | Manual index swap logic | Scout Extended's `scout:reimport` | Uses temporary index, atomic swap, no search downtime |
| Debounced typeahead | Custom JavaScript debounce | Livewire's `wire:model.live.debounce.Xms` | Built-in, configurable, handles edge cases |
| Searchable attributes config | Manual Algolia dashboard edits | Scout Extended config files + `scout:sync` | Version controlled, reproducible, environment-specific |

**Key insight:** Algolia + Scout Extended handles all search complexity. The implementation should be thin: configure the model, create the Livewire component, wire up the UI. Any custom search logic beyond this is likely unnecessary.

## Common Pitfalls

### Pitfall 1: Configuration Caching
**What goes wrong:** Changes to .env or config/scout.php don't take effect
**Why it happens:** Laravel caches configuration in production
**How to avoid:** After config changes, run `php artisan config:clear` and `php artisan cache:clear`
**Warning signs:** Index not syncing, credentials not working

### Pitfall 2: Queue Not Processing
**What goes wrong:** Records don't appear in search after saving
**Why it happens:** `'queue' => true` in scout.php but queue worker not running
**How to avoid:** Either run queue worker (`php artisan queue:work`) or set `'queue' => false` during development
**Warning signs:** Records eventually appear after running queue:work

### Pitfall 3: Asynchronous Indexing Delays
**What goes wrong:** Record saved but not immediately searchable
**Why it happens:** Algolia always indexes asynchronously, even with queue disabled
**How to avoid:** Understand this is expected behavior; typically 1-2 seconds delay
**Warning signs:** Tests failing due to timing issues

### Pitfall 4: Record Size Limit (10KB)
**What goes wrong:** Import fails with "Record too big" error
**Why it happens:** Post content exceeds Algolia's 10KB per record limit
**How to avoid:** In `toSearchableArray()`, truncate content field or use `strip_tags()` and `Str::limit()`
**Warning signs:** Error during `scout:import` mentioning record size

### Pitfall 5: Missing Prefix in Custom Index Name
**What goes wrong:** `scout:reimport` doesn't work with custom index names
**Why it happens:** Custom `searchableAs()` doesn't include `config('scout.prefix')`
**How to avoid:** Always return `config('scout.prefix') . 'index_name'` from `searchableAs()`
**Warning signs:** Reimport creates wrong index or fails silently

### Pitfall 6: Dashboard Edits Overwritten
**What goes wrong:** Settings changed in Algolia dashboard get lost
**Why it happens:** Running `scout:sync` pushes local config, overwriting dashboard changes
**How to avoid:** Always make settings changes in config files, then sync
**Warning signs:** Settings reverting after deployments

### Pitfall 7: Excessive API Calls from Typeahead
**What goes wrong:** Algolia bill spikes, slow search experience
**Why it happens:** No debounce on search input, every keystroke triggers API call
**How to avoid:** Use `wire:model.live.debounce.300ms` (300ms minimum recommended)
**Warning signs:** Network tab shows many rapid requests

### Pitfall 8: FIFO Queue Required for Reimport
**What goes wrong:** `scout:reimport` results in missing records (5-10%)
**Why it happens:** Non-FIFO queues (like AWS SQS) process jobs out of order, causing race conditions
**How to avoid:** Use database or Redis queue for reimport, or run reimport synchronously
**Warning signs:** Record count mismatch after reimport

## Code Examples

Verified patterns from official sources:

### Post Model with Searchable Trait
```php
// Source: https://laravel.com/docs/12.x/scout
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    // ... existing code ...

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => \Illuminate\Support\Str::limit(strip_tags($this->content), 5000),
            'slug' => $this->slug,
            'published_at' => $this->published_at?->timestamp,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }
}
```

### Scout Configuration
```php
// config/scout.php
// Source: https://laravel.com/docs/12.x/scout
return [
    'driver' => env('SCOUT_DRIVER', 'algolia'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', true),
    'after_commit' => true,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],
];
```

### Environment Variables
```bash
# .env
SCOUT_DRIVER=algolia
SCOUT_PREFIX=prod_
SCOUT_QUEUE=true
ALGOLIA_APP_ID=your_app_id
ALGOLIA_SECRET=your_admin_api_key
```

### Basic Search Command
```bash
# Import all posts to Algolia
php artisan scout:import "App\Models\Post"

# Zero-downtime reimport (Scout Extended)
php artisan scout:reimport "App\Models\Post"

# Generate optimized settings
php artisan scout:optimize "App\Models\Post"

# Sync settings to Algolia
php artisan scout:sync "App\Models\Post"

# Flush index
php artisan scout:flush "App\Models\Post"
```

### Livewire Search Component (Complete)
```php
// app/Livewire/Search/SearchBar.php
<?php

namespace App\Livewire\Search;

use App\Models\Post;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';
    public bool $showResults = false;

    public function updatedQuery(): void
    {
        $this->showResults = strlen($this->query) >= 2;
    }

    public function selectResult(): void
    {
        $this->showResults = false;
    }

    public function render()
    {
        $results = collect();

        if (strlen($this->query) >= 2) {
            $results = Post::search($this->query)
                ->take(5)
                ->get();
        }

        return view('livewire.search.search-bar', [
            'results' => $results,
        ]);
    }
}
```

```blade
{{-- resources/views/livewire/search/search-bar.blade.php --}}
<div
    class="relative"
    x-data="{
        open: @entangle('showResults'),
        activeIndex: -1,
        results: [],
    }"
    @keydown.arrow-down.prevent="activeIndex = Math.min(activeIndex + 1, results.length - 1)"
    @keydown.arrow-up.prevent="activeIndex = Math.max(activeIndex - 1, 0)"
    @keydown.enter.prevent="if(activeIndex >= 0) window.location = results[activeIndex].url"
    @keydown.escape="open = false"
>
    <input
        type="search"
        wire:model.live.debounce.300ms="query"
        @focus="open = $wire.query.length >= 2"
        @click.away="open = false"
        placeholder="Search posts..."
        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        autocomplete="off"
    >

    <div
        x-show="open && {{ count($results) }} > 0"
        x-transition
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden"
    >
        @foreach($results as $index => $post)
            <a
                href="{{ $post->url }}"
                wire:click="selectResult"
                class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0"
                :class="{ 'bg-gray-100': activeIndex === {{ $index }} }"
            >
                <div class="font-medium text-gray-900">{{ $post->title }}</div>
                @if($post->excerpt)
                    <div class="text-sm text-gray-500 truncate">{{ Str::limit($post->excerpt, 80) }}</div>
                @endif
            </a>
        @endforeach
    </div>

    {{-- No results state --}}
    <div
        x-show="open && {{ count($results) }} === 0 && $wire.query.length >= 2"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-gray-500 text-center"
    >
        No posts found for "{{ $query }}"
    </div>
</div>
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| algolia/laravel-scout-settings | algolia/scout-extended | 2019 | Use Scout Extended for all Algolia + Laravel projects |
| algolia/laravel-scout-algolia-macros | algolia/scout-extended | 2019 | Deprecated, migrate to Scout Extended |
| wire:model.debounce (Livewire 2) | wire:model.live.debounce (Livewire 3+) | Livewire 3.0 | New modifier syntax, .live required for real-time updates |
| Manual index settings via dashboard | scout:optimize + scout:sync | Scout Extended | Config files are version-controlled, reproducible |

**Deprecated/outdated:**
- `algolia/algoliasearch-laravel`: Use Scout Extended instead
- `algolia/laravel-scout-settings`: Deprecated, Scout Extended replaces it
- `wire:model.debounce.300ms` without `.live`: In Livewire 3+/4, requires `.live` modifier for real-time updates

## Livewire 4 Specific Notes

The project has Livewire 4.0.3 installed. Key considerations:

**Compatibility:**
- Livewire 4 maintains backward compatibility with class-based components
- Existing CommentForm/CommentSection patterns can be followed
- `wire:model.live.debounce.Xms` syntax works unchanged from v3

**New Features Available (optional):**
- Single-file components (`.wire.php`) are now default for new components
- Islands for isolated rendering (not needed for search)
- `wire:ref` for JavaScript targeting
- View Transitions API via `wire:transition`

**Recommended Approach:**
- Use class-based component (matches existing project style)
- Standard `wire:model.live.debounce.300ms` for typeahead
- Alpine.js for dropdown/keyboard navigation (bundled with Livewire)

## Open Questions

Things that couldn't be fully resolved:

1. **Algolia Free Tier Limits**
   - What we know: Algolia has a free Community plan
   - What's unclear: Exact limits for records and operations on current plans
   - Recommendation: Check Algolia dashboard during implementation for current limits; 2,500 posts should be well within free tier

2. **Content Truncation Strategy**
   - What we know: 10KB record limit exists; content should be truncated in toSearchableArray
   - What's unclear: Optimal truncation length for this blog's content
   - Recommendation: Start with 5,000 characters after strip_tags; adjust if search quality suffers

3. **Search Results Page vs Typeahead Only**
   - What we know: Requirement SRCH-01 specifies typeahead with instant results
   - What's unclear: Whether a full search results page is also needed
   - Recommendation: Implement typeahead first; full search page can be added later using same Scout query

## Sources

### Primary (HIGH confidence)
- [Laravel Scout 12.x Documentation](https://laravel.com/docs/12.x/scout) - Complete Scout API, configuration, Algolia setup
- [Scout Extended GitHub](https://github.com/algolia/scout-extended) - v3.2.2, installation, features
- [Algolia Scout Extended Installation](https://www.algolia.com/doc/framework-integration/laravel/getting-started/installation) - Requirements, setup steps
- [Livewire wire:model Documentation](https://livewire.laravel.com/docs/3.x/wire-model) - Debounce, live updates
- [Livewire 4 Upgrade Guide](https://livewire.laravel.com/docs/4.x/upgrading) - Breaking changes, compatibility

### Secondary (MEDIUM confidence)
- [Algolia Configure Index](https://www.algolia.com/doc/framework-integration/laravel/indexing/configure-index) - searchableAttributes, customRanking
- [Laravel News - Livewire 4 Features](https://laravel-news.com/everything-new-in-livewire-4) - New features overview
- [Algolia Large Indexing Jobs](https://www.algolia.com/doc/guides/scaling/scaling-to-larger-datasets) - Batch sizes, limits

### Tertiary (LOW confidence)
- Community tutorials on Livewire + Scout integration (patterns verified against official docs)
- GitHub discussions on scout:reimport race conditions

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - Official packages, well-documented
- Architecture: HIGH - Follows established Laravel/Livewire patterns
- Pitfalls: HIGH - From official FAQ and documented issues
- Code examples: HIGH - Adapted from official documentation

**Research date:** 2026-01-25
**Valid until:** 2026-02-25 (30 days - stable ecosystem)
