<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Display posts with a tag.
     *
     * WordPress permalink format: /tag/{slug}/
     *
     * With 15,448 tags, pagination is essential for performance.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        // Look up tag by slug, 404 if not found
        $tag = Tag::where('slug', $slug)->firstOrFail();

        // Get published posts with this tag, paginated for performance
        $posts = $tag->posts()
            ->whereNotNull('published_at')
            ->where('status', 'published')
            ->with('author')
            ->orderByDesc('published_at')
            ->paginate(15);

        return response()->json([
            'tag' => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'url' => $tag->url,
            ],
            'posts' => $posts,
        ]);
    }
}
