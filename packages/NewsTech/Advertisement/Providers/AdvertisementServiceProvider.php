<?php

namespace NewsTech\Advertisement\Providers;

use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use NewsTech\Admin\Support\SettingsGroupManager;
use NewsTech\Advertisement\Repositories\AdvertisementRepository;
use NewsTech\Advertisement\Support\AdvertisementRenderer;
use NewsTech\Advertisement\Support\AdvertisementSlotManager;
use NewsTech\Advertisement\Support\PersistAdvertisementSettings;
use NewsTech\Core\Support\RenderEventManager;
use NewsTech\Core\Support\SystemSettingsManager;

class AdvertisementServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-advertisement'
        );

        config()->set('menu.admin', [
            ...config('menu.admin', []),
            ...require __DIR__.'/../Config/menu.php',
        ]);

        config()->set('acl', [
            ...config('acl', []),
            ...require __DIR__.'/../Config/acl.php',
        ]);

        $this->app->singleton(AdvertisementRepository::class);
        $this->app->singleton(AdvertisementSlotManager::class);
        $this->app->singleton(AdvertisementRenderer::class);

        $this->app->afterResolving(SystemSettingsManager::class, function (SystemSettingsManager $settingsManager): void {
            $settingsManager->registerConfigMap([
                'advertisements.enabled' => 'newstech-advertisement.enabled',
                'advertisements.placeholders_enabled' => 'newstech-advertisement.placeholders_enabled',
                'advertisements.track_impressions' => 'newstech-advertisement.track_impressions',
                'advertisements.track_clicks' => 'newstech-advertisement.track_clicks',
                'advertisements.default_open_in_new_tab' => 'newstech-advertisement.default_open_in_new_tab',
                'advertisements.default_nofollow' => 'newstech-advertisement.default_nofollow',
                'advertisements.default_sponsored' => 'newstech-advertisement.default_sponsored',
            ], [
                'newstech-advertisement.enabled' => true,
                'newstech-advertisement.placeholders_enabled' => false,
                'newstech-advertisement.track_impressions' => true,
                'newstech-advertisement.track_clicks' => true,
                'newstech-advertisement.default_open_in_new_tab' => true,
                'newstech-advertisement.default_nofollow' => false,
                'newstech-advertisement.default_sponsored' => true,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(
        RenderEventManager $renderEventManager,
        SettingsGroupManager $settingsGroupManager,
        AdvertisementSlotManager $slotManager,
        AdvertisementRenderer $renderer,
    ): void {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'newstech-advertisement');

        foreach ($slotManager->all() as $slotKey => $slot) {
            foreach ($slot['render_events'] ?? [] as $eventDefinition) {
                $renderEventManager->register(
                    $eventDefinition['key'],
                    fn (array $payload = []): HtmlString => $renderer->renderSlot(
                        $slotKey,
                        (bool) ($eventDefinition['compact'] ?? false),
                        $payload,
                    )
                );
            }
        }

        $settingsGroupManager->register([
            'key' => 'advertisement',
            'title' => 'Advertisement Settings',
            'description' => 'Control managed ad rendering, placeholder fallback behavior, click/impression tracking, and default link attributes.',
            'icon' => 'AD',
            'sort' => 40,
            'sections' => [
                [
                    'key' => 'advertisement.rendering',
                    'name' => 'Rendering And Tracking',
                    'info' => 'Global controls for managed advertisements and placeholder fallback behavior.',
                    'fields' => [
                        [
                            'key' => 'advertisements.enabled',
                            'label' => 'Enable Advertisements',
                            'type' => 'toggle',
                            'hint' => 'Turn all managed advertisement rendering on or off across the frontend.',
                        ],
                        [
                            'key' => 'advertisements.placeholders_enabled',
                            'label' => 'Enable Placeholder Fallbacks',
                            'type' => 'toggle',
                            'hint' => 'Render placeholder slots when no managed advertisement is available for a configured slot.',
                        ],
                        [
                            'key' => 'advertisements.track_impressions',
                            'label' => 'Track Impressions',
                            'type' => 'toggle',
                            'hint' => 'Increment the advertisement impression counter each time an active managed ad is rendered.',
                        ],
                        [
                            'key' => 'advertisements.track_clicks',
                            'label' => 'Track Clicks',
                            'type' => 'toggle',
                            'hint' => 'Use the NewsTech click redirect route to count advertisement clicks before redirecting visitors.',
                        ],
                    ],
                ],
                [
                    'key' => 'advertisement.defaults',
                    'name' => 'Default Link Attributes',
                    'info' => 'Default values used when creating new managed advertisements.',
                    'fields' => [
                        [
                            'key' => 'advertisements.default_open_in_new_tab',
                            'label' => 'Open New Ads In New Tab',
                            'type' => 'toggle',
                            'hint' => 'Sets the default open-in-new-tab toggle for newly created ads.',
                        ],
                        [
                            'key' => 'advertisements.default_nofollow',
                            'label' => 'Default Nofollow',
                            'type' => 'toggle',
                            'hint' => 'Sets the default nofollow attribute for newly created ads.',
                        ],
                        [
                            'key' => 'advertisements.default_sponsored',
                            'label' => 'Default Sponsored',
                            'type' => 'toggle',
                            'hint' => 'Sets the default sponsored attribute for newly created ads.',
                        ],
                    ],
                ],
            ],
            'rules' => [
                'advertisements_enabled' => ['required', 'boolean'],
                'placeholders_enabled' => ['required', 'boolean'],
                'track_impressions' => ['required', 'boolean'],
                'track_clicks' => ['required', 'boolean'],
                'default_open_in_new_tab' => ['required', 'boolean'],
                'default_nofollow' => ['required', 'boolean'],
                'default_sponsored' => ['required', 'boolean'],
            ],
            'attributes' => [
                'advertisements_enabled' => 'advertisements enabled',
                'placeholders_enabled' => 'placeholder fallbacks enabled',
                'track_impressions' => 'track impressions',
                'track_clicks' => 'track clicks',
                'default_open_in_new_tab' => 'default open in new tab',
                'default_nofollow' => 'default nofollow',
                'default_sponsored' => 'default sponsored',
            ],
            'save' => PersistAdvertisementSettings::class,
            'summary' => function (array $settingsValues): string {
                return sprintf(
                    '%s · %s · %s',
                    ($settingsValues['advertisements.enabled'] ?? config('newstech-advertisement.enabled')) ? 'ads enabled' : 'ads disabled',
                    ($settingsValues['advertisements.placeholders_enabled'] ?? config('newstech-advertisement.placeholders_enabled')) ? 'placeholder fallback on' : 'placeholder fallback off',
                    ($settingsValues['advertisements.track_clicks'] ?? config('newstech-advertisement.track_clicks')) ? 'click tracking on' : 'click tracking off'
                );
            },
        ]);

        $renderEventManager->registerView('admin.dashboard.cards.after', 'newstech-advertisement::dashboard-card');
    }
}
