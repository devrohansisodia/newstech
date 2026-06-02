<?php

namespace NewsTech\Seo\Support;

use NewsTech\Core\Support\SystemSettingsManager;

class PersistSeoSettings
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(array $validated, SystemSettingsManager $systemSettingsManager): void
    {
        $systemSettingsManager->setMany([
            'seo.site_title_suffix' => $validated['site_title_suffix'] ?: null,
            'seo.default_meta_description' => $validated['default_meta_description'] ?: null,
            'seo.enable_real_time_checks' => (bool) $validated['enable_real_time_checks'],
            'seo.score_threshold_warning' => (int) $validated['score_threshold_warning'],
            'seo.enable_social_preview' => (bool) $validated['enable_social_preview'],
        ]);
    }
}
