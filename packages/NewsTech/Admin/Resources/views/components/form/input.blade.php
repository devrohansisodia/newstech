@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
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
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $resolvedValue }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        {{ $attributes->class([
            'w-full rounded-xl border bg-white px-4 py-3 text-sm text-stone-700 placeholder:text-stone-400 focus:outline-none',
            'border-rose-300 focus:border-rose-500' => $fieldError,
            'border-stone-200 focus:border-amber-400' => ! $fieldError,
        ]) }}
    />
</x-newstech-admin::form.field>
