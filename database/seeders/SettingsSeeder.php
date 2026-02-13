<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'ShoeMoney', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Making Money Online', 'type' => 'string', 'group' => 'general'],
            ['key' => 'meta_description', 'value' => 'The original blog about making money online since 2003', 'type' => 'text', 'group' => 'general'],
            ['key' => 'posts_per_page', 'value' => '10', 'type' => 'integer', 'group' => 'general'],

            // Comments
            ['key' => 'comment_moderation', 'value' => 'first_time', 'type' => 'string', 'group' => 'comments'],

            // Social
            ['key' => 'social_twitter', 'value' => '', 'type' => 'string', 'group' => 'social'],
            ['key' => 'social_facebook', 'value' => '', 'type' => 'string', 'group' => 'social'],
            ['key' => 'social_youtube', 'value' => '', 'type' => 'string', 'group' => 'social'],
            ['key' => 'social_linkedin', 'value' => '', 'type' => 'string', 'group' => 'social'],

            // Code Snippets
            ['key' => 'analytics_code', 'value' => '', 'type' => 'text', 'group' => 'code'],
            ['key' => 'custom_header_code', 'value' => '', 'type' => 'text', 'group' => 'code'],
            ['key' => 'custom_footer_code', 'value' => '', 'type' => 'text', 'group' => 'code'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully.');
    }
}
