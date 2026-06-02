@props([
    'articles',
])

<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" aria-label="Article listing">
    @foreach ($articles as $article)
        <x-newstech-frontend::article-card :article="$article" />
    @endforeach
</div>
