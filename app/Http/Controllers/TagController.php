<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TagController extends Controller
{
    /**
     * Display posts with a tag.
     *
     * WordPress permalink format: /tag/{slug}/
     *
     * @param string $slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        return response("Tag placeholder: {$slug} - to be implemented in Plan 02", 200);
    }
}
