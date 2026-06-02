<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;

class HomeController
{
    use AppliesSystemSettings;

    public function __construct(protected ArticleRepository $articles) {}

    public function __invoke(): View
    {
        $this->applySystemSettings();

        $heroArticle = $this->articles->heroArticle();
        $breakingArticles = $this->articles->breakingArticles();
        $featuredArticles = $this->articles->featuredArticles(exceptArticleId: $heroArticle?->getKey());
        $latestArticles = $this->articles->latestPublishedArticles(exceptArticleId: $heroArticle?->getKey());
        $categoryBlocks = $this->articles->homepageCategoryBlocks();

        $canonicalUrl = route('newstech.home');
        $heroHeadline = $heroArticle?->title ?? 'Latest News';
        $homepageLayout = config('newstech.homepage.layout', 'full_width');

        $seo = SeoData::make(
            config('newstech.brand.name').' | '.$heroHeadline,
            $heroArticle?->excerpt
                ?: 'Read the latest published coverage, breaking developments, featured stories, and category highlights from NewsTech.',
            $canonicalUrl
        )
            ->openGraph(
                config('newstech.brand.name').' | '.$heroHeadline,
                $heroArticle?->excerpt
                    ?: 'Read the latest published coverage, breaking developments, featured stories, and category highlights from NewsTech.',
                $heroArticle?->featured_image_url
            )
            ->twitter(
                'summary_large_image',
                config('newstech.brand.name').' | '.$heroHeadline,
                $heroArticle?->excerpt
                    ?: 'Read the latest published coverage, breaking developments, featured stories, and category highlights from NewsTech.',
                $heroArticle?->featured_image_url
            )
            ->structuredData([
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => config('newstech.brand.name'),
                    'url' => $canonicalUrl,
                    'description' => 'NewsTech homepage for the latest published reporting and newsroom highlights.',
                ],
            ])
            ->breadcrumbs([
                [
                    'name' => 'Home',
                    'url' => $canonicalUrl,
                ],
            ]);

        return view('newstech-frontend::home', [
            'seo' => $seo,
            'heroArticle' => $heroArticle,
            'breakingArticles' => $breakingArticles,
            'featuredArticles' => $featuredArticles,
            'latestArticles' => $latestArticles,
            'categoryBlocks' => $categoryBlocks,
            'homepageLayout' => $homepageLayout,
            'homepageSidebar' => [
                'title' => config('newstech.homepage.sidebar_title'),
                'content' => config('newstech.homepage.sidebar_content'),
                'link_label' => config('newstech.homepage.sidebar_link_label'),
                'link_url' => config('newstech.homepage.sidebar_link_url'),
            ],
        ]);
    }
}
