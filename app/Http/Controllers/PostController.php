<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
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
        $posts = Post::posts()->published()
            ->with('author', 'categories', 'featuredImage')
            ->orderBy('published_at', 'desc')
            ->paginate(Setting::getValue('posts_per_page', 10));

        $siteName = Setting::getValue('site_name', 'ShoeMoney');
        $siteTagline = Setting::getValue('site_tagline', 'Making Money Online');
        $metaDescription = Setting::getValue('meta_description', 'The original blog about making money online since 2003');

        seo()
            ->title($siteName . ($siteTagline ? ' - ' . $siteTagline : ''))
            ->description($metaDescription);

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
    public function show(string $year, string $month, string $day, string $slug): View|RedirectResponse
    {
        // Query with full date validation to prevent URL manipulation
        $post = Post::posts()
            ->where('slug', $slug)
            ->whereYear('published_at', $year)
            ->whereMonth('published_at', $month)
            ->whereDay('published_at', $day)
            ->where('status', 'published')
            ->with('author', 'categories', 'tags', 'featuredImage')
            ->first();

        // If exact date match fails, try slug-only lookup and 301 redirect
        // This handles old WordPress URLs where the migration date doesn't match
        if (!$post) {
            $post = Post::posts()
                ->where('slug', $slug)
                ->where('status', 'published')
                ->first();

            if ($post) {
                return redirect($post->url, 301);
            }

            abort(404);
        }

        $seo = seo()
            ->title($post->title . ' - ShoeMoney')
            ->description($post->excerpt ?: Str::limit(strip_tags($post->content), 160))
            ->url(url($post->url));

        if ($post->featured_image_url) {
            $seo->image($post->featured_image_url);
        }

        return view('posts.show', compact('post'));
    }
}
