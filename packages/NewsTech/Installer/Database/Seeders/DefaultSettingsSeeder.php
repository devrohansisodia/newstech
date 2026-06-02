<?php

namespace NewsTech\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use NewsTech\Installer\Support\DefaultSettingsInstaller;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        app(DefaultSettingsInstaller::class)->seed(force: false, seedDemoContent: false);
    }
}
