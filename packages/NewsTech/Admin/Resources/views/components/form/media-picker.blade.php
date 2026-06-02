@props([
    'name',
    'label',
    'value' => null,
    'hint' => null,
    'required' => false,
    'emptyState' => 'No image selected yet.',
    'previewLabel' => 'Selected image',
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
    $fieldError = session('errors')?->first($name);
    $resolvedValue = old($name, $value);
    $resolvedPath = is_string($resolvedValue) ? trim($resolvedValue) : '';
    $resolvedUrl = $resolvedPath !== ''
        ? app(\NewsTech\Core\Support\MediaManager::class)->url($resolvedPath)
        : null;

    $selectedMedia = $resolvedPath !== ''
        ? app(\NewsTech\Media\Repositories\MediaRepository::class)
            ->query()
            ->where('path', $resolvedPath)
            ->first()
        : null;

    $mediaItems = app(\NewsTech\Media\Repositories\MediaRepository::class)
        ->orderedQuery()
        ->where('mime_type', 'like', 'image/%')
        ->limit(18)
        ->get();

    if ($selectedMedia && ! $mediaItems->contains(fn ($media): bool => $media->is($selectedMedia))) {
        $mediaItems->prepend($selectedMedia);
    }

    $mediaPickerConfig = [
        'fieldName' => $name,
        'fieldId' => $fieldId,
        'label' => $label,
        'emptyState' => $emptyState,
        'previewLabel' => $previewLabel,
        'currentPath' => $resolvedPath,
        'currentUrl' => $resolvedUrl ?? '',
        'uploadEndpoint' => route('admin.newstech.media.store'),
        'csrfToken' => csrf_token(),
        'mediaItems' => $mediaItems
            ->values()
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
            ->all(),
    ];
@endphp

<x-newstech-admin::form.field
    :label="$label"
    :hint="$hint"
    :error="$fieldError"
    :required="$required"
>
    <div
        data-media-picker
        data-media-picker-root="true"
        data-vue-mount-status="pending"
    >
        <input
            id="{{ $fieldId }}-media-path"
            name="{{ $name }}"
            type="hidden"
            value="{{ $resolvedPath }}"
            data-media-picker-hidden-input
            {{ $attributes }}
        >

        <div
            class="rounded-xl border p-4 {{ $resolvedUrl ? 'border-stone-200 bg-white' : 'border-dashed border-stone-200 bg-stone-50' }}"
            data-media-picker-preview
        >
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">{{ $previewLabel }}</p>

            <img
                src="{{ $resolvedUrl ?? '' }}"
                alt="{{ $label }}"
                @class([
                    'max-h-28 w-auto rounded-lg object-contain',
                    'hidden' => ! $resolvedUrl,
                ])
                data-media-picker-preview-image
            >

            <p
                @class([
                    'mt-3 text-sm leading-6 text-stone-500',
                    'hidden' => $resolvedUrl,
                ])
                data-media-picker-preview-text
            >
                {{ $emptyState }}
            </p>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <x-newstech-admin::form.button type="button" tone="primary" data-media-picker-open>
                Select Image
            </x-newstech-admin::form.button>

            <x-newstech-admin::form.button type="button" tone="ghost" data-media-picker-clear>
                Clear
            </x-newstech-admin::form.button>
        </div>

        <script type="application/json" data-media-picker-config>
            {!! json_encode($mediaPickerConfig, JSON_THROW_ON_ERROR) !!}
        </script>
    </div>
</x-newstech-admin::form.field>
