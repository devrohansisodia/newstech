<?php

namespace NewsTech\Article\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Article\Repositories\ArticleRepository;

class ArticleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ArticleRepository::class);

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
