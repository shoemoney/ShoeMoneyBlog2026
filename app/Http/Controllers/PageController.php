<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * Display a single page by slug.
     *
     * WordPress permalink format: /{slug}/
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->with('author')
            ->firstOrFail();

        // Placeholder response until Phase 3 views
        return response()->json([
            'message' => 'Page found - view pending Phase 3',
            'id' => $page->id,
            'title' => $page->title,
            'url' => $page->url,
        ]);
    }
}
