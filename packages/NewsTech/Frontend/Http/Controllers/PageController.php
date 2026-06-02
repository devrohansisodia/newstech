<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;
use NewsTech\Page\Repositories\PageRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController
{
    use AppliesSystemSettings;

    public function __construct(protected PageRepository $pages) {}

    public function show(string $slug): View
    {
        $this->applySystemSettings();

        $page = $this->pages->findActiveBySlug($slug);

        if (! $page) {
            throw new NotFoundHttpException;
        }

        $canonicalUrl = route('newstech.pages.show', ['slug' => $page->slug]);
        $description = $page->meta_description ?: 'Read the '.$page->title.' page on NewsTech.';
        $seoTitle = $page->meta_title ?: config('newstech.brand.name').' | '.$page->title;

        $seo = SeoData::make($seoTitle, $description, $canonicalUrl)
            ->openGraph($seoTitle, $description)
            ->twitter('summary', $seoTitle, $description)
            ->breadcrumbs([
                ['name' => 'Home', 'url' => route('newstech.home')],
                ['name' => $page->title, 'url' => $canonicalUrl],
            ]);

        return view('newstech-frontend::pages.dynamic', [
            'seo' => $seo,
            'page' => $page,
            'pageEyebrow' => 'Page',
            'pageTitle' => $page->title,
            'pageLead' => $description,
        ]);
    }
}
