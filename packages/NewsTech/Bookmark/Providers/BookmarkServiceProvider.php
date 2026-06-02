<?php

namespace NewsTech\Bookmark\Providers;

use Illuminate\Support\ServiceProvider;
use NewsTech\Bookmark\Repositories\BookmarkFolderRepository;
use NewsTech\Bookmark\Repositories\BookmarkRepository;
use NewsTech\Bookmark\Repositories\ReaderArticleHistoryRepository;

class BookmarkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BookmarkRepository::class);
        $this->app->singleton(BookmarkFolderRepository::class);
        $this->app->singleton(ReaderArticleHistoryRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
