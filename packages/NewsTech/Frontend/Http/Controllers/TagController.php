<?php

namespace NewsTech\Frontend\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Article\Repositories\ArticleRepository;
use NewsTech\Core\Support\SeoData;
use NewsTech\Frontend\Support\AppliesSystemSettings;
use NewsTech\Tag\Models\Tag;

class TagController
{
    use AppliesSystemSettings;

    public function __construct(protected ArticleRepository $articles) {}

    public function show(string $slug): View
    {
        $this->applySystemSettings();

        /** @var ?Tag $tag */
        $tag = Tag::query()
            ->where('status', true)
            ->where('slug', $slug)
            ->first();

        if (! $tag) {
            abort(404);
        }

        $articles = $this->articles->paginatePublishedByTag($tag);
        $canonicalUrl = route('newstech.tags.show', ['slug' => $tag->slug]);

        $seo = SeoData::make(
            $tag->meta_title ?: config('newstech.brand.name').' | '.$tag->name,
            $tag->meta_description ?: ($tag->description ?: 'Latest published articles filed under this NewsTech topic tag.'),
            $canonicalUrl
        )
            ->openGraph(
                $tag->meta_title ?: config('newstech.brand.name').' | '.$tag->name,
                $tag->meta_description ?: ($tag->description ?: 'Latest published articles filed under this NewsTech topic tag.')
            )
            ->twitter(
                'summary',
                $tag->meta_title ?: config('newstech.brand.name').' | '.$tag->name,
                $tag->meta_description ?: ($tag->description ?: 'Latest published articles filed under this NewsTech topic tag.')
            )
            ->breadcrumbs([
                ['name' => 'Home', 'url' => route('newstech.home')],
                ['name' => $tag->name, 'url' => $canonicalUrl],
            ]);

        return view('newstech-frontend::tags.show', [
            'seo' => $seo,
            'tag' => $tag,
            'articles' => $articles,
        ]);
    }
}
