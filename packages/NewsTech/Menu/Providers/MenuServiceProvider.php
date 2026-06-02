<?php

namespace NewsTech\Menu\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Menu\Repositories\MenuGroupRepository;
use NewsTech\Menu\Repositories\MenuItemRepository;
use NewsTech\Menu\Support\FrontendMenuResolver;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MenuGroupRepository::class);
        $this->app->singleton(MenuItemRepository::class);
        $this->app->singleton(FrontendMenuResolver::class);

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
