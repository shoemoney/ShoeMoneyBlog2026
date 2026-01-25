<?php

/**
 * SEO Configuration for ShoeMoney Blog
 *
 * These values are used by the SEO service provider to set defaults.
 * The archtechx/laravel-seo package uses a fluent API configured
 * in AppServiceProvider.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | The name of your website. This appears in OpenGraph tags and can be
    | appended to page titles.
    |
    */
    'site_name' => 'ShoeMoney',

    /*
    |--------------------------------------------------------------------------
    | Title Suffix
    |--------------------------------------------------------------------------
    |
    | This string is appended to all page titles for consistent branding.
    |
    */
    'title_suffix' => ' - ShoeMoney',

    /*
    |--------------------------------------------------------------------------
    | Default Description
    |--------------------------------------------------------------------------
    |
    | The default meta description used when no specific description is set.
    |
    */
    'description' => 'The original blog about making money online since 2003',

    /*
    |--------------------------------------------------------------------------
    | Twitter Card Configuration
    |--------------------------------------------------------------------------
    |
    | Twitter card type determines how your content appears when shared.
    | Options: summary, summary_large_image, app, player
    |
    */
    'twitter' => [
        'card_type' => 'summary_large_image',
        'site' => null,
        'creator' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Image
    |--------------------------------------------------------------------------
    |
    | The default OpenGraph image used when no specific image is set.
    | Set to null to omit, or provide an absolute URL.
    |
    */
    'default_image' => null,
];
