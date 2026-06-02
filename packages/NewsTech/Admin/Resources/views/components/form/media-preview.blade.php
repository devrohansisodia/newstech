@props([
    'path' => null,
    'label' => 'Current image',
])

@php
    $previewPath = is_string($path) ? trim($path) : '';
    $previewUrl = null;

    if ($previewPath !== '') {
        $previewUrl = filter_var($previewPath, FILTER_VALIDATE_URL)
            ? $previewPath
            : app(\NewsTech\Core\Support\MediaManager::class)->url($previewPath);
    }
@endphp

@if ($previewUrl)
    <div class="rounded-lg border border-stone-200 bg-white p-4">
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">{{ $label }}</p>
        <img src="{{ $previewUrl }}" alt="{{ $label }}" class="max-h-24 w-auto object-contain">
    </div>
@endif
