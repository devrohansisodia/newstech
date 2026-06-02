<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;
use NewsTech\Page\Models\Page;
use NewsTech\Page\Repositories\PageRepository;

class StaticPageController
{
    use AppliesSystemSettings;

    public function __construct(protected PageRepository $pages) {}

    public function about(): View
    {
        return $this->renderStaticPage(
            slug: 'about',
            viewName: 'newstech-frontend::pages.about',
            title: 'About',
            description: 'Learn about the NewsTech editorial platform, newsroom direction, and publishing approach.',
            eyebrow: 'About NewsTech',
            heading: 'A modular newsroom platform built for fast, SEO-first publishing.',
            lead: 'NewsTech combines a Blade-first public frontend with a modular Laravel admin built for editorial teams, future growth, and clean publishing workflows.',
            routeName: 'newstech.about'
        );
    }

    public function contact(): View
    {
        return $this->renderStaticPage(
            slug: 'contact',
            viewName: 'newstech-frontend::pages.contact',
            title: 'Contact',
            description: 'Find the NewsTech contact details for editorial inquiries, partnerships, and support.',
            eyebrow: 'Contact',
            heading: 'Get in touch with the NewsTech team.',
            lead: 'Use this page for editorial, partnership, advertising, and support contact details until a custom contact workflow is added.',
            routeName: 'newstech.contact'
        );
    }

    public function privacyPolicy(): View
    {
        return $this->renderStaticPage(
            slug: 'privacy-policy',
            viewName: 'newstech-frontend::pages.privacy-policy',
            title: 'Privacy Policy',
            description: 'Read the NewsTech privacy policy covering data use, analytics, and reader privacy expectations.',
            eyebrow: 'Privacy Policy',
            heading: 'How NewsTech handles privacy, analytics, and reader data.',
            lead: 'This page explains the baseline privacy expectations for the NewsTech website, reader accounts, comments, newsletters, and analytics-ready publishing.',
            routeName: 'newstech.privacy-policy'
        );
    }

    public function terms(): View
    {
        return $this->renderStaticPage(
            slug: 'terms',
            viewName: 'newstech-frontend::pages.terms',
            title: 'Terms',
            description: 'Read the NewsTech terms covering acceptable use, content access, and site expectations.',
            eyebrow: 'Terms',
            heading: 'Terms for using the NewsTech website and published content.',
            lead: 'This page outlines general usage expectations, content access rules, and editorial publishing boundaries for the NewsTech platform.',
            routeName: 'newstech.terms'
        );
    }

    protected function renderStaticPage(
        string $slug,
        string $viewName,
        string $title,
        string $description,
        string $eyebrow,
        string $heading,
        string $lead,
        string $routeName,
    ): View {
        $this->applySystemSettings();

        $page = $this->pages->findActiveBySlug($slug);

        $canonicalUrl = route($routeName);

        if ($page) {
            return $this->renderDatabasePage(
                page: $page,
                canonicalUrl: $canonicalUrl,
                routeLabel: $title
            );
        }

        $seo = $this->buildSeoData(
            title: config('newstech.brand.name').' | '.$title,
            description: $description,
            canonicalUrl: $canonicalUrl,
            breadcrumbLabel: $title
        );

        return view($viewName, [
            'seo' => $seo,
            'pageEyebrow' => $eyebrow,
            'pageTitle' => $heading,
            'pageLead' => $lead,
        ]);
    }

    protected function renderDatabasePage(Page $page, string $canonicalUrl, string $routeLabel): View
    {
        $resolvedTitle = $page->meta_title ?: config('newstech.brand.name').' | '.$page->title;
        $resolvedDescription = $page->meta_description
            ?: 'Read the '.$page->title.' page on NewsTech.';

        $seo = $this->buildSeoData(
            title: $resolvedTitle,
            description: $resolvedDescription,
            canonicalUrl: $canonicalUrl,
            breadcrumbLabel: $routeLabel
        );

        return view('newstech-frontend::pages.dynamic', [
            'seo' => $seo,
            'page' => $page,
            'pageEyebrow' => str($page->slug)->headline()->toString(),
            'pageTitle' => $page->title,
            'pageLead' => $page->meta_description ?: 'Database-backed static content managed from the NewsTech admin panel.',
        ]);
    }

    protected function buildSeoData(
        string $title,
        string $description,
        string $canonicalUrl,
        string $breadcrumbLabel,
    ): SeoData {
        return SeoData::make($title, $description, $canonicalUrl)
            ->openGraph($title, $description)
            ->twitter('summary', $title, $description)
            ->breadcrumbs([
                ['name' => 'Home', 'url' => route('newstech.home')],
                ['name' => $breadcrumbLabel, 'url' => $canonicalUrl],
            ]);
    }
}
