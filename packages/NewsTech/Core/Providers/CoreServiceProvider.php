<?php

namespace NewsTech\Core\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use NewsTech\Core\Repositories\SystemSettingRepository;
use NewsTech\Core\Support\AclTreeBuilder;
use NewsTech\Core\Support\MediaManager;
use NewsTech\Core\Support\MenuTreeBuilder;
use NewsTech\Core\Support\RenderEventManager;
use NewsTech\Core\Support\SystemSettingsManager;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech'
        );

        $this->app->singleton(MenuTreeBuilder::class);
        $this->app->singleton(AclTreeBuilder::class);
        $this->app->singleton(MediaManager::class);
        $this->app->singleton(RenderEventManager::class);
        $this->app->singleton(SystemSettingRepository::class);
        $this->app->singleton(SystemSettingsManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'newstech');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'newstech');

        app(SystemSettingsManager::class)->bootConfig();
    }
}
