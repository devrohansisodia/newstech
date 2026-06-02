<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-10">
        {!! newstech_view_render_event('frontend.listing.top', ['type' => 'search', 'filters' => $filters]) !!}
        {!! newstech_view_render_event('frontend.search.show.top', ['filters' => $filters]) !!}
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>
            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">Search</span>
        </nav>

        <section class="space-y-6">
            <x-newstech-frontend::section-heading
                eyebrow="Search"
                title="Search published articles"
                description="Find coverage by keyword and optionally narrow results by category, author, or tag."
            />

            <form method="GET" action="{{ route('newstech.search') }}" class="grid gap-4 rounded-[2rem] border border-stone-200 bg-white p-5 lg:grid-cols-[minmax(0,1.4fr)_repeat(3,minmax(0,0.8fr))_auto]">
                <div class="space-y-2">
                    <label for="search-query" class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Keyword</label>
                    <input
                        id="search-query"
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Search title, excerpt, or content"
                        class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-300 focus:outline-none"
                    >
                </div>

                <div class="space-y-2">
                    <label for="search-category" class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Category</label>
                    <select
                        id="search-category"
                        name="category"
                        class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none"
                    >
                        <option value="">All categories</option>
                        @foreach ($categoryOptions as $slug => $name)
                            <option value="{{ $slug }}" @selected($filters['category'] === $slug)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="search-author" class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Author</label>
                    <select
                        id="search-author"
                        name="author"
                        class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none"
                    >
                        <option value="">All authors</option>
                        @foreach ($authorOptions as $slug => $name)
                            <option value="{{ $slug }}" @selected($filters['author'] === $slug)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="search-tag" class="text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">Tag</label>
                    <select
                        id="search-tag"
                        name="tag"
                        class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none"
                    >
                        <option value="">All tags</option>
                        @foreach ($tagOptions as $slug => $name)
                            <option value="{{ $slug }}" @selected($filters['tag'] === $slug)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-2xl border border-stone-200 bg-stone-50 px-5 py-3 text-sm font-semibold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50"
                    >
                        Search
                    </button>
                </div>
            </form>
        </section>

        <section class="space-y-6">
            <div class="flex flex-wrap items-center gap-3 text-sm text-stone-500">
                @if ($filters['q'] !== '')
                    <span class="rounded-full border border-stone-200 bg-white px-4 py-2">Query: {{ $filters['q'] }}</span>
                @endif

                <span class="rounded-full border border-stone-200 bg-white px-4 py-2">{{ $results->total() }} results</span>
            </div>

            @if ($results->isEmpty())
                <x-newstech::panel class="space-y-3 border-stone-200 bg-white p-6 text-stone-600">
                    <h2 class="text-xl font-bold tracking-tight text-stone-950">No published articles found</h2>
                    <p class="text-sm leading-7">
                        Try a different keyword or remove one of the optional filters to broaden the published result set.
                    </p>
                </x-newstech::panel>
            @else
                <x-newstech-frontend::article-grid :articles="$results" />

                <div>
                    {{ $results->links() }}
                </div>
            @endif
        </section>
        {!! newstech_view_render_event('frontend.search.show.bottom', ['filters' => $filters]) !!}
        {!! newstech_view_render_event('frontend.listing.bottom', ['type' => 'search', 'filters' => $filters]) !!}
    </div>
</x-newstech-frontend::layouts.app>
