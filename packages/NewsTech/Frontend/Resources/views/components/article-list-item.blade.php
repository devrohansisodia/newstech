@props([
    'article',
])

@php
    $articleUrl = route('newstech.articles.show', ['slug' => $article->slug]);
@endphp

<article class="rounded-2xl border border-stone-200 bg-white p-4 transition hover:border-amber-300 hover:bg-amber-50/40">
    <a href="{{ $articleUrl }}" class="space-y-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100">
        <x-newstech-frontend::article-meta :article="$article" compact />

        <h3 class="text-lg font-bold tracking-tight text-stone-950">
            {{ $article->title }}
        </h3>

        @if ($article->excerpt)
            <p class="nt-line-clamp-4 text-sm leading-7 text-stone-600">
                {{ $article->excerpt }}
            </p>
        @endif
    </a>
</article>
