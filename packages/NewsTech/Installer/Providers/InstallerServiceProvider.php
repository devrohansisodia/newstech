<?php

namespace NewsTech\Installer\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Installer\Console\Commands\NewsTechInstallCommand;
use NewsTech\Installer\Support\DefaultSettingsInstaller;
use NewsTech\Installer\Support\DemoAssetPublisher;
use NewsTech\Installer\Support\DemoContentInstaller;
use NewsTech\Installer\Support\EnvironmentFileManager;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-installer'
        );

        $this->app->singleton(DefaultSettingsInstaller::class);
        $this->app->singleton(DemoAssetPublisher::class);
        $this->app->singleton(DemoContentInstaller::class);
        $this->app->singleton(EnvironmentFileManager::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                NewsTechInstallCommand::class,
            ]);
        }
    }
}
