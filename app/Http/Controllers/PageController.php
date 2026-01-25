<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PageController extends Controller
{
    /**
     * Display a single page by slug.
     *
     * WordPress permalink format: /{slug}/
     *
     * @param string $slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        return response("Page placeholder: {$slug} - to be implemented in Plan 02", 200);
    }
}
