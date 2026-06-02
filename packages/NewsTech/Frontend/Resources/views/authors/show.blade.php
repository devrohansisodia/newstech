@php
    $avatar = $author->avatar_url;
    $authorInitials = str($author->name)->explode(' ')->map(fn (string $part) => str($part)->substr(0, 1)->upper()->toString())->take(2)->implode('');
@endphp

<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-10">
        {!! newstech_view_render_event('frontend.listing.top', ['type' => 'author', 'author' => $author]) !!}
        {!! newstech_view_render_event('frontend.author.show.top', ['author' => $author]) !!}
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>
            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">{{ $author->name }}</span>
        </nav>

        <section class="grid gap-8 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <div class="overflow-hidden rounded-[2rem] border border-stone-200 bg-white">
                @if ($avatar)
                    <img
                        src="{{ $avatar }}"
                        alt="{{ $author->name }}"
                        class="h-full w-full object-cover"
                    >
                @else
                    <div class="flex aspect-square h-full w-full items-center justify-center bg-gradient-to-br from-stone-900 via-stone-800 to-amber-700 text-6xl font-black tracking-tight text-white">
                        {{ $authorInitials }}
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <x-newstech-frontend::section-heading
                    eyebrow="Author"
                    :title="$author->name"
                    :description="$author->bio ?: 'Published reporting and recent work from this NewsTech author.'"
                />

                <div class="flex flex-wrap items-center gap-3 text-sm text-stone-500">
                    @if ($author->designation)
                        <span class="rounded-full border border-stone-200 bg-white px-4 py-2">{{ $author->designation }}</span>
                    @endif

                    <span class="rounded-full border border-stone-200 bg-white px-4 py-2">{{ $articles->total() }} published articles</span>
                </div>

                <div class="flex flex-wrap gap-3 text-sm font-semibold text-stone-700">
                    @if ($author->website_url)
                        <a href="{{ $author->website_url }}" class="rounded-full border border-stone-200 bg-white px-4 py-2 transition hover:border-amber-300 hover:bg-amber-50">Website</a>
                    @endif
                    @if ($author->twitter_url)
                        <a href="{{ $author->twitter_url }}" class="rounded-full border border-stone-200 bg-white px-4 py-2 transition hover:border-amber-300 hover:bg-amber-50">X</a>
                    @endif
                    @if ($author->linkedin_url)
                        <a href="{{ $author->linkedin_url }}" class="rounded-full border border-stone-200 bg-white px-4 py-2 transition hover:border-amber-300 hover:bg-amber-50">LinkedIn</a>
                    @endif
                </div>
            </div>
        </section>

        @if ($articles->isEmpty())
            <x-newstech::panel class="border-stone-200 bg-white p-6 text-sm leading-7 text-stone-600">
                No published articles are currently available from this author.
            </x-newstech::panel>
        @else
            <section class="space-y-6">
                <x-newstech-frontend::section-heading
                    eyebrow="Published Work"
                    :title="'Latest articles by '.$author->name"
                    description="Published newsroom reporting currently attributed to this author."
                />

                <x-newstech-frontend::article-grid :articles="$articles" />

                <div>
                    {{ $articles->links() }}
                </div>
            </section>
        @endif
        {!! newstech_view_render_event('frontend.author.show.bottom', ['author' => $author]) !!}
        {!! newstech_view_render_event('frontend.listing.bottom', ['type' => 'author', 'author' => $author]) !!}
    </div>
</x-newstech-frontend::layouts.app>
