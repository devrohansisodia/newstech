<?php

namespace NewsTech\Frontend\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Support\SystemSettingsManager;
use NewsTech\Menu\Support\FrontendMenuResolver;

class FrontendServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'newstech-frontend'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        Route::middleware(config('newstech-frontend.route.middleware'))
            ->name(config('newstech-frontend.route.name'))
            ->prefix(config('newstech-frontend.route.prefix'))
            ->group(function (): void {
                require base_path('packages/NewsTech/Frontend/Routes/bookmarks.php');
                require base_path('packages/NewsTech/Frontend/Routes/comments.php');
                require base_path('packages/NewsTech/Frontend/Routes/newsletter.php');
                require base_path('packages/NewsTech/Frontend/Routes/reader.php');
                require base_path('packages/NewsTech/Frontend/Routes/advertisements.php');
            });
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'newstech-frontend');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'newstech-frontend');
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components/advertisement', 'newstech-advertisement');

        View::composer('newstech-frontend::*', function (ViewInstance $view): void {
            /** @var FrontendMenuResolver $frontendMenuResolver */
            $frontendMenuResolver = app(FrontendMenuResolver::class);
            app(SystemSettingsManager::class)->bootConfig();

            $fallbackStaticPages = [
                ['label' => 'About', 'route' => 'newstech.about'],
                ['label' => 'Contact', 'route' => 'newstech.contact'],
                ['label' => 'Privacy Policy', 'route' => 'newstech.privacy-policy'],
                ['label' => 'Terms', 'route' => 'newstech.terms'],
            ];

            $navigationCategories = Category::query()
                ->where('status', true)
                ->ordered()
                ->limit(6)
                ->get(['id', 'name', 'slug']);

            $headerMenuItems = $frontendMenuResolver->itemsForLocation('header');
            $footerMenuItems = $frontendMenuResolver->itemsForLocation('footer');
            $mobileMenuItems = $frontendMenuResolver->itemsForLocation('mobile');

            $view->with([
                'frontendStaticPages' => $fallbackStaticPages,
                'frontendNavigationCategories' => $navigationCategories,
                'frontendHeaderMenuItems' => $headerMenuItems,
                'frontendFooterMenuItems' => $footerMenuItems,
                'frontendMobileMenuItems' => $mobileMenuItems,
                'frontendFallbackHeaderMenuItems' => collect([
                    ['label' => 'Home', 'url' => route('newstech.home'), 'target' => '_self', 'children' => collect()],
                    ...collect($fallbackStaticPages)->map(fn (array $page): array => [
                        'label' => $page['label'],
                        'url' => route($page['route']),
                        'target' => '_self',
                        'children' => collect(),
                    ])->all(),
                ]),
                'frontendFallbackFooterMenuItems' => collect($fallbackStaticPages)->map(fn (array $page): array => [
                    'label' => $page['label'],
                    'url' => route($page['route']),
                    'target' => '_self',
                    'children' => collect(),
                ]),
            ]);
        });
    }
}
