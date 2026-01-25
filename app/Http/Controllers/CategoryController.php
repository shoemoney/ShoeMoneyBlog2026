<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display posts in a category.
     *
     * WordPress permalink format: /category/{slug}/
     *
     * @param string $slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        return response("Category placeholder: {$slug} - to be implemented in Plan 02", 200);
    }
}
