@php
    $mediaLibraryConfig = [
        'csrfToken' => csrf_token(),
        'uploadEndpoint' => route('admin.newstech.media.store'),
        'items' => $mediaLibraryItems,
        'paginationHtml' => $mediaPaginationHtml,
        'firstItem' => $mediaItems->firstItem() ?? 0,
        'lastItem' => $mediaItems->lastItem() ?? 0,
        'totalItems' => $mediaItems->total(),
    ];
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Media'"
    meta-description="NewsTech media library."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Media library</h2>
                </div>
            </div>

            @if (session('media_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('media_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-newstech-admin::stat-card eyebrow="Library" title="Total Media" :value="$mediaCount" description="All non-deleted media records currently available in the shared library." />
            <x-newstech-admin::stat-card eyebrow="Images" title="Image Assets" :value="$imageCount" description="Image files available for reuse in forms and settings." />
            <x-newstech-admin::stat-card eyebrow="Reuse" title="Picker Ready" value="Enabled" description="Articles, authors, and settings can reuse existing uploaded images." />
            <x-newstech-admin::stat-card eyebrow="Order" title="Sorting" value="Newest first" description="Recent uploads appear first in both the library and picker." />
        </div>

        <x-newstech::panel class="border-stone-200 bg-white p-6 shadow-stone-200/60">
            <div
                data-media-library-root="true"
                data-vue-mount-status="pending"
            >
                <script type="application/json" data-media-library-config>
                    {!! json_encode($mediaLibraryConfig, JSON_THROW_ON_ERROR) !!}
                </script>
            </div>
        </x-newstech::panel>
    </div>
</x-newstech-admin::layouts.app>
