<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Category\Models\Category;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;

class CategoryController
{
    use AppliesSystemSettings;

    public function __construct(protected ArticleRepository $articles) {}

    public function show(string $slug): View
    {
        $this->applySystemSettings();

        /** @var ?Category $category */
        $category = Category::query()
            ->where('status', true)
            ->where('slug', $slug)
            ->first();

        if (! $category) {
            abort(404);
        }

        $articles = $this->articles->paginatePublishedByCategory($category);
        $canonicalUrl = route('newstech.categories.show', ['slug' => $category->slug]);

        $seo = SeoData::make(
            $category->meta_title ?: config('newstech.brand.name').' | '.$category->name,
            $category->meta_description ?: ($category->description ?: 'Latest published articles from this NewsTech category.'),
            $canonicalUrl
        )
            ->openGraph(
                $category->meta_title ?: config('newstech.brand.name').' | '.$category->name,
                $category->meta_description ?: ($category->description ?: 'Latest published articles from this NewsTech category.')
            )
            ->twitter(
                'summary',
                $category->meta_title ?: config('newstech.brand.name').' | '.$category->name,
                $category->meta_description ?: ($category->description ?: 'Latest published articles from this NewsTech category.')
            )
            ->breadcrumbs([
                ['name' => 'Home', 'url' => route('newstech.home')],
                ['name' => $category->name, 'url' => $canonicalUrl],
            ]);

        return view('newstech-frontend::categories.show', [
            'seo' => $seo,
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}
