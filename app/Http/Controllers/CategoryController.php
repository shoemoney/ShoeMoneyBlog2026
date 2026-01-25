<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display posts in a category.
     *
     * WordPress permalink format: /category/{slug}/
     *
     * @param string $slug
     * @return View
     */
    public function show(string $slug): View
    {
        // Look up category by slug, 404 if not found
        $category = Category::where('slug', $slug)->firstOrFail();

        // Get published posts in this category with pagination
        $posts = $category->posts()
            ->whereNotNull('published_at')
            ->where('status', 'published')
            ->with(['author', 'categories'])
            ->orderByDesc('published_at')
            ->paginate(15);

        // Configure SEO meta tags
        seo()
            ->title($category->name . ' - ShoeMoney')
            ->description($category->description ?: 'Posts in ' . $category->name);

        return view('categories.show', compact('category', 'posts'));
    }
}
