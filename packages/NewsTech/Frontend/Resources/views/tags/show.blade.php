<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-10">
        {!! newstech_view_render_event('frontend.listing.top', ['type' => 'tag', 'tag' => $tag]) !!}
        {!! newstech_view_render_event('frontend.tag.show.top', ['tag' => $tag]) !!}
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>
            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">{{ $tag->name }}</span>
        </nav>

        <section class="space-y-6">
            <x-newstech-frontend::section-heading
                eyebrow="Topic Tag"
                :title="$tag->name"
                :description="$tag->description ?: 'Published stories grouped under this NewsTech topic tag.'"
            />

            <div class="flex flex-wrap items-center gap-3 text-sm text-stone-500">
                <span class="rounded-full border border-stone-200 bg-white px-4 py-2">{{ $articles->total() }} published articles</span>
            </div>
        </section>

        @if ($articles->isEmpty())
            <x-newstech::panel class="border-stone-200 bg-white p-6 text-sm leading-7 text-stone-600">
                No published articles are currently available for this topic tag.
            </x-newstech::panel>
        @else
            <x-newstech-frontend::article-grid :articles="$articles" />

            <div>
                {{ $articles->links() }}
            </div>
        @endif
        {!! newstech_view_render_event('frontend.tag.show.bottom', ['tag' => $tag]) !!}
        {!! newstech_view_render_event('frontend.listing.bottom', ['type' => 'tag', 'tag' => $tag]) !!}
    </div>
</x-newstech-frontend::layouts.app>
