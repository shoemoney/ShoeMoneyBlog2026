<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.admin.layouts.app')]
#[Title('Site Settings - Admin')]
class SiteSettings extends Component
{
    // General
    public string $site_name = '';
    public string $site_tagline = '';
    public string $meta_description = '';
    public int $posts_per_page = 10;

    // Comments
    public string $comment_moderation = 'first_time';

    // Social
    public string $social_twitter = '';
    public string $social_facebook = '';
    public string $social_youtube = '';
    public string $social_linkedin = '';

    // Code
    public string $analytics_code = '';
    public string $custom_header_code = '';
    public string $custom_footer_code = '';

    public function mount(): void
    {
        $settings = Setting::all()->keyBy('key');

        $this->site_name = $settings->get('site_name')?->value ?? '';
        $this->site_tagline = $settings->get('site_tagline')?->value ?? '';
        $this->meta_description = $settings->get('meta_description')?->value ?? '';
        $this->posts_per_page = (int) ($settings->get('posts_per_page')?->value ?? 10);
        $this->comment_moderation = $settings->get('comment_moderation')?->value ?? 'first_time';
        $this->social_twitter = $settings->get('social_twitter')?->value ?? '';
        $this->social_facebook = $settings->get('social_facebook')?->value ?? '';
        $this->social_youtube = $settings->get('social_youtube')?->value ?? '';
        $this->social_linkedin = $settings->get('social_linkedin')?->value ?? '';
        $this->analytics_code = $settings->get('analytics_code')?->value ?? '';
        $this->custom_header_code = $settings->get('custom_header_code')?->value ?? '';
        $this->custom_footer_code = $settings->get('custom_footer_code')?->value ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'posts_per_page' => 'required|integer|min:1|max:100',
            'comment_moderation' => 'required|in:none,first_time,all',
            'social_twitter' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
        ]);

        $values = [
            'site_name' => $this->site_name,
            'site_tagline' => $this->site_tagline,
            'meta_description' => $this->meta_description,
            'posts_per_page' => (string) $this->posts_per_page,
            'comment_moderation' => $this->comment_moderation,
            'social_twitter' => $this->social_twitter,
            'social_facebook' => $this->social_facebook,
            'social_youtube' => $this->social_youtube,
            'social_linkedin' => $this->social_linkedin,
            'analytics_code' => $this->analytics_code,
            'custom_header_code' => $this->custom_header_code,
            'custom_footer_code' => $this->custom_footer_code,
        ];

        foreach ($values as $key => $value) {
            Setting::setValue($key, $value);
        }

        session()->flash('success', 'Settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.admin.settings.site-settings');
    }
}
