<?php

namespace NewsTech\Page\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Page\Repositories\PageRepository;

class PageServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PageRepository::class);

        config()->set('menu.admin', [
            ...config('menu.admin', []),
            ...require __DIR__.'/../Config/menu.php',
        ]);

        config()->set('acl', [
            ...config('acl', []),
            ...require __DIR__.'/../Config/acl.php',
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
