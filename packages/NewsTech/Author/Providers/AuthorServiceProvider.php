<?php

namespace NewsTech\Author\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Author\Repositories\AuthorRepository;

class AuthorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthorRepository::class);

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
