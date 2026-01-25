<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
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

// Page route (MUST be LAST - catch-all for single slugs)
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('page.show');
