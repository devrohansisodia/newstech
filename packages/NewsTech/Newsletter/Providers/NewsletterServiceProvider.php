<?php

namespace NewsTech\Newsletter\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Admin\Support\SettingsGroupManager;
use NewsTech\Core\Support\SystemSettingsManager;
use NewsTech\Newsletter\Repositories\NewsletterCampaignRecipientRepository;
use NewsTech\Newsletter\Repositories\NewsletterCampaignRepository;
use NewsTech\Newsletter\Repositories\NewsletterSubscriberRepository;
use NewsTech\Newsletter\Support\NewsletterCampaignService;
use NewsTech\Newsletter\Support\PersistNewsletterSettings;

class NewsletterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-newsletter'
        );

        $this->app->singleton(NewsletterSubscriberRepository::class);
        $this->app->singleton(NewsletterCampaignRepository::class);
        $this->app->singleton(NewsletterCampaignRecipientRepository::class);
        $this->app->singleton(NewsletterCampaignService::class);

        config()->set('menu.admin', [
            ...config('menu.admin', []),
            ...require __DIR__.'/../Config/menu.php',
        ]);

        config()->set('acl', [
            ...config('acl', []),
            ...require __DIR__.'/../Config/acl.php',
        ]);

        $this->app->afterResolving(SystemSettingsManager::class, function (SystemSettingsManager $settingsManager): void {
            $settingsManager->registerConfigMap([
                'newsletter.enabled' => 'newstech-newsletter.enabled',
                'newsletter.double_opt_in' => 'newstech-newsletter.double_opt_in',
                'newsletter.allow_resubscribe' => 'newstech-newsletter.allow_resubscribe',
                'newsletter.sender_name' => 'newstech-newsletter.sender_name',
                'newsletter.sender_email' => 'newstech-newsletter.sender_email',
                'newsletter.footer_unsubscribe_text' => 'newstech-newsletter.footer_unsubscribe_text',
            ], [
                'newstech-newsletter.enabled' => true,
                'newstech-newsletter.double_opt_in' => false,
                'newstech-newsletter.allow_resubscribe' => true,
                'newstech-newsletter.sender_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'NewsTech')),
                'newstech-newsletter.sender_email' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'newstech-newsletter.footer_unsubscribe_text' => 'You are receiving this email because you subscribed to newsletter updates. Use the unsubscribe link below to stop future campaign emails.',
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(SettingsGroupManager $settingsGroupManager): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'newstech-newsletter');

        $settingsGroupManager->register([
            'key' => 'newsletter',
            'title' => 'Newsletter Settings',
            'description' => 'Control whether newsletter subscriptions and campaign delivery are enabled, plus sender defaults and unsubscribe footer copy.',
            'icon' => 'NL',
            'sort' => 50,
            'sections' => [
                [
                    'key' => 'newsletter.delivery',
                    'name' => 'Delivery Controls',
                    'info' => 'Global toggles for subscription capture and campaign sender defaults.',
                    'fields' => [
                        [
                            'key' => 'newsletter.enabled',
                            'label' => 'Enable Newsletter',
                            'type' => 'toggle',
                            'hint' => 'When disabled, public subscription forms are hidden and new subscriptions are blocked.',
                        ],
                        [
                            'key' => 'newsletter.double_opt_in',
                            'label' => 'Double Opt In',
                            'type' => 'toggle',
                            'hint' => 'Stored for future confirmation workflows. No extra confirmation email is sent in this phase.',
                        ],
                        [
                            'key' => 'newsletter.allow_resubscribe',
                            'label' => 'Allow Resubscribe',
                            'type' => 'toggle',
                            'hint' => 'Allow an unsubscribed address to subscribe again using the public form.',
                        ],
                        [
                            'key' => 'newsletter.sender_name',
                            'label' => 'Sender Name',
                            'type' => 'text',
                            'hint' => 'Fallback sender name used for campaign mail when not overridden by mail.from.name.',
                        ],
                        [
                            'key' => 'newsletter.sender_email',
                            'label' => 'Sender Email',
                            'type' => 'text',
                            'hint' => 'Fallback sender email used for campaign mail when not overridden by mail.from.address.',
                        ],
                        [
                            'key' => 'newsletter.footer_unsubscribe_text',
                            'label' => 'Footer Unsubscribe Text',
                            'type' => 'textarea',
                            'rows' => 4,
                            'hint' => 'Displayed in campaign emails above the unsubscribe link.',
                        ],
                    ],
                ],
            ],
            'rules' => [
                'enabled' => ['required', 'boolean'],
                'double_opt_in' => ['required', 'boolean'],
                'allow_resubscribe' => ['required', 'boolean'],
                'sender_name' => ['nullable', 'string', 'max:255'],
                'sender_email' => ['nullable', 'email', 'max:255'],
                'footer_unsubscribe_text' => ['nullable', 'string'],
            ],
            'attributes' => [
                'enabled' => 'newsletter enabled',
                'double_opt_in' => 'double opt in',
                'allow_resubscribe' => 'allow resubscribe',
                'sender_name' => 'sender name',
                'sender_email' => 'sender email',
                'footer_unsubscribe_text' => 'footer unsubscribe text',
            ],
            'save' => PersistNewsletterSettings::class,
            'summary' => function (array $settingsValues): string {
                return sprintf(
                    '%s · %s · sender %s',
                    ($settingsValues['newsletter.enabled'] ?? config('newstech-newsletter.enabled')) ? 'newsletter on' : 'newsletter off',
                    ($settingsValues['newsletter.allow_resubscribe'] ?? config('newstech-newsletter.allow_resubscribe')) ? 'resubscribe on' : 'resubscribe off',
                    $settingsValues['newsletter.sender_email'] ?? config('newstech-newsletter.sender_email')
                );
            },
        ]);
    }
}
