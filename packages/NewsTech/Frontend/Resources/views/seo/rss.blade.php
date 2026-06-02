<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>{{ $title }}</title>
        <link>{{ $canonicalUrl }}</link>
        <description>{{ $description }}</description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <lastBuildDate>{{ optional($articles->first()?->published_at ?? now())->toRssString() }}</lastBuildDate>
@foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ route('newstech.articles.show', ['slug' => $article->slug]) }}</link>
            <guid>{{ route('newstech.articles.show', ['slug' => $article->slug]) }}</guid>
            <description>{{ $article->meta_description ?: ($article->excerpt ?: 'Read the full article on '.$siteName.'.') }}</description>
@if ($article->published_at)
            <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
@endif
@if ($category && $article->category)
            <category>{{ $article->category->name }}</category>
@elseif ($article->category)
            <category>{{ $article->category->name }}</category>
@endif
        </item>
@endforeach
    </channel>
</rss>
