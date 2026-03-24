<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            // Hero section
            ['key' => 'hero_photo_url', 'value' => '', 'type' => 'string', 'group' => 'hero'],
            ['key' => 'hero_blurb', 'value' => '', 'type' => 'string', 'group' => 'hero'],
            ['key' => 'hero_name', 'value' => 'ShoeMoney', 'type' => 'string', 'group' => 'hero'],
            ['key' => 'hero_title', 'value' => 'Entrepreneur & Blogger', 'type' => 'string', 'group' => 'hero'],

            // Footer
            ['key' => 'footer_text', 'value' => '', 'type' => 'string', 'group' => 'footer'],
            ['key' => 'footer_links', 'value' => '[]', 'type' => 'json', 'group' => 'footer'],

            // Sidebar: Shitlist
            ['key' => 'shitlist_items', 'value' => '[]', 'type' => 'json', 'group' => 'sidebar'],
            ['key' => 'shitlist_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'sidebar'],

            // Sidebar: Resources
            ['key' => 'resources_items', 'value' => '[]', 'type' => 'json', 'group' => 'sidebar'],
            ['key' => 'resources_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'sidebar'],

            // Sidebar: Popular Posts
            ['key' => 'popular_posts_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'sidebar'],
            ['key' => 'popular_posts_count', 'value' => '5', 'type' => 'integer', 'group' => 'sidebar'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'hero_photo_url', 'hero_blurb', 'hero_name', 'hero_title',
            'footer_text', 'footer_links',
            'shitlist_items', 'shitlist_enabled',
            'resources_items', 'resources_enabled',
            'popular_posts_enabled', 'popular_posts_count',
        ])->delete();
    }
};
