<?php

namespace NewsTech\Frontend\Support;

use NewsTech\Core\Support\SystemSettingsManager;

trait AppliesSystemSettings
{
    protected function applySystemSettings(): void
    {
        app(SystemSettingsManager::class)->bootConfig();
    }
}
