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
    ->middleware(['auth', EnsureUserIsAdmin::class, 'doNotCacheResponse'])
    ->group(function () {
        // Dashboard
        Route::get('/', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');

        // Posts management
        Route::get('/posts', \App\Livewire\Admin\Posts\PostIndex::class)->name('admin.posts.index');
        Route::get('/posts/create', \App\Livewire\Admin\Posts\PostCreate::class)->name('admin.posts.create');
        Route::get('/posts/{post}/edit', \App\Livewire\Admin\Posts\PostEdit::class)->name('admin.posts.edit');

        // Comments management
        Route::get('/comments', \App\Livewire\Admin\Comments\CommentModeration::class)
            ->name('admin.comments.index');

        // Categories management
        Route::get('/categories', \App\Livewire\Admin\Taxonomies\CategoryManager::class)
            ->name('admin.categories.index');

        // Tags management
        Route::get('/tags', \App\Livewire\Admin\Taxonomies\TagManager::class)
            ->name('admin.tags.index');

        // Pages management
        Route::get('/pages', \App\Livewire\Admin\Pages\PageIndex::class)->name('admin.pages.index');
        Route::get('/pages/create', \App\Livewire\Admin\Pages\PageCreate::class)->name('admin.pages.create');
        Route::get('/pages/{page}/edit', \App\Livewire\Admin\Pages\PageEdit::class)->name('admin.pages.edit');

        // Users management
        Route::get('/users', \App\Livewire\Admin\Users\UserIndex::class)->name('admin.users.index');
        Route::get('/users/create', \App\Livewire\Admin\Users\UserForm::class)->name('admin.users.create');
        Route::get('/users/{user}/edit', \App\Livewire\Admin\Users\UserForm::class)->name('admin.users.edit');

        // Settings
        Route::get('/settings', \App\Livewire\Admin\Settings\SiteSettings::class)->name('admin.settings.index');
        Route::get('/settings/navigation', \App\Livewire\Admin\Settings\NavigationManager::class)->name('admin.settings.navigation');
        Route::get('/settings/widgets', \App\Livewire\Admin\Settings\WidgetManager::class)->name('admin.settings.widgets');
    });

// Page route (MUST be LAST - catch-all for single slugs)
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');
