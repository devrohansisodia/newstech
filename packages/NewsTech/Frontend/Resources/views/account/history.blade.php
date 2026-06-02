@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Reading History',
        'Review the recent articles viewed by your NewsTech reader account.',
        route('newstech.account.history')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Account"
    title="Reading history"
    lead="Your recent article views are stored on your reader account so you can return to stories you opened earlier."
>
    <div class="space-y-6">
        @include('newstech-frontend::account.partials.nav')

        @if ($history && $history->count() > 0)
            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($history as $entry)
                    <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-6">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
                                <span>{{ $entry->article?->category?->name ?? 'General' }}</span>
                                <span class="h-1 w-1 rounded-full bg-stone-300"></span>
                                <span>{{ $entry->last_viewed_at?->format('M d, Y · H:i') ?? 'Recently viewed' }}</span>
                            </div>
                            <h2 class="text-2xl font-black tracking-tight text-stone-950">
                                <a href="{{ route('newstech.articles.show', ['slug' => $entry->article->slug]) }}" class="transition hover:text-amber-700">
                                    {{ $entry->article->title }}
                                </a>
                            </h2>
                            <p class="text-sm leading-7 text-stone-600">
                                Viewed {{ $entry->view_count }} {{ \Illuminate\Support\Str::plural('time', $entry->view_count) }}.
                            </p>
                        </div>
                    </x-newstech::panel>
                @endforeach
            </div>

            <div>
                {{ $history->links() }}
            </div>
        @else
            <x-newstech::panel class="border-dashed border-stone-300 bg-stone-50 p-6 text-sm leading-7 text-stone-500">
                No reading history yet. Open published articles while signed in to build your recent-reading list.
            </x-newstech::panel>
        @endif
    </div>
</x-newstech-frontend::page-shell>
