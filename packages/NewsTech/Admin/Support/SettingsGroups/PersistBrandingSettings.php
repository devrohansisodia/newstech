<?php

namespace NewsTech\Admin\Support\SettingsGroups;

use Illuminate\Http\Request;
use NewsTech\Core\Support\MediaManager;
use NewsTech\Core\Support\SystemSettingsManager;

class PersistBrandingSettings
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function __invoke(
        Request $request,
        array $validated,
        SystemSettingsManager $systemSettingsManager,
        MediaManager $mediaManager,
    ): void {
        $settings = [
            'website.identity.site_name' => $validated['site_name'],
        ];

        $existingLogoPath = $systemSettingsManager->get('website.identity.logo_path');
        $existingFooterLogoPath = $systemSettingsManager->get('website.identity.footer_logo_path');

        if ($request->hasFile('logo')) {
            $settings['website.identity.logo_path'] = $mediaManager->store(
                $request->file('logo'),
                'newstech/settings/branding'
            );

            if (is_string($existingLogoPath) && $existingLogoPath !== '') {
                $mediaManager->delete($existingLogoPath);
            }
        } else {
            $settings['website.identity.logo_path'] = $this->resolvedSelectedPath(
                $validated['logo'] ?? null,
                $existingLogoPath
            );
        }

        if ($request->hasFile('footer_logo')) {
            $settings['website.identity.footer_logo_path'] = $mediaManager->store(
                $request->file('footer_logo'),
                'newstech/settings/branding'
            );

            if (is_string($existingFooterLogoPath) && $existingFooterLogoPath !== '') {
                $mediaManager->delete($existingFooterLogoPath);
            }
        } else {
            $settings['website.identity.footer_logo_path'] = $this->resolvedSelectedPath(
                $validated['footer_logo'] ?? null,
                $existingFooterLogoPath
            );
        }

        $systemSettingsManager->setMany($settings);
    }

    protected function resolvedSelectedPath(mixed $value, mixed $existingValue): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        return is_string($existingValue) && $existingValue !== ''
            ? $existingValue
            : null;
    }
}
