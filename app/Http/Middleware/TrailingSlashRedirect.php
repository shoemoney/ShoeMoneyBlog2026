<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrailingSlashRedirect
{
    /**
     * File extensions to exclude from trailing slash redirect.
     * These paths should not have trailing slashes added.
     */
    protected array $excludedExtensions = [
        'xml',   // sitemap.xml, feed.xml
        'txt',   // robots.txt
        'json',  // API responses
        'css',
        'js',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'ico',
        'webp',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'pdf',
        'zip',
    ];

    /**
     * Handle an incoming request.
     *
     * Redirects URLs without trailing slashes to their trailing slash versions.
     * Uses 301 (permanent) redirect for SEO benefit.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to GET requests
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $path = $request->getPathInfo();

        // Skip if already has trailing slash
        if (str_ends_with($path, '/')) {
            return $next($request);
        }

        // Skip homepage (path is just "/")
        if ($path === '') {
            return $next($request);
        }

        // Skip paths with file extensions
        if ($this->hasFileExtension($path)) {
            return $next($request);
        }

        // Build redirect URL with trailing slash, preserving query string
        $url = $request->getSchemeAndHttpHost() . $path . '/';

        if ($request->getQueryString()) {
            $url .= '?' . $request->getQueryString();
        }

        return redirect($url, 301);
    }

    /**
     * Check if the path has a file extension that should be excluded.
     */
    protected function hasFileExtension(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, $this->excludedExtensions, true);
    }
}
