@props([
    'name',
    'label',
    'checked' => false,
    'hint' => null,
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
    $fieldError = session('errors')?->first($name);
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="space-y-3">
    <input type="hidden" name="{{ $name }}" value="0">

    <label
        for="{{ $fieldId }}"
        @class([
            'flex items-center justify-between gap-4 rounded-xl border px-4 py-4 transition',
            'border-rose-300 bg-rose-50' => $fieldError,
            'border-stone-200 bg-white' => ! $fieldError,
        ])
    >
        <div class="space-y-1">
            <p class="text-sm font-semibold text-stone-900">{{ $label }}</p>
            <p class="text-sm leading-6 text-stone-500">{{ $hint }}</p>
        </div>

        <div class="relative inline-flex items-center">
            <input
                id="{{ $fieldId }}"
                name="{{ $name }}"
                type="checkbox"
                value="1"
                class="peer sr-only"
                @checked($isChecked)
                {{ $attributes }}
            />

            <span class="h-7 w-12 rounded-full bg-stone-200 transition peer-checked:bg-amber-300"></span>
            <span class="absolute left-1 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
        </div>
    </label>

    @if ($fieldError)
        <x-newstech-admin::form.error :message="$fieldError" />
    @endif
</div>
