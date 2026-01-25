<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display posts in a category.
     *
     * WordPress permalink format: /category/{slug}/
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        // Look up category by slug, 404 if not found
        $category = Category::where('slug', $slug)->firstOrFail();

        // Get published posts in this category with pagination
        $posts = $category->posts()
            ->whereNotNull('published_at')
            ->where('status', 'published')
            ->with('author')
            ->orderByDesc('published_at')
            ->paginate(15);

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'url' => $category->url,
            ],
            'posts' => $posts,
        ]);
    }
}
