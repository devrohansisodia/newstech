@props([
    'article',
    'showAuthor' => true,
    'showCategory' => true,
    'showDate' => true,
    'compact' => false,
])

@php
    $categoryName = $article->category?->name ?? 'General';
    $authorName = $article->author?->name ?? 'News Desk';
    $publishedAt = $article->published_at;
@endphp

<div @class([
    'flex flex-wrap items-center gap-x-3 gap-y-2 text-stone-500',
    'text-[11px] font-semibold uppercase tracking-[0.22em]' => $compact,
    'text-sm' => ! $compact,
])>
    @if ($showCategory)
        <span class="rounded-xl border border-stone-200 bg-stone-50 px-3 py-1.5 text-stone-700">
            {{ $categoryName }}
        </span>
    @endif

    @if ($article->is_breaking)
        <span class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-rose-700">Breaking</span>
    @endif

    @if ($article->is_featured)
        <span class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-amber-700">Featured</span>
    @endif

    @if ($showAuthor)
        <span>{{ $authorName }}</span>
    @endif

    @if ($showAuthor && $showDate)
        <span class="h-1 w-1 rounded-full bg-stone-300"></span>
    @endif

    @if ($showDate)
        <time datetime="{{ optional($publishedAt)->toIso8601String() }}">
            {{ $publishedAt?->format('M d, Y · H:i') ?? 'Published' }}
        </time>
    @endif
</div>
