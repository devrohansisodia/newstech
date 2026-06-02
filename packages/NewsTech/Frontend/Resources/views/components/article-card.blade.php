@props([
    'article',
    'tone' => 'default',
])

@php
    $articleUrl = route('newstech.articles.show', ['slug' => $article->slug]);
    $imageUrl = $article->featured_image_url;
    $imageAlt = $article->title ?: 'Published NewsTech article image';
@endphp

<article
    @class([
        'group overflow-hidden rounded-2xl border border-stone-200 transition hover:border-amber-300 hover:bg-amber-50/40',
        'bg-white shadow-sm shadow-stone-200/60' => $tone === 'default',
        'bg-gradient-to-br from-amber-100 via-white to-stone-50 shadow-lg shadow-stone-200/70' => $tone === 'hero',
    ])
>
    <a href="{{ $articleUrl }}" class="block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100">
        <div class="aspect-[16/10] overflow-hidden border-b border-stone-200">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $imageAlt }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                    loading="{{ $tone === 'hero' ? 'eager' : 'lazy' }}"
                    fetchpriority="{{ $tone === 'hero' ? 'high' : 'auto' }}"
                    decoding="async"
                >
            @else
                <div class="flex h-full w-full items-end bg-gradient-to-br from-stone-900 via-stone-800 to-amber-700 p-6 text-white">
                    <div class="space-y-2">
                        <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em]">NewsTech</span>
                        <p class="max-w-xs text-sm font-semibold leading-6 text-stone-100">Add a featured image to improve cards, social previews, and SEO coverage.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-4 p-5 sm:p-6">
            <x-newstech-frontend::article-meta :article="$article" :compact="$tone !== 'hero'" />

            <div class="space-y-3">
                <h3
                    @class([
                        'tracking-tight text-stone-950 transition group-hover:text-amber-700',
                        'text-3xl font-black sm:text-4xl' => $tone === 'hero',
                        'text-xl font-bold sm:text-2xl' => $tone !== 'hero',
                    ])
                >
                    {{ $article->title }}
                </h3>

                @if ($article->excerpt)
                    <p
                        @class([
                            'nt-line-clamp-4 text-stone-600',
                            'max-w-3xl text-base leading-8 sm:text-lg' => $tone === 'hero',
                            'text-sm leading-7 sm:text-base' => $tone !== 'hero',
                        ])
                    >
                        {{ $article->excerpt }}
                    </p>
                @endif
            </div>
        </div>
    </a>
</article>
