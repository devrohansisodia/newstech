@props([
    'name',
    'label',
    'hint' => null,
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
@endphp

<x-newstech-admin::form.field
    :for="$fieldId"
    :label="$label"
    :hint="$hint"
>
    <div class="rounded-xl border border-dashed border-stone-300 bg-stone-50 p-5">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-stone-900">File input placeholder</p>
                <p class="mt-1 text-sm leading-6 text-stone-500">
                    Real uploads and media handling will plug into this foundation in a later module.
                </p>
            </div>

            <input
                id="{{ $fieldId }}"
                name="{{ $name }}"
                type="file"
                disabled
                class="block w-full cursor-not-allowed rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-400 md:max-w-xs"
            />
        </div>
    </div>
</x-newstech-admin::form.field>
