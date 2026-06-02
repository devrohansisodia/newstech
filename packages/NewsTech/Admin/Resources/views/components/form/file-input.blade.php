@props([
    'name',
    'label',
    'hint' => null,
    'required' => false,
    'accept' => null,
    'previewUrl' => null,
    'previewAlt' => null,
    'emptyState' => null,
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
    $fieldError = session('errors')?->first($name);
    $previewContainerId = $fieldId.'-preview';
    $previewImageId = $fieldId.'-preview-image';
    $emptyStateId = $fieldId.'-empty-state';
@endphp

<x-newstech-admin::form.field
    :for="$fieldId"
    :label="$label"
    :hint="$hint"
    :error="$fieldError"
    :required="$required"
>
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="file"
        data-file-preview-input
        data-preview-target="{{ $previewContainerId }}"
        data-preview-image="{{ $previewImageId }}"
        data-empty-state="{{ $emptyStateId }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @required($required)
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-4 py-3 text-sm text-stone-700 file:mr-4 file:rounded-md file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-100 focus:outline-none',
            'border-rose-300 focus:border-rose-500' => $fieldError,
            'border-stone-200 focus:border-amber-400' => ! $fieldError,
        ]) }}
    />

    @if ($previewUrl || $emptyState)
        <div
            id="{{ $previewContainerId }}"
            @class([
                'mt-4 rounded-lg border p-4',
                'border-stone-200 bg-white' => $previewUrl,
                'border-dashed border-stone-200 bg-stone-50' => ! $previewUrl,
            ])
        >
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
                {{ $previewUrl ? 'Current asset' : 'Preview' }}
            </p>

            @if ($previewUrl)
                <img
                    id="{{ $previewImageId }}"
                    src="{{ $previewUrl }}"
                    alt="{{ $previewAlt ?: $label }}"
                    class="max-h-20 w-auto object-contain"
                >
            @else
                <img
                    id="{{ $previewImageId }}"
                    src=""
                    alt="{{ $previewAlt ?: $label }}"
                    class="hidden max-h-20 w-auto object-contain"
                >
            @endif

            <p
                id="{{ $emptyStateId }}"
                @class([
                    'text-sm text-stone-500',
                    'hidden' => $previewUrl,
                ])
            >
                {{ $emptyState }}
            </p>
        </div>
    @endif
</x-newstech-admin::form.field>
