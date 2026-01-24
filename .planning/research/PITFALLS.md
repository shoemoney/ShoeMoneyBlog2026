# Domain Pitfalls: WordPress to Laravel Migration

**Domain:** WordPress to Laravel Blog Migration (20+ years of content)
**Researched:** 2026-01-24
**Context:** 1.3GB SQL export, Livewire frontend, Algolia search, nested comments, multiple authors

## Critical Pitfalls

Mistakes that cause rewrites, major SEO loss, or data corruption.

---

### Pitfall 1: URL Structure Breaking SEO

**What goes wrong:**
WordPress uses predefined permalink structures (`/year/month/postname/`, `/category/postname/`, etc.) while Laravel uses custom, developer-defined routing patterns. After migration, old URLs break, causing 404s for all indexed pages, leading to immediate and catastrophic SEO ranking loss.

**Why it happens:**
Developers focus on building the new Laravel application without mapping the exact URL patterns from WordPress. They assume they can "just add redirects later" but underestimate the complexity of preserving 20 years of varied URL structures.

**Consequences:**
- Rankings disappear overnight (potentially 70-90% traffic loss within weeks)
- Years of accumulated link equity is lost
- Clients panic, business revenue drops
- Emergency rollback becomes necessary
- Trust in the migration project is destroyed

**Prevention:**
1. **Before starting migration:** Export ALL URLs from WordPress using SQL:
   ```sql
   SELECT CONCAT('https://yourdomain.com', '/', post_name) as url,
          post_type,
          post_status,
          post_date
   FROM wp_posts
   WHERE post_status = 'publish'
   ORDER BY post_date DESC;
   ```

2. **Map WordPress permalink structure to Laravel routes EXACTLY:**
   ```php
   // If WordPress used /%year%/%monthnum%/%postname%/
   Route::get('/{year}/{month}/{slug}', [PostController::class, 'show'])
       ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{2}']);

   // If WordPress used /%category%/%postname%/
   Route::get('/{category}/{slug}', [PostController::class, 'showByCategory']);
   ```

3. **Create a URL verification script** that tests every WordPress URL against your new Laravel routing before launch

4. **Implement 301 redirects** ONLY for URLs that MUST change structure, never as a blanket solution

5. **Add meta canonical tags** to prevent duplicate content issues during transition

**Detection:**
- Run `php artisan route:list` and compare with WordPress URL export
- Use Screaming Frog or similar crawler to test all URLs in staging
- Monitor Google Search Console for 404 errors spike
- Check for "noindex" tags accidentally left in place
- Traffic analytics show sudden organic traffic drop

**Phase impact:**
- Phase 1 (Data Migration): Document ALL URL patterns
- Phase 2 (Routing Setup): Implement exact URL pattern matching
- Phase 3 (Pre-Launch): URL verification testing (MANDATORY gate)

**Confidence:** HIGH (verified from multiple migration case studies)

**Sources:**
- [Laravel to WordPress Migration SEO challenges](https://creativetweed.co.uk/laravel-to-wordpress-migration/)
- [WordPress to Laravel migration guide](https://dmwebsoft.com/the-ultimate-guide-to-migrating-from-wordpress-to-laravel-step-by-step)
- [Migration SEO pitfalls](https://sampression.com/laravel-to-wordpress-migration/)

---

### Pitfall 2: Importing 1.3GB Database Without Chunking/Streaming

**What goes wrong:**
Attempting to load a 1.3GB SQL file directly into memory or process it in a single database transaction causes PHP memory exhaustion (Fatal error: Allowed memory size exhausted), timeouts, or partial imports that corrupt data integrity.

**Why it happens:**
Default PHP memory limit is 128MB-256MB. A 1.3GB SQL file cannot fit in memory. Developers try `DB::unprepared(file_get_contents('export.sql'))` or phpMyAdmin imports which fail silently or timeout after 60 seconds.

**Consequences:**
- Import stops mid-way, leaving partial data (e.g., 5,000 of 50,000 posts)
- Foreign key constraints break because referenced records don't exist
- No clear error message—just "Import complete" with missing data
- Data integrity issues cascade (orphaned comments, broken category relationships)
- Must start over, wasting hours/days

**Prevention:**

1. **Use MySQL CLI with progress monitoring** (most reliable):
   ```bash
   pv shoemoney-blog-export.sql | mysql -u username -p database_name
   # pv shows progress bar and ETA
   ```

2. **Split the SQL file into chunks** if MySQL CLI isn't available:
   ```bash
   # Split into 100MB chunks
   split -b 100m shoemoney-blog-export.sql chunk_

   # Import sequentially
   for file in chunk_*; do
       mysql -u username -p database_name < $file
   done
   ```

3. **For Laravel seeder approach, use streaming and chunking:**
   ```php
   // DON'T DO THIS:
   // $sql = file_get_contents('export.sql'); // Loads 1.3GB into RAM

   // DO THIS:
   $handle = fopen('export.sql', 'r');
   $statement = '';

   while (($line = fgets($handle)) !== false) {
       if (trim($line) === '' || strpos($line, '--') === 0) continue;

       $statement .= $line;

       if (substr(trim($line), -1) === ';') {
           DB::unprepared($statement);
           $statement = '';
       }
   }
   fclose($handle);
   ```

4. **Increase PHP limits** (temporary measure, not a solution):
   ```ini
   memory_limit = 2G
   max_execution_time = 3600
   post_max_size = 2G
   upload_max_filesize = 2G
   ```

5. **Use a migration tool** designed for large datasets:
   - WP CLI for export: `wp db export large-export.sql --max_allowed_packet=512M`
   - Laravel migration in chunks with progress bar

**Detection:**
- PHP error logs show "Allowed memory size of X bytes exhausted"
- Import script times out after 60-300 seconds
- Database shows partial data (check `SELECT COUNT(*) FROM posts`)
- Foreign key errors during application testing
- "Class 'PDOException' not found" or connection timeout errors

**Phase impact:**
- Phase 1 (Data Import): CRITICAL—use chunked import from day one
- Phase 2 (Data Verification): Count validation (WordPress counts vs Laravel counts)
- Testing: Automated data integrity checks before any coding starts

**Confidence:** HIGH (documented in WordPress migration forums)

**Sources:**
- [Migration problems with large databases](https://wordpress.org/support/topic/migration-problems-large-database/)
- [Stuck on restoring database](https://blogvault.net/all-in-one-wp-migration-stuck-on-restoring-database/)
- [WordPress large database scaling](https://sandersdesign.com/blog/scaling-up-can-wordpress-handle-large-databases/)

---

### Pitfall 3: WordPress Password Hash Incompatibility

**What goes wrong:**
WordPress uses phpass (PHPass) hashing algorithm for passwords (stored as `$P$B...` format), which is incompatible with Laravel's bcrypt hashing. After migration, NO users can log in with their existing passwords because Laravel's `Hash::check()` fails validation.

**Why it happens:**
Developers migrate the `wp_users` table directly to a `users` table without addressing password hash format differences. They assume "passwords are just hashed strings" not realizing WordPress and Laravel use completely different algorithms.

**Consequences:**
- All 20+ years of user accounts become inaccessible
- Forced password reset for ALL users (extremely poor UX)
- Users who no longer have access to their registration email are locked out permanently
- Support requests flood in on launch day
- Reputation damage: "The new site won't let me log in"

**Prevention:**

**Option 1: Hybrid Password Verification** (RECOMMENDED—preserves existing passwords)
```php
// app/Models/User.php
use Illuminate\Support\Facades\Hash;

public function validatePassword(string $password): bool
{
    // Check if password is WordPress format ($P$B...)
    if (substr($this->password, 0, 3) === '$P$') {
        // Use WordPress password verification
        if ($this->checkWordPressPassword($password, $this->password)) {
            // UPGRADE to Laravel bcrypt on successful login
            $this->password = Hash::make($password);
            $this->save();
            return true;
        }
        return false;
    }

    // Standard Laravel bcrypt check
    return Hash::check($password, $this->password);
}

private function checkWordPressPassword(string $password, string $hash): bool
{
    // Use WordPress phpass library
    require_once 'path/to/PasswordHash.php';
    $hasher = new \PasswordHash(8, true);
    return $hasher->CheckPassword($password, $hash);
}
```

**Option 2: Force Password Reset** (EASIER but terrible UX)
```php
// In migration seeder
DB::table('users')->update([
    'password' => null,
    'must_reset_password' => true,
    'reset_token' => Str::random(60)
]);

// Send password reset emails to all users (may hit email limits)
```

**Option 3: Use WordPress Corcel Bridge** (if keeping WordPress database)
```php
// This allows reading WordPress data directly with Laravel models
// But doesn't solve the migration problem
```

**Best Practice Implementation:**
1. Migrate WordPress passwords AS-IS (don't touch the hash)
2. Add `wordpress_password` boolean column to track which users need hybrid validation
3. Implement hybrid password checking (attempt WordPress hash first, then Laravel)
4. Gradually upgrade passwords to bcrypt as users log in
5. After 6-12 months, force reset for remaining WordPress-hashed passwords

**Detection:**
- No users can log in immediately after migration
- Auth logs show "password check failed" for valid credentials
- Users report "Invalid credentials" with correct passwords
- Password reset requests spike to 100% of user base

**Phase impact:**
- Phase 1 (User Migration): Implement hybrid password validation BEFORE importing users
- Phase 2 (Auth System): Test password verification with actual WordPress hashed passwords
- Phase 3 (Launch): Monitor login success rates, have password reset ready

**Confidence:** HIGH (documented in WordPress-Laravel migration guides)

**Sources:**
- [Migrating WordPress users and passwords to Laravel](https://gist.github.com/Yiannistaos/b32db7af83b5ea7c9e981d027690b336)
- [WordPress to Laravel migration guide](https://medium.com/@hosnyben/migrating-a-7-year-old-wordpress-business-to-laravel-bf9f11542fc1)

---

### Pitfall 4: Livewire Performance Degradation with Large Comment Threads

**What goes wrong:**
Livewire sends the ENTIRE component state (all properties) in the wire:snapshot on every request. With nested comments (parent + replies), a single blog post with 500+ comments creates massive payloads (500KB-2MB per request). When users interact with comments (reply, like, etc.), the page becomes unusably slow—7 to 60 seconds per action.

**Why it happens:**
Developers declare comment collections as public properties in Livewire components. Livewire serializes ALL public properties to JSON and sends them to the browser on every render, then deserializes them on every action. With 20 years of comments, popular posts have thousands of comments.

**Consequences:**
- Blog posts with many comments take 30-60 seconds to load
- Every comment interaction (reply, edit, like) triggers a 5-30 second delay
- Browser DevTools shows "waiting for response" for 20+ seconds, but server responds in 200ms
- Users abandon the page, thinking it's broken
- Server RAM usage spikes as Livewire hydrates/dehydrates massive objects
- "This new site is way slower than WordPress" feedback

**Prevention:**

**1. NEVER store collections in public properties:**
```php
// ❌ WRONG: This serializes all comments on every request
class BlogPost extends Component
{
    public $comments; // Collection of 500 comments = 1MB payload

    public function mount($postId)
    {
        $this->comments = Comment::where('post_id', $postId)->get();
    }
}

// ✅ CORRECT: Use computed properties (not serialized)
class BlogPost extends Component
{
    public $postId;

    public function getCommentsProperty()
    {
        return Comment::where('post_id', $this->postId)
            ->with('author')
            ->latest()
            ->paginate(20); // Pagination crucial
    }
}

// In Blade: {{ $this->comments }}
```

**2. Implement pagination/lazy loading for comments:**
```php
public function getCommentsProperty()
{
    return Comment::where('post_id', $this->postId)
        ->whereNull('parent_id') // Top-level only
        ->with(['replies' => function($query) {
            $query->limit(3); // Show 3 replies, "Load more" for rest
        }])
        ->latest()
        ->paginate(10);
}
```

**3. Use wire:poll with extreme caution (or never):**
```blade
{{-- ❌ WRONG: Polls every 150ms with full payload --}}
<div wire:poll.150ms>
    {{ $comments->count() }} comments
</div>

{{-- ✅ CORRECT: Use events for real-time updates --}}
<div wire:poll.60s="refreshCommentCount"> {{-- Only count, not full data --}}
    {{ $commentCount }} comments
</div>
```

**4. Debounce user input aggressively:**
```blade
{{-- ❌ WRONG: Fires request every 150ms during typing --}}
<input wire:model="commentBody">

{{-- ✅ CORRECT: Wait 500ms after user stops typing --}}
<input wire:model.debounce.500ms="commentBody">

{{-- ✅ EVEN BETTER: Only sync on blur --}}
<input wire:model.blur="commentBody">
```

**5. Dispatch events from Blade, not PHP:**
```php
// ❌ WRONG: Dispatching from PHP triggers extra request
public function likeComment($commentId)
{
    Comment::find($commentId)->increment('likes');
    $this->dispatch('comment-liked'); // Extra round-trip
}

// ✅ CORRECT: Dispatch directly from Blade after action
// In component:
public function likeComment($commentId)
{
    Comment::find($commentId)->increment('likes');
}

// In Blade:
<button wire:click="likeComment({{ $comment->id }})"
        x-on:click="$dispatch('comment-liked')">
    Like
</button>
```

**6. Consider extracting nested comments to separate components:**
```php
// Parent component shows post + top-level comments
class BlogPost extends Component
{
    public $postId;

    public function getTopLevelCommentsProperty()
    {
        return Comment::where('post_id', $this->postId)
            ->whereNull('parent_id')
            ->latest()
            ->paginate(10);
    }
}

// Child component handles individual comment threads
class CommentThread extends Component
{
    public $commentId;
    public $showReplies = false;

    public function getRepliesProperty()
    {
        if (!$this->showReplies) return collect();

        return Comment::where('parent_id', $this->commentId)
            ->latest()
            ->get();
    }
}
```

**Detection:**
- Browser DevTools Network tab shows 500KB+ payloads on Livewire requests
- "Low Performance in Version 3" discussions appear in testing
- Users report "spinning wheel" lasting 10+ seconds on comment actions
- Server responds in <500ms but browser takes 20+ seconds to update
- Wire:snapshot in page source is 100+ KB of JSON

**Phase impact:**
- Phase 1 (Architecture): Design comment loading strategy (pagination + lazy load)
- Phase 2 (Livewire Components): Use computed properties exclusively for collections
- Phase 3 (Performance Testing): Test with actual high-comment posts (500+ comments)
- Phase 4 (Optimization): Implement query caching for computed properties

**Confidence:** HIGH (verified from Livewire GitHub discussions)

**Sources:**
- [Livewire runs really slowly](https://github.com/livewire/livewire/discussions/4492)
- [Speed Up Livewire V3](https://medium.com/@thenibirahmed/speed-up-livewire-v3-the-only-guide-you-need-32fe73338098)
- [Livewire performance tips](https://joelmale.com/blog/laravel-livewire-performance-tips-tricks)
- [Low Performance in Version 3](https://github.com/livewire/livewire/discussions/6052)

---

### Pitfall 5: Algolia/Scout Initial Import Without Queueing

**What goes wrong:**
Running `php artisan scout:import "App\Models\Post"` on 20 years of posts (potentially 10,000-50,000 records) sends synchronous HTTP requests to Algolia for EVERY record. This blocks the process for hours, hitting PHP execution time limits (default 60s), causing the import to fail midway with no clear resume point.

**Why it happens:**
Developers follow basic Scout documentation without configuring queue workers. They run the import command synchronously, assuming it's like a database seeder. With large datasets, this means 50,000 HTTP requests executed sequentially in a single PHP process.

**Consequences:**
- Import times out after 60-300 seconds (only 1,000-2,000 posts indexed)
- No resume capability—must start over from scratch
- Algolia receives partial data, search returns incomplete results
- Production launch blocked waiting for import to complete
- Server becomes unresponsive during import attempts (all resources consumed)
- Confusion about which records actually made it to Algolia index

**Prevention:**

**1. Configure queue workers BEFORE importing:**
```php
// config/scout.php
'queue' => [
    'connection' => 'redis', // or 'database' for smaller scale
    'queue' => 'scout',
],
```

```bash
# Start dedicated Scout queue worker
php artisan queue:work --queue=scout --tries=3
```

**2. Use chunked imports with queue:**
```bash
# Import in chunks (processes 500 at a time)
php artisan scout:import "App\Models\Post" --chunk=500

# Queue the import job instead of running synchronously
php artisan scout:queue-import "App\Models\Post" --chunk=500
```

**3. Monitor import progress:**
```php
// Create a custom command with progress tracking
use Illuminate\Console\Command;

class ImportPostsToAlgolia extends Command
{
    public function handle()
    {
        $total = Post::count();
        $bar = $this->output->createProgressBar($total);

        Post::chunk(500, function($posts) use ($bar) {
            $posts->searchable(); // Queues 500 records
            $bar->advance(500);
        });

        $bar->finish();
        $this->info("\nQueued {$total} posts for indexing");
    }
}
```

**4. Handle import failures gracefully:**
```php
// In AppServiceProvider or dedicated Scout config
Scout::makeSearchableUsing(function ($models) {
    try {
        $models->searchable();
    } catch (\Exception $e) {
        Log::error('Scout indexing failed', [
            'model' => get_class($models->first()),
            'ids' => $models->pluck('id')->toArray(),
            'error' => $e->getMessage()
        ]);
        throw $e; // Re-throw to trigger queue retry
    }
});
```

**5. Verify Algolia received all records:**
```php
// After import, verify counts match
$laravelCount = Post::count();
$algoliaCount = Post::search('')->get()->count(); // Empty search returns all

if ($laravelCount !== $algoliaCount) {
    $this->warn("Mismatch: Laravel has {$laravelCount}, Algolia has {$algoliaCount}");
}
```

**6. Be aware of asynchronous indexing:**
```php
// ❌ WRONG: Search immediately after creating
$post = Post::create([...]);
$results = Post::search($post->title)->get(); // May not find it yet

// ✅ CORRECT: Understand indexing is async (even with queue=false)
$post = Post::create([...]);
// Algolia indexes asynchronously (may take 1-5 seconds)
// Don't expect immediate search results
```

**7. Optimize toSearchableArray to reduce payload:**
```php
// ❌ WRONG: Sending unnecessary data increases indexing time
public function toSearchableArray()
{
    return $this->toArray(); // Sends ALL fields including blobs
}

// ✅ CORRECT: Only send searchable fields
public function toSearchableArray()
{
    return [
        'id' => (string) $this->id, // Cast to string for Algolia
        'title' => $this->title,
        'excerpt' => $this->excerpt,
        'author' => $this->author->name,
        'category' => $this->category->name,
        'published_at' => $this->published_at->timestamp,
        // DON'T include full post content if not needed for search
    ];
}
```

**Detection:**
- Import command times out with "Maximum execution time exceeded"
- `scout:import` appears to complete but search returns no/partial results
- Algolia dashboard shows record count doesn't match database count
- Server logs show thousands of "Indexing..." messages then timeout
- Queue worker logs show failed jobs with Algolia API errors

**Phase impact:**
- Phase 1 (Infrastructure Setup): Configure Redis/database queue FIRST
- Phase 2 (Scout Configuration): Set up queue workers before any import
- Phase 3 (Initial Import): Use chunked, queued import with monitoring
- Phase 4 (Verification): Automated count verification (Laravel vs Algolia)
- Phase 5 (Ongoing): Queue workers running in production for real-time indexing

**Confidence:** HIGH (verified from Laravel Scout documentation and Algolia guides)

**Sources:**
- [Laravel Scout documentation on queueing](https://laravel.com/docs/12.x/scout)
- [Algolia Laravel integration guide](https://www.algolia.com/doc/framework-integration/laravel/tutorials/getting-started-with-laravel-scout-vuejs)
- [Scout extended FAQ](https://www.algolia.com/doc-beta/framework-integration/laravel/troubleshooting/faq)

---

### Pitfall 6: WordPress Shortcodes in Migrated Content Break Rendering

**What goes wrong:**
WordPress posts contain shortcodes like `[gallery ids="1,2,3"]`, `[caption]`, `[embed]`, or custom plugin shortcodes. After migration, these shortcodes appear as literal text in the rendered blog posts because Laravel doesn't have WordPress's shortcode parser. Posts look broken with `[gallery ids="1,2,3"]` showing instead of actual galleries.

**Why it happens:**
20 years of WordPress content accumulated thousands of shortcode instances. Developers migrate post content directly to Laravel without considering shortcode parsing. They discover the issue only after importing content and viewing posts in production.

**Consequences:**
- All posts with shortcodes display broken formatting (literal shortcode text)
- Gallery posts show `[gallery]` text instead of images
- Embedded content (YouTube, Twitter) doesn't render—shows `[embed]` text
- Custom plugin shortcodes (contact forms, pricing tables) completely non-functional
- Manual editing of thousands of posts is not feasible
- Content looks unprofessional and broken

**Prevention:**

**Option 1: Parse and convert shortcodes during migration** (RECOMMENDED)
```php
// Create a shortcode converter during data import
class WordPressContentConverter
{
    public function convertShortcodes(string $content): string
    {
        // Gallery shortcode: [gallery ids="1,2,3"]
        $content = preg_replace_callback(
            '/\[gallery ids="([0-9,]+)"\]/',
            function ($matches) {
                $imageIds = explode(',', $matches[1]);
                return $this->renderGalleryHtml($imageIds);
            },
            $content
        );

        // Caption shortcode: [caption]<img> caption text[/caption]
        $content = preg_replace_callback(
            '/\[caption.*?\](.*?)\[\/caption\]/s',
            function ($matches) {
                return $this->renderCaptionHtml($matches[1]);
            },
            $content
        );

        // Embed shortcode: [embed]URL[/embed]
        $content = preg_replace_callback(
            '/\[embed\](.*?)\[\/embed\]/s',
            function ($matches) {
                return $this->renderEmbedHtml($matches[1]);
            },
            $content
        );

        return $content;
    }

    private function renderGalleryHtml(array $imageIds): string
    {
        // Fetch WordPress attachment URLs and render modern gallery
        $images = WordPressAttachment::whereIn('id', $imageIds)->get();

        return view('components.gallery', ['images' => $images])->render();
    }
}

// Use during migration seeder:
foreach ($wordpressPosts as $wpPost) {
    Post::create([
        'title' => $wpPost->post_title,
        'content' => $converter->convertShortcodes($wpPost->post_content),
        // ...
    ]);
}
```

**Option 2: Use a Laravel shortcode parser package** (runtime parsing)
```bash
composer require webwizo/laravel-shortcodes
```

```php
// app/Shortcodes/GalleryShortcode.php
use Webwizo\Shortcodes\Shortcode;

class GalleryShortcode extends Shortcode
{
    public $description = 'WordPress gallery shortcode';

    public function handle($content, $atts = [])
    {
        $imageIds = explode(',', $atts['ids'] ?? '');
        $images = Media::whereIn('id', $imageIds)->get();

        return view('shortcodes.gallery', compact('images'))->render();
    }
}

// Register shortcode:
Shortcode::register('gallery', GalleryShortcode::class);

// In Blade template:
{!! Shortcode::compile($post->content) !!}
```

**Option 3: Identify and manually migrate critical shortcodes**
```php
// Find all unique shortcodes in your content
$shortcodes = [];
foreach (Post::all() as $post) {
    preg_match_all('/\[([a-z_]+)/', $post->content, $matches);
    $shortcodes = array_merge($shortcodes, $matches[1]);
}
$uniqueShortcodes = array_unique($shortcodes);

// Output: ['gallery', 'caption', 'embed', 'contact-form-7', 'pricing-table']
// Prioritize which ones to support vs. strip out
```

**Option 4: Strip shortcodes entirely** (if content still makes sense)
```php
public function stripShortcodes(string $content): string
{
    // Remove all shortcodes but preserve content inside them
    $content = preg_replace('/\[([a-z_]+)([^\]]*)\](.*?)\[\/\1\]/s', '$3', $content);

    // Remove self-closing shortcodes
    $content = preg_replace('/\[([a-z_]+)([^\]]*)\]/', '', $content);

    return $content;
}
```

**Detection:**
- Posts display literal `[shortcode]` text when viewed
- Galleries show bracket text instead of images
- WordPress content preview looks normal, Laravel version shows brackets
- Search for `[` in post content reveals thousands of shortcode instances
- User feedback: "Where did all the galleries go?"

**Phase impact:**
- Phase 1 (Content Analysis): Identify all shortcode types in WordPress export
- Phase 2 (Conversion Strategy): Decide which shortcodes to convert/support/strip
- Phase 3 (Data Migration): Implement shortcode conversion during import
- Phase 4 (Testing): Verify high-usage posts render correctly
- Alternative: Phase 2 (Runtime Parsing): Install shortcode parser if converting during migration isn't feasible

**Confidence:** MEDIUM (based on Laravel shortcode packages and WordPress migration discussions)

**Sources:**
- [Laravel shortcodes package](https://github.com/webwizo/laravel-shortcodes)
- [WordPress shortcode Laravel integration](https://vedmant.com/laravel-shortcodes/)
- [Corcel shortcode handling](https://github.com/corcel/corcel)

---

## Moderate Pitfalls

Mistakes that cause delays, technical debt, or require rework.

---

### Pitfall 7: Comment Thread Nesting Schema Mismatch

**What goes wrong:**
WordPress stores comment threading using `comment_parent` (direct parent reference), but developers implement Laravel comments with only `post_id` foreign key, losing all reply threading. All comments appear as flat list instead of nested conversations.

**Why it happens:**
Initial migration focuses on getting comments imported, overlooking the `comment_parent` column. Developers create a simple `comments` table with `post_id` but no `parent_id`, only discovering the threading loss when viewing posts.

**Prevention:**
```php
// Migration: Preserve WordPress comment structure
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
    $table->text('content');
    $table->enum('status', ['pending', 'approved', 'spam'])->default('pending');
    $table->timestamps();
    $table->softDeletes(); // For moderation
});

// Model: Implement self-referencing relationship
class Comment extends Model
{
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

// Import mapping:
$wpComment->comment_parent => Laravel $comment->parent_id
```

**Detection:**
- All comments display in flat list regardless of reply structure
- Comment count matches but no threading visible
- WordPress preview shows nested comments, Laravel shows flat list

**Sources:**
- [Laravel nested comments tutorial](https://dev.to/techdurjoy/laravel-8-x-multilevel-nested-comments-system-tutorial-41a2)
- [BeyondCode Laravel Comments](https://github.com/beyondcode/laravel-comments)

---

### Pitfall 8: Author Permissions and Role Mapping Loss

**What goes wrong:**
WordPress has complex role hierarchy (Administrator, Editor, Author, Contributor, Subscriber) with granular capabilities. Migration creates a simple `users` table without preserving roles, causing permission chaos—former Contributors can now edit all posts, or Authors lose publishing rights.

**Why it happens:**
WordPress stores roles in `wp_usermeta` table as serialized PHP arrays. Developers import `wp_users` table but ignore `wp_usermeta`, losing all role and capability data.

**Prevention:**
```php
// Migration: Map WordPress roles to Laravel
$wpRoleMapping = [
    'administrator' => 'admin',
    'editor' => 'editor',
    'author' => 'author',
    'contributor' => 'contributor',
    'subscriber' => 'user',
];

// During user import:
foreach ($wpUsers as $wpUser) {
    $wpRoles = unserialize($wpUserMeta->meta_value); // wp_capabilities
    $wpRole = key($wpRoles); // First role

    $user = User::create([
        'name' => $wpUser->display_name,
        'email' => $wpUser->user_email,
        'role' => $wpRoleMapping[$wpRole] ?? 'user',
    ]);

    // Or use Spatie Laravel Permission:
    $user->assignRole($wpRoleMapping[$wpRole] ?? 'user');
}
```

**Detection:**
- Users complain they can't edit posts they previously could
- Wrong users have admin access
- Permission errors in application logs

**Sources:**
- [WordPress to Laravel user migration](https://gist.github.com/Yiannistaos/b32db7af83b5ea7c9e981d027690b336)

---

### Pitfall 9: Media Files Not Migrated, Only Database References

**What goes wrong:**
Developers migrate the `wp_posts` table (which includes attachments as post_type='attachment') but don't copy actual media files from `/wp-content/uploads/`. Result: All image URLs in posts return 404s, broken images everywhere.

**Why it happens:**
Focus on database migration, forgetting that WordPress stores files separately in filesystem. The database only contains file paths, not the files themselves.

**Prevention:**
```bash
# Copy WordPress uploads directory to Laravel storage
cp -r /path/to/wordpress/wp-content/uploads /path/to/laravel/storage/app/public/uploads

# Create symbolic link
php artisan storage:link

# Update image URLs in post content during migration
UPDATE posts
SET content = REPLACE(content,
    'https://old-wordpress-site.com/wp-content/uploads',
    'https://new-laravel-site.com/storage/uploads'
);
```

Alternatively:
```php
// Migrate to S3 during import
foreach (Media::all() as $media) {
    $wpPath = "/wp-content/uploads/{$media->path}";
    $contents = file_get_contents("https://old-site.com{$wpPath}");

    Storage::disk('s3')->put("uploads/{$media->path}", $contents);

    $media->update([
        'url' => Storage::disk('s3')->url("uploads/{$media->path}")
    ]);
}
```

**Detection:**
- All images show broken image icons
- Browser console shows 404 errors for image URLs
- Media library empty despite post content referencing images

**Sources:**
- [Spatie Laravel Media Library](https://github.com/spatie/laravel-medialibrary)
- [WordPress to Laravel migration guide](https://dmwebsoft.com/the-ultimate-guide-to-migrating-from-wordpress-to-laravel-step-by-step)

---

### Pitfall 10: SEO Meta Fields (Yoast/RankMath) Not Migrated

**What goes wrong:**
WordPress SEO plugins store meta titles, descriptions, Open Graph tags, and schema markup in `wp_postmeta`. Migration imports posts but loses all SEO metadata, causing:
- Generic meta descriptions (first 160 chars of post)
- Missing custom meta titles (uses post title instead)
- Lost Open Graph images and descriptions
- Broken schema markup

**Why it happens:**
Developers focus on visible content (title, body) but ignore `wp_postmeta` table where Yoast/RankMath store SEO data with meta keys like `_yoast_wpseo_title`, `_yoast_wpseo_metadesc`.

**Prevention:**
```php
// During post migration, fetch SEO meta from wp_postmeta
foreach ($wpPosts as $wpPost) {
    $yoastTitle = $wpPostMeta->where('meta_key', '_yoast_wpseo_title')->first()?->meta_value;
    $yoastDesc = $wpPostMeta->where('meta_key', '_yoast_wpseo_metadesc')->first()?->meta_value;
    $ogImage = $wpPostMeta->where('meta_key', '_yoast_wpseo_opengraph-image')->first()?->meta_value;

    Post::create([
        'title' => $wpPost->post_title,
        'content' => $wpPost->post_content,
        'meta_title' => $yoastTitle ?? $wpPost->post_title,
        'meta_description' => $yoastDesc ?? Str::limit(strip_tags($wpPost->post_content), 160),
        'og_image' => $ogImage,
    ]);
}

// Add SEO columns to posts migration:
Schema::table('posts', function (Blueprint $table) {
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    $table->string('og_image')->nullable();
    $table->json('schema_markup')->nullable();
});
```

**Detection:**
- Google Search Console shows changed meta descriptions
- Social sharing shows wrong/missing images
- Structured data warnings increase in Search Console
- Meta tags in page source are generic instead of custom

**Sources:**
- [WordPress migration SEO considerations](https://wpexperts.io/blog/wordpress-migration-mistakes/)

---

### Pitfall 11: Forgetting WordPress Redirects and Custom Rewrites

**What goes wrong:**
WordPress sites often have custom redirects (via Redirection plugin or .htaccess) that aren't captured in the database. After migration, old marketing URLs, renamed categories, or consolidated pages return 404s instead of redirecting.

**Why it happens:**
Redirects live in `wp_options` (Redirection plugin) or `.htaccess` file, separate from post data. Developers only migrate posts/pages, not redirect rules.

**Prevention:**
```bash
# Export Redirection plugin redirects
# In WordPress admin: Tools → Redirection → Export (CSV/JSON)

# Convert to Laravel routes or database redirects
Route::redirect('/old-page', '/new-page', 301);

# Or use package for database-driven redirects:
composer require spatie/laravel-redirect

# Import redirect rules:
foreach ($redirects as $redirect) {
    \Spatie\Redirect\Models\Redirect::create([
        'old_url' => $redirect->source_url,
        'new_url' => $redirect->target_url,
        'status_code' => $redirect->http_code,
    ]);
}
```

**Detection:**
- Traffic drops for specific landing pages
- 404 errors spike for URLs that previously worked
- Backlinks return 404s instead of redirecting

---

### Pitfall 12: Not Handling WordPress Taxonomies (Categories/Tags) Relationships

**What goes wrong:**
WordPress uses complex taxonomy system with `wp_terms`, `wp_term_taxonomy`, and `wp_term_relationships` tables. Developers create simple `categories` table but don't migrate term relationships, resulting in all posts being uncategorized.

**Why it happens:**
WordPress taxonomy architecture is non-intuitive (3 tables for one relationship). Developers migrate posts and create category table but forget the bridging `wp_term_relationships` table.

**Prevention:**
```php
// Migration: Create proper many-to-many relationships
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->timestamps();
});

Schema::create('category_post', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
});

// Import WordPress taxonomies:
// wp_term_taxonomy WHERE taxonomy='category' → categories
// wp_term_relationships → category_post pivot
foreach ($wpTermRelationships as $rel) {
    $wpPost = WpPost::find($rel->object_id);
    $wpTerm = WpTerm::find($rel->term_taxonomy_id);

    $post = Post::where('wordpress_id', $wpPost->ID)->first();
    $category = Category::where('wordpress_id', $wpTerm->term_id)->first();

    $post->categories()->attach($category->id);
}
```

**Detection:**
- All posts show "Uncategorized"
- Category pages empty despite posts existing
- Tag clouds empty
- Navigation menus missing category links

---

## Minor Pitfalls

Mistakes that cause annoyance but are fixable without major rework.

---

### Pitfall 13: WordPress Post Status Confusion

**What goes wrong:**
WordPress has post statuses: publish, draft, pending, private, trash, auto-draft, inherit. Migration maps only 'publish' to active posts, leaving thousands of drafts and pending posts inaccessible.

**Prevention:**
```php
// Map WordPress statuses to Laravel enum
$statusMap = [
    'publish' => 'published',
    'draft' => 'draft',
    'pending' => 'pending_review',
    'private' => 'private',
    'trash' => 'trashed', // or soft delete
];

// Don't import auto-draft or inherit (temporary/meta statuses)
if (in_array($wpPost->post_status, ['auto-draft', 'inherit'])) {
    continue;
}
```

**Detection:**
- Draft posts missing from admin panel
- Authors can't find their unpublished work

---

### Pitfall 14: Comment Moderation Status Lost

**What goes wrong:**
WordPress comments have `comment_approved` field (0=pending, 1=approved, spam). Migration imports all comments as approved, publishing spam and unmoderated comments.

**Prevention:**
```php
$statusMap = [
    '1' => 'approved',
    '0' => 'pending',
    'spam' => 'spam',
    'trash' => 'trashed',
];

Comment::create([
    // ...
    'status' => $statusMap[$wpComment->comment_approved] ?? 'pending',
]);
```

**Detection:**
- Spam comments visible on live posts
- Unmoderated comments published immediately

---

### Pitfall 15: Time Zone Handling Between WordPress and Laravel

**What goes wrong:**
WordPress stores dates in database as GMT but displays in site timezone. Laravel stores dates in UTC. Migration copies dates directly, causing 5-8 hour time shifts on published dates.

**Prevention:**
```php
// WordPress posts are stored in GMT
// Laravel expects UTC (which is GMT, so this is usually fine)

// But if WordPress timezone was set to non-UTC:
$wpTimezone = 'America/Chicago'; // From WordPress settings

Post::create([
    'published_at' => Carbon::createFromFormat(
        'Y-m-d H:i:s',
        $wpPost->post_date,
        $wpTimezone
    )->setTimezone('UTC'),
]);
```

**Detection:**
- Post publish dates off by several hours
- "Published 5 hours in the future" warnings

---

## Phase-Specific Warnings

| Phase Topic | Likely Pitfall | Mitigation | Priority |
|-------------|---------------|------------|----------|
| Initial Database Import | Memory exhaustion from 1.3GB file | Use chunked/streamed import, MySQL CLI | CRITICAL |
| URL Routing Setup | SEO loss from URL structure changes | Map exact WordPress permalink structure | CRITICAL |
| User Authentication | Password hash incompatibility | Implement hybrid password validation | CRITICAL |
| Content Migration | Shortcodes break rendering | Parse/convert shortcodes during import | HIGH |
| Search Integration | Scout import timeout without queues | Configure queue workers before import | CRITICAL |
| Comment System | Lost thread nesting | Preserve parent_id from comment_parent | MEDIUM |
| Livewire Components | Performance degradation with large collections | Use computed properties, never public collections | HIGH |
| Media Migration | 404s for images (files not copied) | Copy /wp-content/uploads, update URLs | HIGH |
| SEO Metadata | Lost Yoast/RankMath data | Import from wp_postmeta during migration | MEDIUM |
| Role/Permissions | Permission chaos from lost roles | Map wp_usermeta roles to Laravel roles | MEDIUM |
| Taxonomy System | Posts lose categories/tags | Migrate 3-table WordPress taxonomy structure | MEDIUM |
| Custom Redirects | Marketing URLs return 404 | Export/import Redirection plugin rules | MEDIUM |

---

## Implementation Checklist by Phase

### Phase 1: Pre-Migration Analysis
- [ ] Export complete WordPress database with `--max_allowed_packet=512M`
- [ ] Inventory all URL patterns (query WordPress for all post types)
- [ ] Identify all shortcode types used in content (regex scan)
- [ ] Export Redirection plugin rules or .htaccess redirects
- [ ] Document WordPress timezone setting
- [ ] Count posts, comments, users, media files for verification later
- [ ] Check for WordPress custom post types beyond standard posts/pages

### Phase 2: Infrastructure Setup
- [ ] Configure Redis or database queue for Laravel
- [ ] Set up queue workers (minimum 2: default + scout)
- [ ] Configure Algolia credentials in Laravel Scout
- [ ] Increase PHP memory/execution limits for migration scripts
- [ ] Set up S3 or storage disk for media files

### Phase 3: Database Schema Design
- [ ] Create `posts` table with SEO metadata columns (meta_title, meta_description, og_image)
- [ ] Create `comments` table with `parent_id` for threading
- [ ] Create `users` table with role/permission system
- [ ] Create categories and tags with pivot tables
- [ ] Add `wordpress_id` column to all tables for reference during migration
- [ ] Plan soft deletes for posts/comments to match WordPress trash

### Phase 4: Data Migration Execution
- [ ] Import users with password hash preservation (hybrid validation)
- [ ] Import categories/tags from WordPress taxonomy tables
- [ ] Import posts with shortcode conversion applied
- [ ] Import SEO metadata from wp_postmeta
- [ ] Import comments with parent_id mapping
- [ ] Import media file references
- [ ] Copy media files from wp-content/uploads to Laravel storage
- [ ] Update image URLs in post content
- [ ] Import redirect rules
- [ ] Verify counts match (WordPress vs Laravel for each table)

### Phase 5: URL/Routing Configuration
- [ ] Map exact WordPress permalink structure in Laravel routes
- [ ] Test URL mapping with actual WordPress URLs (sample 100-500 URLs)
- [ ] Configure 301 redirects for any necessary URL changes
- [ ] Add canonical tags to prevent duplicate content issues

### Phase 6: Search Indexing
- [ ] Configure Scout queue settings
- [ ] Start queue workers
- [ ] Run `scout:import` with `--chunk=500` flag
- [ ] Monitor queue worker logs for failures
- [ ] Verify Algolia index count matches Laravel post count
- [ ] Test search functionality with actual queries

### Phase 7: Livewire Performance Optimization
- [ ] Use computed properties for all comment collections
- [ ] Implement pagination for comments (20 per page)
- [ ] Add lazy loading for nested comment replies
- [ ] Test with high-comment posts (500+ comments)
- [ ] Debounce all user input fields (500ms minimum)
- [ ] Avoid wire:poll on large datasets

### Phase 8: Pre-Launch Verification
- [ ] URL verification script (test all WordPress URLs)
- [ ] SEO audit (meta tags, Open Graph, schema markup)
- [ ] Search functionality test (Algolia returning correct results)
- [ ] User authentication test (old passwords work with hybrid validation)
- [ ] Comment threading display test
- [ ] Media file 404 check (scan for broken images)
- [ ] Performance test on high-traffic posts
- [ ] Monitor queue worker health

---

## Confidence Assessment

| Pitfall Category | Confidence Level | Reasoning |
|-----------------|------------------|-----------|
| URL/SEO Issues | HIGH | Multiple migration case studies document SEO collapse |
| Large Database Import | HIGH | Well-documented WordPress migration forum issues |
| Password Compatibility | HIGH | Verified in WordPress-Laravel migration guides |
| Livewire Performance | HIGH | Confirmed in Livewire GitHub discussions |
| Scout/Algolia Queueing | HIGH | Documented in Laravel Scout official docs |
| Shortcode Handling | MEDIUM | Based on package documentation, needs project-specific testing |
| Comment Threading | MEDIUM | Based on Laravel tutorials and packages |
| Media Migration | MEDIUM | Standard migration pattern, but project-specific |
| SEO Metadata | MEDIUM | Depends on which WordPress SEO plugin was used |
| Taxonomies | MEDIUM | WordPress-specific architecture complexity |

---

## Sources

**WordPress to Laravel Migration:**
- [Migrating 7-Year-Old WordPress Business to Laravel](https://medium.com/@hosnyben/migrating-a-7-year-old-wordpress-business-to-laravel-bf9f11542fc1)
- [Laravel to WordPress Migration: Real Cost of Developer Dependency](https://creativetweed.co.uk/laravel-to-wordpress-migration/)
- [Ultimate Guide to Migrating from WordPress to Laravel](https://dmwebsoft.com/the-ultimate-guide-to-migrating-from-wordpress-to-laravel-step-by-step)
- [WordPress Migration Mistakes](https://wpexperts.io/blog/wordpress-migration-mistakes/)
- [Migrating WordPress users and passwords to Laravel](https://gist.github.com/Yiannistaos/b32db7af83b5ea7c9e981d027690b336)

**Livewire Performance:**
- [Livewire runs really slowly - GitHub Discussion](https://github.com/livewire/livewire/discussions/4492)
- [Speed Up Livewire V3 Guide](https://medium.com/@thenibirahmed/speed-up-livewire-v3-the-only-guide-you-need-32fe73338098)
- [Laravel Livewire Performance Tips & Tricks](https://joelmale.com/blog/laravel-livewire-performance-tips-tricks)
- [Low Performance in Version 3 - GitHub Discussion](https://github.com/livewire/livewire/discussions/6052)
- [Livewire Vulnerability and Performance Issues](https://dev.to/brunoabpinto/how-a-livewire-vulnerability-led-to-crypto-mining-on-our-servers-5gh8)

**Laravel Scout and Algolia:**
- [Laravel Scout Documentation](https://laravel.com/docs/12.x/scout)
- [Getting Started with Laravel Scout and Algolia](https://www.algolia.com/doc/framework-integration/laravel/tutorials/getting-started-with-laravel-scout-vuejs)
- [Scout Extended FAQ](https://www.algolia.com/doc-beta/framework-integration/laravel/troubleshooting/faq)
- [Customize Searchable Data - Algolia](https://www.algolia.com/doc/framework-integration/laravel/indexing/configure-searchable-data)
- [Algolia Laravel deprecated package notice](https://github.com/algolia/algoliasearch-laravel)

**WordPress Large Database:**
- [Migration Problems with Large Database](https://wordpress.org/support/topic/migration-problems-large-database/)
- [All-in-One WP Migration Stuck on Restoring Database](https://blogvault.net/all-in-one-wp-migration-stuck-on-restoring-database/)
- [Scaling Up: Can WordPress Handle Large Databases?](https://sandersdesign.com/blog/scaling-up-can-wordpress-handle-large-databases/)
- [WordPress Performance: Database Optimization](https://pressidium.com/blog/wordpress-performance-database-clean-up-and-optimization/)

**Laravel Comments:**
- [Laravel Nested Comments Tutorial](https://dev.to/techdurjoy/laravel-8-x-multilevel-nested-comments-system-tutorial-41a2)
- [BeyondCode Laravel Comments Package](https://github.com/beyondcode/laravel-comments)
- [Build Laravel Live Commenting System](https://kinsta.com/blog/laravel-comments/)

**WordPress Shortcodes:**
- [Laravel Shortcodes Package](https://github.com/webwizo/laravel-shortcodes)
- [Vedmant Laravel Shortcodes](https://vedmant.com/laravel-shortcodes/)
- [Corcel WordPress-Laravel Bridge](https://github.com/corcel/corcel)

**Media Library:**
- [Spatie Laravel Media Library](https://github.com/spatie/laravel-medialibrary)
- [Laravel Media Library Documentation](https://spatie.be/docs/laravel-medialibrary/v11/introduction)
- [WordPress to Laravel Package](https://packagist.org/packages/leeovery/wordpress-to-laravel)
