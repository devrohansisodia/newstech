<?php

namespace NewsTech\Seo\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Admin\Support\SettingsGroupManager;
use NewsTech\Core\Support\SystemSettingsManager;
use NewsTech\Seo\Support\PersistSeoSettings;
use NewsTech\Seo\Support\SeoAnalyzer;
use NewsTech\Seo\Support\SeoPreviewBuilder;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-seo'
        );

        $this->app->singleton(SeoPreviewBuilder::class);
        $this->app->singleton(SeoAnalyzer::class);
    }

    public function boot(SystemSettingsManager $systemSettingsManager): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $systemSettingsManager->registerConfigMap([
            'seo.site_title_suffix' => 'newstech-seo.site_title_suffix',
            'seo.default_meta_description' => 'newstech-seo.default_meta_description',
            'seo.enable_real_time_checks' => 'newstech-seo.enable_real_time_checks',
            'seo.score_threshold_warning' => 'newstech-seo.score_threshold_warning',
            'seo.enable_social_preview' => 'newstech-seo.enable_social_preview',
        ], [
            'newstech-seo.site_title_suffix' => ' | NewsTech',
            'newstech-seo.default_meta_description' => 'Read the latest NewsTech coverage, analysis, and editorial updates.',
            'newstech-seo.enable_real_time_checks' => true,
            'newstech-seo.score_threshold_warning' => 80,
            'newstech-seo.enable_social_preview' => true,
        ]);

        if ($this->app->bound(SettingsGroupManager::class)) {
            $this->registerSettingsGroup($this->app->make(SettingsGroupManager::class));
        }
    }

    protected function registerSettingsGroup(SettingsGroupManager $settingsGroupManager): void
    {
        $settingsGroupManager->register([
            'key' => 'seo',
            'title' => 'SEO Toolkit',
            'description' => 'Configure real-time SEO analysis defaults, preview fallbacks, and the warning threshold used in editor guidance.',
            'icon' => 'SEO',
            'sort' => 40,
            'sections' => [[
                'name' => 'Realtime Analysis',
                'info' => 'Controls the default snippet fallback text, whether live scoring stays enabled, and the threshold the admin panel treats as healthy.',
                'fields' => [
                    [
                        'key' => 'seo.site_title_suffix',
                        'label' => 'Site Title Suffix',
                        'type' => 'text',
                        'placeholder' => ' | NewsTech',
                        'hint' => 'Appended in snippet previews when a dedicated meta title is not set.',
                    ],
                    [
                        'key' => 'seo.default_meta_description',
                        'label' => 'Default Meta Description',
                        'type' => 'textarea',
                        'rows' => 4,
                        'hint' => 'Used in previews when content has no dedicated meta description or excerpt fallback.',
                    ],
                    [
                        'key' => 'seo.enable_real_time_checks',
                        'label' => 'Enable real-time SEO checks',
                        'type' => 'toggle',
                        'hint' => 'If disabled, the editor panel still renders but pauses automatic score refreshes.',
                    ],
                    [
                        'key' => 'seo.score_threshold_warning',
                        'label' => 'Score threshold warning',
                        'type' => 'number',
                        'hint' => 'Scores below this threshold are highlighted as needing more attention in the editor.',
                    ],
                    [
                        'key' => 'seo.enable_social_preview',
                        'label' => 'Enable social preview card',
                        'type' => 'toggle',
                        'hint' => 'Show or hide the social-style preview in the real-time SEO panel.',
                    ],
                ],
            ]],
            'rules' => [
                'site_title_suffix' => ['nullable', 'string', 'max:255'],
                'default_meta_description' => ['nullable', 'string'],
                'enable_real_time_checks' => ['required', 'boolean'],
                'score_threshold_warning' => ['required', 'integer', 'min:0', 'max:100'],
                'enable_social_preview' => ['required', 'boolean'],
            ],
            'attributes' => [
                'site_title_suffix' => 'site title suffix',
                'default_meta_description' => 'default meta description',
                'enable_real_time_checks' => 'enable real-time SEO checks',
                'score_threshold_warning' => 'score threshold warning',
                'enable_social_preview' => 'enable social preview card',
            ],
            'save' => PersistSeoSettings::class,
            'summary' => function (array $settingsValues): string {
                $threshold = (int) ($settingsValues['seo.score_threshold_warning'] ?? config('newstech-seo.score_threshold_warning'));
                $status = filter_var(
                    $settingsValues['seo.enable_real_time_checks'] ?? config('newstech-seo.enable_real_time_checks'),
                    FILTER_VALIDATE_BOOL
                )
                    ? 'live checks enabled'
                    : 'live checks paused';

                return sprintf('%s · threshold %d · social preview %s', $status, $threshold, filter_var(
                    $settingsValues['seo.enable_social_preview'] ?? config('newstech-seo.enable_social_preview'),
                    FILTER_VALIDATE_BOOL
                ) ? 'on' : 'off');
            },
        ]);
    }
}
