<?php

use App\Providers\AppServiceProvider;
use NewsTech\Admin\Providers\AdminServiceProvider;
use NewsTech\Advertisement\Providers\AdvertisementServiceProvider;
use NewsTech\Article\Providers\ArticleServiceProvider;
use NewsTech\Author\Providers\AuthorServiceProvider;
use NewsTech\Bookmark\Providers\BookmarkServiceProvider;
use NewsTech\Category\Providers\CategoryServiceProvider;
use NewsTech\Comment\Providers\CommentServiceProvider;
use NewsTech\Core\Providers\CoreServiceProvider;
use NewsTech\Frontend\Providers\FrontendServiceProvider;
use NewsTech\Installer\Providers\InstallerServiceProvider;
use NewsTech\Media\Providers\MediaServiceProvider;
use NewsTech\Menu\Providers\MenuServiceProvider;
use NewsTech\Newsletter\Providers\NewsletterServiceProvider;
use NewsTech\Page\Providers\PageServiceProvider;
use NewsTech\Reader\Providers\ReaderServiceProvider;
use NewsTech\Seo\Providers\SeoServiceProvider;
use NewsTech\Tag\Providers\TagServiceProvider;

return [
    AppServiceProvider::class,
    CoreServiceProvider::class,
    InstallerServiceProvider::class,
    FrontendServiceProvider::class,
    MenuServiceProvider::class,
    AdminServiceProvider::class,
    MediaServiceProvider::class,
    AdvertisementServiceProvider::class,
    AuthorServiceProvider::class,
    ArticleServiceProvider::class,
    BookmarkServiceProvider::class,
    CategoryServiceProvider::class,
    CommentServiceProvider::class,
    NewsletterServiceProvider::class,
    PageServiceProvider::class,
    ReaderServiceProvider::class,
    SeoServiceProvider::class,
    TagServiceProvider::class,
];
