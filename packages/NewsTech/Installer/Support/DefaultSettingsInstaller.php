<?php

namespace NewsTech\Installer\Support;

use NewsTech\Core\Models\SystemSetting;
use NewsTech\Core\Support\SystemSettingsManager;

class DefaultSettingsInstaller
{
    public function __construct(protected SystemSettingsManager $settingsManager) {}

    /**
     * @param  array<string, string|null>  $demoBranding
     * @return array{persisted:int, values:array<string, mixed>}
     */
    public function seed(bool $force, bool $seedDemoContent, array $demoBranding = []): array
    {
        $defaults = [
            'website.identity.site_name' => config('newstech.brand.name'),
            'website.homepage.layout' => $seedDemoContent ? 'two_column_70_30' : 'full_width',
        ];

        if ($seedDemoContent) {
            $defaults['website.homepage.sidebar_title'] = 'Inside NewsTech';
            $defaults['website.homepage.sidebar_content'] = 'A ready-to-review sidebar with editorial notes, partner messaging, and links that make the demo homepage feel populated from the first load.';
            $defaults['website.homepage.sidebar_link_label'] = 'Editorial Policy';
            $defaults['website.homepage.sidebar_link_url'] = route('newstech.pages.show', ['slug' => 'editorial-policy']);
        }

        if (($demoBranding['logo_path'] ?? null) !== null) {
            $defaults['website.identity.logo_path'] = $demoBranding['logo_path'];
        }

        if (($demoBranding['footer_logo_path'] ?? null) !== null) {
            $defaults['website.identity.footer_logo_path'] = $demoBranding['footer_logo_path'];
        }

        $existing = SystemSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        $valuesToPersist = [];

        foreach ($defaults as $key => $value) {
            if ($force || ! array_key_exists($key, $existing) || blank($existing[$key])) {
                $valuesToPersist[$key] = $value;
            }
        }

        if ($valuesToPersist !== []) {
            $this->settingsManager->setMany($valuesToPersist);
        }

        return [
            'persisted' => count($valuesToPersist),
            'values' => $defaults,
        ];
    }
}
