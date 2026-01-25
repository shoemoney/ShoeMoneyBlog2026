<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

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
     * @return View
     */
    public function show(string $slug): View
    {
        // Look up tag by slug, 404 if not found
        $tag = Tag::where('slug', $slug)->firstOrFail();

        // Get published posts with this tag, paginated for performance
        $posts = $tag->posts()
            ->whereNotNull('published_at')
            ->where('status', 'published')
            ->with(['author', 'categories'])
            ->orderByDesc('published_at')
            ->paginate(15);

        // Configure SEO meta tags
        seo()
            ->title('#' . $tag->name . ' - ShoeMoney')
            ->description($tag->description ?: 'Posts tagged ' . $tag->name);

        return view('tags.show', compact('tag', 'posts'));
    }
}
