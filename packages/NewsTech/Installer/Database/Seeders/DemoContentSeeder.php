<?php

namespace NewsTech\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use NewsTech\Installer\Support\DemoAssetPublisher;
use NewsTech\Installer\Support\DemoContentInstaller;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $publishedAssets = app(DemoAssetPublisher::class)->publish(force: false);

        app(DemoContentInstaller::class)->seed(
            force: false,
            assets: $publishedAssets['assets']
        );
    }
}
