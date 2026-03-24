<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Page;
use App\Models\Post;
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
    public string $site_logo_url = '';
    public string $meta_description = '';
    public int $posts_per_page = 10;

    // Hero
    public string $hero_photo_url = '';
    public string $hero_name = '';
    public string $hero_title = '';
    public string $hero_blurb = '';

    // Social
    public string $social_twitter = '';
    public string $social_facebook = '';
    public string $social_youtube = '';
    public string $social_linkedin = '';

    // Footer
    public string $footer_text = '';
    public array $footer_links = [];

    // Sidebar: Shitlist
    public bool $shitlist_enabled = true;
    public array $shitlist_items = [];

    // Sidebar: Resources
    public bool $resources_enabled = true;
    public array $resources_items = [];

    // Sidebar: Popular Posts (manually curated)
    public bool $popular_posts_enabled = true;
    public array $popular_posts_items = [];

    // Hero: "As Seen In" press mentions
    public array $hero_press_items = [];

    // Hero: Project & social links (with icon names)
    public array $hero_links = [];

    // Code
    public string $analytics_code = '';
    public string $custom_header_code = '';
    public string $custom_footer_code = '';

    public function mount(): void
    {
        $settings = Setting::all()->keyBy('key');

        $this->site_name = $settings->get('site_name')?->value ?? '';
        $this->site_tagline = $settings->get('site_tagline')?->value ?? '';
        $this->site_logo_url = $settings->get('site_logo_url')?->value ?? '';
        $this->meta_description = $settings->get('meta_description')?->value ?? '';
        $this->posts_per_page = (int) ($settings->get('posts_per_page')?->value ?? 10);
        // Hero
        $this->hero_photo_url = $settings->get('hero_photo_url')?->value ?? '';
        $this->hero_name = $settings->get('hero_name')?->value ?? 'ShoeMoney';
        $this->hero_title = $settings->get('hero_title')?->value ?? 'Entrepreneur & Blogger';
        $this->hero_blurb = $settings->get('hero_blurb')?->value ?? '';

        // Social
        $this->social_twitter = $settings->get('social_twitter')?->value ?? '';
        $this->social_facebook = $settings->get('social_facebook')?->value ?? '';
        $this->social_youtube = $settings->get('social_youtube')?->value ?? '';
        $this->social_linkedin = $settings->get('social_linkedin')?->value ?? '';

        // Footer
        $this->footer_text = $settings->get('footer_text')?->value ?? '';
        $footerLinksRaw = $settings->get('footer_links')?->value ?? '[]';
        $this->footer_links = is_array($footerLinksRaw) ? $footerLinksRaw : (json_decode($footerLinksRaw, true) ?? []);

        // Sidebar
        $this->shitlist_enabled = filter_var($settings->get('shitlist_enabled')?->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $shitlistRaw = $settings->get('shitlist_items')?->value ?? '[]';
        $this->shitlist_items = is_array($shitlistRaw) ? $shitlistRaw : (json_decode($shitlistRaw, true) ?? []);

        $this->resources_enabled = filter_var($settings->get('resources_enabled')?->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $resourcesRaw = $settings->get('resources_items')?->value ?? '[]';
        $this->resources_items = is_array($resourcesRaw) ? $resourcesRaw : (json_decode($resourcesRaw, true) ?? []);

        $this->popular_posts_enabled = filter_var($settings->get('popular_posts_enabled')?->value ?? '1', FILTER_VALIDATE_BOOLEAN);
        $popularPostsRaw = $settings->get('popular_posts_items')?->value ?? '[]';
        $this->popular_posts_items = is_array($popularPostsRaw) ? $popularPostsRaw : (json_decode($popularPostsRaw, true) ?? []);

        // Hero: Press mentions & project links
        $pressRaw = $settings->get('hero_press_items')?->value ?? '[]';
        $this->hero_press_items = is_array($pressRaw) ? $pressRaw : (json_decode($pressRaw, true) ?? []);

        $linksRaw = $settings->get('hero_links')?->value ?? '[]';
        $this->hero_links = is_array($linksRaw) ? $linksRaw : (json_decode($linksRaw, true) ?? []);

        // Code
        $this->analytics_code = $settings->get('analytics_code')?->value ?? '';
        $this->custom_header_code = $settings->get('custom_header_code')?->value ?? '';
        $this->custom_footer_code = $settings->get('custom_footer_code')?->value ?? '';
    }

    // Footer link management
    public function addFooterLink(): void
    {
        $this->footer_links[] = ['label' => '', 'url' => '', 'new_tab' => false];
    }

    public function removeFooterLink(int $index): void
    {
        unset($this->footer_links[$index]);
        $this->footer_links = array_values($this->footer_links);
    }

    // Shitlist management
    public function addShitlistItem(): void
    {
        $this->shitlist_items[] = ['name' => '', 'url' => '', 'description' => ''];
    }

    public function removeShitlistItem(int $index): void
    {
        unset($this->shitlist_items[$index]);
        $this->shitlist_items = array_values($this->shitlist_items);
    }

    // Resources management
    public function addResourceItem(): void
    {
        $this->resources_items[] = ['name' => '', 'url' => '', 'description' => ''];
    }

    public function removeResourceItem(int $index): void
    {
        unset($this->resources_items[$index]);
        $this->resources_items = array_values($this->resources_items);
    }

    // Popular Posts management
    public function addPopularPost(): void
    {
        $this->popular_posts_items[] = ['title' => '', 'url' => ''];
    }

    public function removePopularPost(int $index): void
    {
        unset($this->popular_posts_items[$index]);
        $this->popular_posts_items = array_values($this->popular_posts_items);
    }

    // Hero: Press mentions management
    public function addPressItem(): void
    {
        $this->hero_press_items[] = ['name' => '', 'url' => ''];
    }

    public function removePressItem(int $index): void
    {
        unset($this->hero_press_items[$index]);
        $this->hero_press_items = array_values($this->hero_press_items);
    }

    // Hero: Project/social links management
    public function addHeroLink(): void
    {
        $this->hero_links[] = ['icon' => 'link', 'label' => '', 'url' => ''];
    }

    public function removeHeroLink(int $index): void
    {
        unset($this->hero_links[$index]);
        $this->hero_links = array_values($this->hero_links);
    }

    /**
     * Search posts and pages for any URL autocomplete (footer links, etc).
     */
    public function searchContent(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $posts = Post::posts()
            ->where('status', 'published')
            ->where('title', 'like', '%' . $query . '%')
            ->orderByDesc('published_at')
            ->take(5)
            ->get()
            ->map(fn ($post) => [
                'title' => $post->title,
                'url' => $post->url,
                'type' => 'Post',
                'date' => $post->published_at?->format('M j, Y') ?? '',
            ]);

        $pages = Page::where('status', 'published')
            ->where('title', 'like', '%' . $query . '%')
            ->orderBy('title')
            ->take(3)
            ->get()
            ->map(fn ($page) => [
                'title' => $page->title,
                'url' => $page->url,
                'type' => 'Page',
                'date' => '',
            ]);

        return collect($pages)->merge(collect($posts))->values()->toArray();
    }

    /**
     * Search posts by title for the popular posts autocomplete.
     */
    public function searchPostsForPopular(string $query): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return Post::posts()
            ->where('status', 'published')
            ->where('title', 'like', '%' . $query . '%')
            ->orderByDesc('published_at')
            ->take(8)
            ->get()
            ->map(fn ($post) => [
                'title' => $post->title,
                'url' => $post->url,
                'date' => $post->published_at?->format('M j, Y') ?? '',
            ])
            ->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_logo_url' => 'nullable|url|max:500',
            'meta_description' => 'nullable|string|max:500',
            'posts_per_page' => 'required|integer|min:1|max:100',
            'hero_photo_url' => 'nullable|url|max:500',
            'hero_name' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255',
            'hero_blurb' => 'nullable|string|max:2000',
            'social_twitter' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'footer_text' => 'nullable|string|max:500',
        ]);

        $values = [
            'site_name' => $this->site_name,
            'site_tagline' => $this->site_tagline,
            'site_logo_url' => $this->site_logo_url,
            'meta_description' => $this->meta_description,
            'posts_per_page' => (string) $this->posts_per_page,
            'hero_photo_url' => $this->hero_photo_url,
            'hero_name' => $this->hero_name,
            'hero_title' => $this->hero_title,
            'hero_blurb' => $this->hero_blurb,
            'social_twitter' => $this->social_twitter,
            'social_facebook' => $this->social_facebook,
            'social_youtube' => $this->social_youtube,
            'social_linkedin' => $this->social_linkedin,
            'analytics_code' => $this->analytics_code,
            'custom_header_code' => $this->custom_header_code,
            'custom_footer_code' => $this->custom_footer_code,
            'footer_text' => $this->footer_text,
            'shitlist_enabled' => $this->shitlist_enabled ? '1' : '0',
            'resources_enabled' => $this->resources_enabled ? '1' : '0',
            'popular_posts_enabled' => $this->popular_posts_enabled ? '1' : '0',
        ];

        foreach ($values as $key => $value) {
            Setting::setValue($key, $value);
        }

        // Save JSON settings
        Setting::setValue('footer_links', $this->footer_links, 'json', 'footer');
        Setting::setValue('shitlist_items', $this->shitlist_items, 'json', 'sidebar');
        Setting::setValue('resources_items', $this->resources_items, 'json', 'sidebar');
        Setting::setValue('popular_posts_items', $this->popular_posts_items, 'json', 'sidebar');
        Setting::setValue('hero_press_items', $this->hero_press_items, 'json', 'hero');
        Setting::setValue('hero_links', $this->hero_links, 'json', 'hero');

        session()->flash('success', 'Settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.admin.settings.site-settings');
    }
}
