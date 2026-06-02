@props([
    'title' => config('newstech.admin.label'),
    'metaDescription' => 'NewsTech administrative foundation shell.',
    'showNavigation' => true,
])

@php
    $editorImageModalConfig = [
        'modalTitle' => 'Insert Content Image',
        'uploadEndpoint' => route('admin.newstech.media.store'),
        'csrfToken' => csrf_token(),
        'mediaItems' => app(\NewsTech\Media\Repositories\MediaRepository::class)
            ->orderedQuery()
            ->where('mime_type', 'like', 'image/%')
            ->limit(18)
            ->get()
            ->map(fn ($media): array => [
                'id' => $media->getKey(),
                'path' => $media->path,
                'url' => $media->resolvedUrl(),
                'filename' => $media->filename,
                'original_name' => $media->original_name ?: $media->filename,
                'mime_type' => $media->mime_type,
                'extension' => $media->extension,
                'size' => $media->size,
                'alt_text' => $media->alt_text,
                'caption' => $media->caption,
                'created_at' => $media->created_at?->toIso8601String(),
                'created_at_label' => $media->created_at?->format('M d, Y H:i'),
                'is_image' => $media->isImage(),
                'update_url' => route('admin.newstech.media.update', $media),
            ])
            ->values()
            ->all(),
    ];
@endphp

<x-newstech::layouts.app
    :title="$title"
    :meta-description="$metaDescription"
    :vite-entries="[
        'packages/NewsTech/Admin/Resources/assets/css/app.css',
        'packages/NewsTech/Admin/Resources/assets/js/app.js',
    ]"
    vite-build-directory="build-admin"
    vite-hot-file="admin.hot"
    body-class="bg-stone-100 text-stone-900"
>
    <x-slot:head>
        {!! newstech_view_render_event('admin.layout.head.after') !!}
    </x-slot>

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.14),transparent_22%),linear-gradient(180deg,#fafaf9_0%,#f5f5f4_100%)]">
        <div @class([
            'min-h-screen w-full',
            'grid lg:grid-cols-[19rem_minmax(0,1fr)]' => $showNavigation,
            'flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8' => ! $showNavigation,
        ])>
            @if ($showNavigation)
                {!! newstech_view_render_event('admin.layout.sidebar.before') !!}
                <x-newstech-admin::sidebar />
                {!! newstech_view_render_event('admin.layout.sidebar.after') !!}
            @endif

            <div @class([
                'flex min-h-screen min-w-0 flex-col' => $showNavigation,
                'w-full max-w-xl' => ! $showNavigation,
            ])>
                @if ($showNavigation)
                    {!! newstech_view_render_event('admin.layout.topbar.before') !!}
                    <x-newstech-admin::topbar :title="$title" />
                    {!! newstech_view_render_event('admin.layout.topbar.after') !!}

                    <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                        {!! newstech_view_render_event('admin.layout.main.before') !!}
                        {{ $slot }}
                        {!! newstech_view_render_event('admin.layout.main.after') !!}
                    </main>
                @else
                    {{ $slot }}
                @endif
            </div>
        </div>

        <div
            data-editor-image-modal-root="true"
            data-vue-mount-status="pending"
        >
            <script type="application/json" data-editor-image-modal-config>
                {!! json_encode($editorImageModalConfig, JSON_THROW_ON_ERROR) !!}
            </script>
        </div>
    </div>
</x-newstech::layouts.app>
