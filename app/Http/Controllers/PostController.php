<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
    /**
     * Display blog listing (homepage).
     *
     * @return Response
     */
    public function index(): Response
    {
        return response('Blog listing placeholder - to be implemented in Plan 02', 200);
    }

    /**
     * Display a single post by date and slug.
     *
     * WordPress permalink format: /{year}/{month}/{day}/{slug}/
     *
     * @param string $year
     * @param string $month
     * @param string $day
     * @param string $slug
     * @return Response
     */
    public function show(string $year, string $month, string $day, string $slug): Response
    {
        return response("Post placeholder: {$year}/{$month}/{$day}/{$slug} - to be implemented in Plan 02", 200);
    }
}
