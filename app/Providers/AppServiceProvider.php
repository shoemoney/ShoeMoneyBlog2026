<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureSeo();
    }

    /**
     * Configure SEO defaults from config/seo.php.
     */
    protected function configureSeo(): void
    {
        seo()
            ->site(config('seo.site_name'))
            ->title(
                default: config('seo.site_name'),
                modifier: fn (?string $title) => $title ? $title . config('seo.title_suffix') : config('seo.site_name')
            )
            ->description(default: config('seo.description'))
            ->twitter();
    }
}
