@props([
    'title' => config('newstech.meta.default_title'),
    'metaDescription' => config('newstech.meta.default_description'),
    'seo' => null,
])

<x-newstech::layouts.app
    :title="$title"
    :meta-description="$metaDescription"
    :seo="$seo"
    :vite-entries="[
        'packages/NewsTech/Frontend/Resources/assets/css/app.css',
        'packages/NewsTech/Frontend/Resources/assets/js/app.js',
    ]"
    vite-build-directory="build-frontend"
    vite-hot-file="frontend.hot"
    body-class="bg-stone-100 text-stone-900"
>
    <x-slot:head>
        {!! newstech_view_render_event('frontend.layout.head.after') !!}
    </x-slot>

    @php
        $staticPages = $frontendStaticPages ?? [];
        $navigationCategories = $frontendNavigationCategories ?? collect();
        $headerMenuItems = ($frontendHeaderMenuItems ?? collect())->isNotEmpty()
            ? $frontendHeaderMenuItems
            : ($frontendFallbackHeaderMenuItems ?? collect());
        $footerMenuItems = ($frontendFooterMenuItems ?? collect())->isNotEmpty()
            ? $frontendFooterMenuItems
            : ($frontendFallbackFooterMenuItems ?? collect());
        $readerGuard = config('newstech-reader.auth.guard');
        $authenticatedReader = auth($readerGuard)->user();
    @endphp

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(245,158,11,0.10),transparent_28%),linear-gradient(180deg,#fafaf9_0%,#f5f5f4_100%)]">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-amber-300 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-stone-950">
            Skip to content
        </a>

        {!! newstech_view_render_event('frontend.layout.header.before') !!}
        <header class="border-b border-stone-200 bg-stone-50/90 backdrop-blur">
            <div class="mx-auto flex max-w-[92rem] flex-col gap-5 px-4 py-6 sm:px-6 xl:px-8">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 space-y-4">
                        <div class="flex items-start gap-4 sm:items-center sm:gap-5">
                            <x-newstech::brand-mark size="prominent" />
                        </div>

                        <nav aria-label="Primary navigation" class="flex flex-wrap items-center gap-2 text-sm font-semibold text-stone-700">
                            @foreach ($headerMenuItems as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noreferrer" @endif
                                    class="rounded-xl border border-stone-200 bg-white px-4 py-2 transition hover:border-amber-300 hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/50 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
                                >
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>

                        {!! newstech_view_render_event('frontend.layout.navigation.after', ['items' => $headerMenuItems]) !!}
                    </div>

                    <div class="flex items-center gap-3 xl:w-auto xl:justify-end">
                        <form method="GET" action="{{ route('newstech.search') }}" class="flex min-w-0 flex-1 items-center gap-2 sm:min-w-[20rem] xl:w-[24rem]" aria-label="Site search form">
                            <label for="header-search" class="sr-only">Search published articles</label>
                            <input
                                id="header-search"
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Search published articles"
                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:border-amber-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/50 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
                            >
                            <button
                                type="submit"
                                class="rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-semibold text-stone-700 transition hover:border-amber-300 hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/50 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
                            >
                                Search
                            </button>
                        </form>

                        <x-newstech-frontend::header-auth-menu />
                    </div>
                </div>

                @if ($navigationCategories->isNotEmpty())
                    <div class="flex flex-wrap gap-2 border-t border-stone-200 pt-4">
                        @foreach ($navigationCategories as $category)
                            <a href="{{ route('newstech.categories.show', ['slug' => $category->slug]) }}" class="rounded-xl border border-stone-200 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-stone-600 transition hover:border-amber-300 hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/50 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {!! newstech_view_render_event('frontend.layout.header.after') !!}
            </div>
        </header>

        {!! newstech_view_render_event('frontend.layout.main.before') !!}
        <main id="main-content" class="mx-auto flex w-full max-w-[92rem] flex-1 flex-col gap-10 px-4 py-8 sm:px-6 xl:px-8">
            {{ $slot }}
        </main>
        {!! newstech_view_render_event('frontend.layout.main.after') !!}

        {!! newstech_view_render_event('frontend.layout.footer.before') !!}
        <footer class="border-t border-stone-200 bg-stone-50/85">
                <div class="mx-auto grid max-w-[92rem] gap-8 px-4 py-8 text-sm text-stone-500 sm:px-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)_minmax(0,0.9fr)_minmax(0,1fr)] xl:px-8">
                <div class="space-y-3">
                    <x-newstech::brand-mark use-footer-logo />
                    <p>Published news, editorial highlights, and category blocks rendered from modular NewsTech packages.</p>
                    <p>Server-rendered, modular, and ready for later newsletter campaigns, ads, and deeper reader growth workflows.</p>
                </div>

                <div class="space-y-3">
                    <p class="font-semibold text-stone-950">Site Links</p>
                    <nav class="flex flex-col gap-2">
                        @foreach ($footerMenuItems as $item)
                            <a
                                href="{{ $item['url'] }}"
                                @if (($item['target'] ?? '_self') === '_blank') target="_blank" rel="noreferrer" @endif
                                class="transition hover:text-stone-950"
                            >
                                {{ $item['label'] }}
                            </a>

                            @foreach ($item['children'] ?? [] as $child)
                                <a
                                    href="{{ $child['url'] }}"
                                    @if (($child['target'] ?? '_self') === '_blank') target="_blank" rel="noreferrer" @endif
                                    class="pl-4 text-stone-400 transition hover:text-stone-950"
                                >
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        @endforeach
                    </nav>
                </div>

                <div class="space-y-3">
                    <p class="font-semibold text-stone-950">Newsletter</p>
                    <x-newstech-frontend::newsletter-form
                        source="footer"
                        title="Subscribe for updates"
                        description="Save your email now. Delivery workflows will be added in a later phase."
                        compact
                    />
                </div>

                <div class="space-y-3">
                    <p class="font-semibold text-stone-950">Browse Categories</p>
                    <nav class="flex flex-col gap-2">
                        @foreach ($navigationCategories as $category)
                            <a href="{{ route('newstech.categories.show', ['slug' => $category->slug]) }}" class="transition hover:text-stone-950">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>
        </footer>
        {!! newstech_view_render_event('frontend.layout.footer.after') !!}
    </div>
</x-newstech::layouts.app>
