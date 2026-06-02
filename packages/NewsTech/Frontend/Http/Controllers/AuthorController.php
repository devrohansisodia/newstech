<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Author\Models\Author;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;

class AuthorController
{
    use AppliesSystemSettings;

    public function __construct(protected ArticleRepository $articles) {}

    public function show(string $slug): View
    {
        $this->applySystemSettings();

        /** @var ?Author $author */
        $author = Author::query()
            ->where('status', true)
            ->where('slug', $slug)
            ->first();

        if (! $author) {
            abort(404);
        }

        $articles = $this->articles->paginatePublishedByAuthor($author);
        $canonicalUrl = route('newstech.authors.show', ['slug' => $author->slug]);

        $seo = SeoData::make(
            $author->meta_title ?: config('newstech.brand.name').' | '.$author->name,
            $author->meta_description ?: ($author->bio ?: 'Latest published reporting from this NewsTech author.'),
            $canonicalUrl
        )
            ->openGraph(
                $author->meta_title ?: config('newstech.brand.name').' | '.$author->name,
                $author->meta_description ?: ($author->bio ?: 'Latest published reporting from this NewsTech author.'),
                $author->avatar_url
            )
            ->twitter(
                'summary',
                $author->meta_title ?: config('newstech.brand.name').' | '.$author->name,
                $author->meta_description ?: ($author->bio ?: 'Latest published reporting from this NewsTech author.'),
                $author->avatar_url
            )
            ->breadcrumbs([
                ['name' => 'Home', 'url' => route('newstech.home')],
                ['name' => $author->name, 'url' => $canonicalUrl],
            ]);

        return view('newstech-frontend::authors.show', [
            'seo' => $seo,
            'author' => $author,
            'articles' => $articles,
        ]);
    }
}
