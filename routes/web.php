<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WordPress-Compatible Routes
|--------------------------------------------------------------------------
|
| These routes preserve the WordPress permalink structure to maintain
| SEO value from 20 years of content. Order matters: specific patterns
| must come before general catch-all patterns.
|
*/

// Sitemap - served with correct Content-Type for search engines
Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');

    if (!file_exists($path)) {
        abort(404, 'Sitemap not generated. Run: php artisan sitemap:generate');
    }

    return response()->file($path, [
        'Content-Type' => 'application/xml',
    ]);
})->name('sitemap');

// Homepage - blog listing
Route::get('/', [PostController::class, 'index'])->name('home');

// Date-based post route (most specific - must come before page catch-all)
Route::get('/{year}/{month}/{day}/{slug}', [PostController::class, 'show'])
    ->where([
        'year' => '[0-9]{4}',
        'month' => '[0-9]{2}',
        'day' => '[0-9]{2}',
        'slug' => '[a-z0-9\-]+',
    ])
    ->name('post.show');

// Category archive route
Route::get('/category/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('category.show');

// Tag archive route
Route::get('/tag/{slug}', [TagController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('tag.show');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Protected by auth middleware + admin check. All routes are placeholders
| that will be replaced by Livewire components in subsequent plans.
|
*/

Route::prefix('admin')
    ->middleware(['auth', EnsureUserIsAdmin::class])
    ->group(function () {
        // Dashboard - placeholder until 06-02 adds Livewire component
        Route::get('/', function () {
            return 'Admin Dashboard (placeholder - will be replaced by Livewire component)';
        })->name('admin.dashboard');

        // Posts management placeholders
        Route::get('/posts', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Posts management coming soon');
        })->name('admin.posts.index');
        Route::get('/posts/create', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Post creation coming soon');
        })->name('admin.posts.create');
        Route::get('/posts/{post}/edit', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Post editing coming soon');
        })->name('admin.posts.edit');

        // Comments management placeholder
        Route::get('/comments', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Comments management coming soon');
        })->name('admin.comments.index');

        // Categories management placeholder
        Route::get('/categories', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Categories management coming soon');
        })->name('admin.categories.index');

        // Tags management placeholder
        Route::get('/tags', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Tags management coming soon');
        })->name('admin.tags.index');

        // Users management placeholders
        Route::get('/users', function () {
            return redirect()->route('admin.dashboard')->with('info', 'Users management coming soon');
        })->name('admin.users.index');
        Route::get('/users/create', function () {
            return redirect()->route('admin.dashboard')->with('info', 'User creation coming soon');
        })->name('admin.users.create');
        Route::get('/users/{user}/edit', function () {
            return redirect()->route('admin.dashboard')->with('info', 'User editing coming soon');
        })->name('admin.users.edit');
    });

// Page route (MUST be LAST - catch-all for single slugs)
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');
