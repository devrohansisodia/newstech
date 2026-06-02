<?php

namespace NewsTech\Admin\Support\SettingsGroups;

use NewsTech\Core\Support\SystemSettingsManager;

class PersistHomepageSettings
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(array $validated, SystemSettingsManager $systemSettingsManager): void
    {
        $systemSettingsManager->setMany([
            'website.homepage.layout' => $validated['homepage_layout'],
            'website.homepage.sidebar_title' => $validated['homepage_sidebar_title'] ?: null,
            'website.homepage.sidebar_content' => $validated['homepage_sidebar_content'] ?: null,
            'website.homepage.sidebar_link_label' => $validated['homepage_sidebar_link_label'] ?: null,
            'website.homepage.sidebar_link_url' => $validated['homepage_sidebar_link_url'] ?: null,
        ]);
    }
}
