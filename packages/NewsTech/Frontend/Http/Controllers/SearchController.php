<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Author\Models\Author;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;
use NewsTech\Tag\Models\Tag;

class SearchController
{
    use AppliesSystemSettings;

    public function __construct(protected ArticleRepository $articles) {}

    public function __invoke(Request $request): View
    {
        $this->applySystemSettings();

        $filters = [
            'q' => trim((string) $request->string('q')),
            'category' => trim((string) $request->string('category')),
            'author' => trim((string) $request->string('author')),
            'tag' => trim((string) $request->string('tag')),
        ];

        $results = $this->articles->searchPublishedArticles($filters);
        $canonicalUrl = route('newstech.search');
        $searchLabel = $filters['q'] !== '' ? 'Search: '.$filters['q'] : 'Search';

        $seo = SeoData::make(
            config('newstech.brand.name').' | '.$searchLabel,
            $filters['q'] !== ''
                ? 'Search published NewsTech articles for "'.$filters['q'].'".'
                : 'Search published NewsTech articles by keyword, category, author, or tag.',
            $canonicalUrl
        )
            ->robots('noindex,follow')
            ->openGraph(
                config('newstech.brand.name').' | '.$searchLabel,
                $filters['q'] !== ''
                    ? 'Search published NewsTech articles for "'.$filters['q'].'".'
                    : 'Search published NewsTech articles by keyword, category, author, or tag.'
            )
            ->twitter(
                'summary',
                config('newstech.brand.name').' | '.$searchLabel,
                $filters['q'] !== ''
                    ? 'Search published NewsTech articles for "'.$filters['q'].'".'
                    : 'Search published NewsTech articles by keyword, category, author, or tag.'
            )
            ->breadcrumbs([
                ['name' => 'Home', 'url' => route('newstech.home')],
                ['name' => 'Search', 'url' => $canonicalUrl],
            ]);

        return view('newstech-frontend::search.index', [
            'seo' => $seo,
            'filters' => $filters,
            'results' => $results,
            'categoryOptions' => Category::query()->where('status', true)->ordered()->pluck('name', 'slug')->all(),
            'authorOptions' => Author::query()->where('status', true)->orderBy('name')->pluck('name', 'slug')->all(),
            'tagOptions' => Tag::query()->where('status', true)->orderBy('name')->pluck('name', 'slug')->all(),
        ]);
    }
}
