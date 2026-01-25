<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display blog listing (homepage).
     *
     * @return View
     */
    public function index(): View
    {
        $posts = Post::published()
            ->with('author', 'categories')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        seo()
            ->title('ShoeMoney - Making Money Online')
            ->description('The original blog about making money online since 2003');

        return view('posts.index', compact('posts'));
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
     * @return View
     */
    public function show(string $year, string $month, string $day, string $slug): View
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

        seo()
            ->title($post->title . ' - ShoeMoney')
            ->description($post->excerpt ?: Str::limit(strip_tags($post->content), 160))
            ->url(url($post->url));

        return view('posts.show', compact('post'));
    }
}
