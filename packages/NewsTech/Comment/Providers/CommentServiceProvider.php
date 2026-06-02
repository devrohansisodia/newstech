<?php

namespace NewsTech\Comment\Providers;

use Illuminate\Support\ServiceProvider;

class CommentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-comment'
        );

        config()->set('menu.admin', [
            ...config('menu.admin', []),
            ...require __DIR__.'/../Config/menu.php',
        ]);

        config()->set('acl', [
            ...config('acl', []),
            ...require __DIR__.'/../Config/acl.php',
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
