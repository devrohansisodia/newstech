<?php

namespace NewsTech\Media\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Media\Repositories\MediaRepository;
use NewsTech\Media\Support\MediaLibraryManager;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MediaRepository::class);
        $this->app->singleton(MediaLibraryManager::class);

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
