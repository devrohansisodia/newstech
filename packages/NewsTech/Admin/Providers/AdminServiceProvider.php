<?php

namespace NewsTech\Admin\Providers;

use Closure;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use NewsTech\Admin\Support\AdminMenuResolver;
use NewsTech\Admin\Support\SettingsGroupManager;
use NewsTech\Admin\Support\SettingsGroups\PersistBrandingSettings;
use NewsTech\Admin\Support\SettingsGroups\PersistCommentSettings;
use NewsTech\Admin\Support\SettingsGroups\PersistHomepageSettings;
use NewsTech\Admin\Support\SystemConfigResolver;
use NewsTech\Core\Support\AclTreeBuilder;
use NewsTech\Core\Support\MediaManager;
use NewsTech\Core\Support\MenuTreeBuilder;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-admin'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/menu.php',
            'menu.admin'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../Config/acl.php',
            'acl'
        );

        $this->app->singleton(AdminMenuResolver::class);
        $this->app->singleton(SettingsGroupManager::class);
        $this->app->singleton(SystemConfigResolver::class);

        $this->mergeConfigFrom(
            __DIR__.'/../Config/system.php',
            'newstech-admin-system'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(
        MenuTreeBuilder $menuTreeBuilder,
        AclTreeBuilder $aclTreeBuilder,
        AdminMenuResolver $adminMenuResolver,
        SystemConfigResolver $systemConfigResolver,
        SettingsGroupManager $settingsGroupManager,
    ): void {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        Route::middleware(config('newstech-admin.route.middleware'))
            ->name(config('newstech-admin.route.name'))
            ->prefix(config('newstech-admin.route.prefix'))
            ->group(function (): void {
                Route::middleware('admin.auth')->group(function (): void {
                    require base_path('packages/NewsTech/Admin/Routes/foundation.php');
                    require base_path('packages/NewsTech/Admin/Routes/profile.php');
                    require base_path('packages/NewsTech/Admin/Routes/articles.php');
                    require base_path('packages/NewsTech/Admin/Routes/readers.php');
                    require base_path('packages/NewsTech/Admin/Routes/comments.php');
                    require base_path('packages/NewsTech/Admin/Routes/categories.php');
                    require base_path('packages/NewsTech/Admin/Routes/tags.php');
                    require base_path('packages/NewsTech/Admin/Routes/authors.php');
                    require base_path('packages/NewsTech/Admin/Routes/pages.php');
                    require base_path('packages/NewsTech/Admin/Routes/menus.php');
                    require base_path('packages/NewsTech/Admin/Routes/media.php');
                    require base_path('packages/NewsTech/Admin/Routes/newsletter.php');
                    require base_path('packages/NewsTech/Admin/Routes/seo.php');
                    require base_path('packages/NewsTech/Admin/Routes/advertisements.php');
                });
            });

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'newstech-admin');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'newstech-admin');

        View::composer('newstech-admin::*', function ($view) use ($menuTreeBuilder, $aclTreeBuilder, $adminMenuResolver): void {
            $adminAcl = $aclTreeBuilder->build(config('acl', []));
            $adminMenu = $adminMenuResolver->filterByAcl(
                $menuTreeBuilder->build(config('menu.admin', [])),
                $adminAcl
            );
            $adminUser = auth(config('newstech-admin.auth.guard'))->user();

            $view->with([
                'adminMenu' => $adminMenu,
                'adminMenuItemCount' => $menuTreeBuilder->count($adminMenu),
                'adminAcl' => $adminAcl,
                'adminAclNodeCount' => $aclTreeBuilder->count($adminAcl),
                'currentAdminUser' => $adminUser,
            ]);
        });

        $this->registerSettingsGroups($systemConfigResolver, $settingsGroupManager);
    }

    protected function registerSettingsGroups(
        SystemConfigResolver $systemConfigResolver,
        SettingsGroupManager $settingsGroupManager,
    ): void {
        $systemConfig = collect($systemConfigResolver->build(config('newstech-admin-system', [])));
        $websiteGroup = $systemConfig->firstWhere('key', 'website');
        $commentsGroup = $systemConfig->firstWhere('key', 'comments');

        $brandingSection = $websiteGroup['children'][0] ?? null;
        $homepageSection = $websiteGroup['children'][1] ?? null;
        $commentsSections = $commentsGroup['children'] ?? [];

        if (is_array($brandingSection)) {
            $settingsGroupManager->register([
                'key' => 'branding',
                'title' => 'Current Branding',
                'description' => 'Manage the public site name and the header and footer logos used across the frontend shell.',
                'icon' => 'BR',
                'sort' => 10,
                'sections' => [$brandingSection],
                'rules' => [
                    'site_name' => ['required', 'string', 'max:255'],
                    'logo' => $this->mediaSelectionRules('logo'),
                    'footer_logo' => $this->mediaSelectionRules('footer_logo'),
                ],
                'messages' => [
                    'site_name.required' => 'Enter the site name used in the frontend branding.',
                    'logo.mimes' => 'The site logo must be a JPG, JPEG, PNG, or WebP image.',
                    'footer_logo.mimes' => 'The footer logo must be a JPG, JPEG, PNG, or WebP image.',
                ],
                'attributes' => [
                    'site_name' => 'site name',
                    'logo' => 'site logo',
                    'footer_logo' => 'footer logo',
                ],
                'save' => PersistBrandingSettings::class,
                'summary' => function (array $settingsValues): string {
                    return sprintf(
                        '%s · Header %s · Footer %s',
                        $settingsValues['website.identity.site_name'] ?: config('newstech.brand.name'),
                        filled($settingsValues['website.identity.logo_path'] ?? null) ? 'custom logo' : 'fallback mark',
                        filled($settingsValues['website.identity.footer_logo_path'] ?? null) ? 'custom logo' : 'primary logo'
                    );
                },
            ]);
        }

        if (is_array($homepageSection)) {
            $settingsGroupManager->register([
                'key' => 'homepage',
                'title' => 'Homepage Layout',
                'description' => 'Control the homepage layout mode and the optional right-rail content block.',
                'icon' => 'HP',
                'sort' => 20,
                'sections' => [$homepageSection],
                'rules' => [
                    'homepage_layout' => ['required', 'in:full_width,two_column_70_30'],
                    'homepage_sidebar_title' => ['nullable', 'string', 'max:255'],
                    'homepage_sidebar_content' => ['nullable', 'string'],
                    'homepage_sidebar_link_label' => ['nullable', 'string', 'max:255'],
                    'homepage_sidebar_link_url' => ['nullable', 'url', 'max:2048'],
                ],
                'messages' => [
                    'homepage_layout.required' => 'Choose how the homepage content area should be laid out.',
                    'homepage_layout.in' => 'Choose a valid homepage layout option.',
                    'homepage_sidebar_link_url.url' => 'Enter a valid sidebar link URL, including https:// when needed.',
                ],
                'attributes' => [
                    'homepage_layout' => 'homepage layout',
                    'homepage_sidebar_title' => 'homepage sidebar title',
                    'homepage_sidebar_content' => 'homepage sidebar content',
                    'homepage_sidebar_link_label' => 'homepage sidebar link label',
                    'homepage_sidebar_link_url' => 'homepage sidebar link URL',
                ],
                'save' => PersistHomepageSettings::class,
                'summary' => function (array $settingsValues): string {
                    $layout = ($settingsValues['website.homepage.layout'] ?? config('newstech.homepage.layout')) === 'two_column_70_30'
                        ? '70 / 30 sidebar layout'
                        : 'full width layout';

                    $sidebarTitle = $settingsValues['website.homepage.sidebar_title'] ?: 'no sidebar title set';

                    return $layout.' · '.$sidebarTitle;
                },
            ]);
        }

        if ($commentsSections !== []) {
            $settingsGroupManager->register([
                'key' => 'comments',
                'title' => 'Comment Controls',
                'description' => 'Manage comment availability, moderation rules, and the built-in anti-spam thresholds.',
                'icon' => 'CO',
                'sort' => 30,
                'sections' => $commentsSections,
                'rules' => [
                    'comments_enabled' => ['required', 'boolean'],
                    'require_moderation' => ['required', 'boolean'],
                    'guest_comments_enabled' => ['required', 'boolean'],
                    'website_field_enabled' => ['required', 'boolean'],
                    'min_comment_length' => ['required', 'integer', 'min:1', 'max:1000'],
                    'max_comment_length' => ['required', 'integer', 'min:5', 'max:10000', 'gte:min_comment_length'],
                    'max_links_per_comment' => ['required', 'integer', 'min:0', 'max:100'],
                    'blocked_words' => ['nullable', 'string'],
                    'blocked_emails' => ['nullable', 'string'],
                    'blocked_ips' => ['nullable', 'string'],
                    'auto_reject_spam' => ['required', 'boolean'],
                    'throttle_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
                ],
                'messages' => [
                    'max_comment_length.gte' => 'The maximum comment length must be greater than or equal to the minimum comment length.',
                ],
                'attributes' => [
                    'comments_enabled' => 'comments enabled',
                    'require_moderation' => 'require moderation',
                    'guest_comments_enabled' => 'guest comments enabled',
                    'website_field_enabled' => 'website field enabled',
                    'min_comment_length' => 'minimum comment length',
                    'max_comment_length' => 'maximum comment length',
                    'max_links_per_comment' => 'maximum links per comment',
                    'blocked_words' => 'blocked words',
                    'blocked_emails' => 'blocked emails',
                    'blocked_ips' => 'blocked IP addresses',
                    'auto_reject_spam' => 'automatically reject spam',
                    'throttle_seconds' => 'throttle window',
                ],
                'save' => PersistCommentSettings::class,
                'summary' => function (array $settingsValues): string {
                    return sprintf(
                        '%s · %s · %s second throttle',
                        ($settingsValues['comments.enabled'] ?? config('newstech-comment.enabled')) ? 'comments open' : 'comments closed',
                        ($settingsValues['comments.require_moderation'] ?? config('newstech-comment.require_moderation')) ? 'moderation required' : 'auto-approve clean comments',
                        $settingsValues['comments.throttle_seconds'] ?? config('newstech-comment.throttle_seconds')
                    );
                },
            ]);
        }
    }

    /**
     * @return array<int, mixed>
     */
    protected function mediaSelectionRules(string $field): array
    {
        return [
            'nullable',
            function (string $attribute, mixed $value, Closure $fail) use ($field): void {
                $mediaManager = app(MediaManager::class);
                $request = request();

                if ($request->hasFile($field)) {
                    $validator = validator(
                        [$field => $request->file($field)],
                        [$field => $mediaManager->imageValidationRules(required: false)]
                    );

                    if ($validator->fails()) {
                        foreach ($validator->errors()->get($field) as $message) {
                            $fail($message);
                        }
                    }

                    return;
                }

                if ($value === null || $value === '') {
                    return;
                }

                if (! is_string($value) || mb_strlen($value) > 2048) {
                    $fail('Select a valid media asset.');
                }
            },
        ];
    }
}
