@props([
    'name',
    'label',
    'value' => null,
    'prefix' => '/news/',
    'hint' => null,
    'required' => false,
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
    $fieldError = session('errors')?->first($name);
    $resolvedValue = old($name, $value);
@endphp

<x-newstech-admin::form.field
    :for="$fieldId"
    :label="$label"
    :hint="$hint"
    :error="$fieldError"
    :required="$required"
>
    <div
        @class([
            'flex flex-col overflow-hidden rounded-xl border bg-white md:flex-row md:items-center',
            'border-rose-300' => $fieldError,
            'border-stone-200' => ! $fieldError,
        ])
    >
        <span class="border-b border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500 md:border-r md:border-b-0">
            {{ $prefix }}
        </span>

        <input
            id="{{ $fieldId }}"
            name="{{ $name }}"
            type="text"
            value="{{ $resolvedValue }}"
            placeholder="headline-slug"
            @required($required)
            {{ $attributes->class('w-full bg-transparent px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:outline-none') }}
        />
    </div>
</x-newstech-admin::form.field>
