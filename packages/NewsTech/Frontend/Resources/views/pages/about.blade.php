<x-newstech-frontend::page-shell
    :seo="$seo"
    :eyebrow="$pageEyebrow"
    :title="$pageTitle"
    :lead="$pageLead"
>
    <div class="grid gap-6 lg:grid-cols-3">
        <x-newstech::panel class="space-y-3 border-stone-200 bg-white p-6 text-stone-700">
            <h2 class="text-xl font-bold tracking-tight text-stone-950">Editorial Focus</h2>
            <p class="text-sm leading-7 text-stone-600">
                NewsTech is structured for fast category-driven publishing, clear editorial ownership, and search-friendly public pages.
            </p>
        </x-newstech::panel>

        <x-newstech::panel class="space-y-3 border-stone-200 bg-white p-6 text-stone-700">
            <h2 class="text-xl font-bold tracking-tight text-stone-950">Platform Direction</h2>
            <p class="text-sm leading-7 text-stone-600">
                The public frontend is server-rendered and Blade-first, while the admin remains modular and ready for future modules like menus, pages, newsletters, and ads.
            </p>
        </x-newstech::panel>

        <x-newstech::panel class="space-y-3 border-stone-200 bg-white p-6 text-stone-700">
            <h2 class="text-xl font-bold tracking-tight text-stone-950">Publishing Principles</h2>
            <p class="text-sm leading-7 text-stone-600">
                SEO, performance, accessibility, and structured content relationships are built into the foundation before larger audience features are added.
            </p>
        </x-newstech::panel>
    </div>
</x-newstech-frontend::page-shell>
