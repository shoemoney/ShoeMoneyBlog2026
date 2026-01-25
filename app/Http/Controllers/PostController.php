<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    /**
     * Display blog listing (homepage).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $posts = Post::published()
            ->with('author', 'categories')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        // Placeholder response until Phase 3 views
        return response()->json([
            'message' => 'Homepage - view pending Phase 3',
            'count' => $posts->total(),
        ]);
    }

    /**
     * Display a single post by date and slug.
     *
     * WordPress permalink format: /{year}/{month}/{day}/{slug}/
     * Validates that URL date matches published_at to prevent URL manipulation.
     *
     * @param string $year
     * @param string $month
     * @param string $day
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $year, string $month, string $day, string $slug): JsonResponse
    {
        // Query with full date validation to prevent URL manipulation
        $post = Post::query()
            ->where('slug', $slug)
            ->whereYear('published_at', $year)
            ->whereMonth('published_at', $month)
            ->whereDay('published_at', $day)
            ->where('status', 'published')
            ->with('author', 'categories', 'tags')
            ->firstOrFail();

        // Placeholder response until Phase 3 views
        return response()->json([
            'message' => 'Post found - view pending Phase 3',
            'id' => $post->id,
            'title' => $post->title,
            'url' => $post->url,
            'published_at' => $post->published_at->toIso8601String(),
        ]);
    }
}
