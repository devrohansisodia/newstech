<?php

use Illuminate\Support\Facades\Route;
use NewsTech\Frontend\Http\Controllers\ArticleController;
use NewsTech\Frontend\Http\Controllers\AuthorController;
use NewsTech\Frontend\Http\Controllers\CategoryController;
use NewsTech\Frontend\Http\Controllers\HomeController;
use NewsTech\Frontend\Http\Controllers\PageController;
use NewsTech\Frontend\Http\Controllers\SearchController;
use NewsTech\Frontend\Http\Controllers\SeoFeedController;
use NewsTech\Frontend\Http\Controllers\StaticPageController;
use NewsTech\Frontend\Http\Controllers\TagController;

Route::middleware(config('newstech-frontend.route.middleware'))
    ->name(config('newstech-frontend.route.name'))
    ->prefix(config('newstech-frontend.route.prefix'))
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/sitemap.xml', [SeoFeedController::class, 'sitemap'])->name('sitemap');
        Route::get('/sitemap-news.xml', [SeoFeedController::class, 'newsSitemap'])->name('sitemap-news');
        Route::get('/rss.xml', [SeoFeedController::class, 'rss'])->name('rss');
        Route::get('/robots.txt', [SeoFeedController::class, 'robots'])->name('robots');
        Route::get('/search', SearchController::class)->name('search');
        Route::get('/about', [StaticPageController::class, 'about'])->name('about');
        Route::get('/contact', [StaticPageController::class, 'contact'])->name('contact');
        Route::get('/privacy-policy', [StaticPageController::class, 'privacyPolicy'])->name('privacy-policy');
        Route::get('/terms', [StaticPageController::class, 'terms'])->name('terms');
        Route::get('/page/{slug}', [PageController::class, 'show'])->name('pages.show');
        Route::get('/news/{slug}', [ArticleController::class, 'show'])->name('articles.show');
        Route::get('/category/{slug}/rss.xml', [SeoFeedController::class, 'categoryRss'])->name('categories.rss');
        Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tags.show');
        Route::get('/author/{slug}', [AuthorController::class, 'show'])->name('authors.show');
    });
