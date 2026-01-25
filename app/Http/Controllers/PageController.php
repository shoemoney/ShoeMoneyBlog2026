<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display a single page by slug.
     *
     * WordPress permalink format: /{slug}/
     *
     * @param string $slug
     * @return View
     */
    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)
            ->with('author')
            ->firstOrFail();

        seo()
            ->title($page->title . ' - ShoeMoney')
            ->description(Str::limit(strip_tags($page->content), 160));

        return view('pages.show', compact('page'));
    }
}
