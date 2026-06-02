<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-10">
        {!! newstech_view_render_event('frontend.listing.top', ['type' => 'category', 'category' => $category]) !!}
        {!! newstech_view_render_event('frontend.category.show.top', ['category' => $category]) !!}
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>
            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">{{ $category->name }}</span>
        </nav>

        <section class="space-y-6">
            <x-newstech-frontend::section-heading
                eyebrow="Category"
                :title="$category->name"
                :description="$category->description ?: 'Published coverage collected from this NewsTech category.'"
            />

            <div class="flex flex-wrap items-center gap-3 text-sm text-stone-500">
                <span class="rounded-full border border-stone-200 bg-white px-4 py-2">{{ $articles->total() }} published articles</span>

                @if ($category->parent)
                    <span class="rounded-full border border-stone-200 bg-white px-4 py-2">Parent: {{ $category->parent->name }}</span>
                @endif
            </div>
        </section>

        @if ($articles->isEmpty())
            <x-newstech::panel class="border-stone-200 bg-white p-6 text-sm leading-7 text-stone-600">
                No published articles are currently available in this category.
            </x-newstech::panel>
        @else
            <x-newstech-frontend::article-grid :articles="$articles" />

            <div>
                {{ $articles->links() }}
            </div>
        @endif
        {!! newstech_view_render_event('frontend.category.show.bottom', ['category' => $category]) !!}
        {!! newstech_view_render_event('frontend.listing.bottom', ['type' => 'category', 'category' => $category]) !!}
    </div>
</x-newstech-frontend::layouts.app>
