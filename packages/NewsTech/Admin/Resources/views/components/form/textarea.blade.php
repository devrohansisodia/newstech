@props([
    'name',
    'label',
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'rows' => 5,
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
    <textarea
        id="{{ $fieldId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        {{ $attributes->class([
            'w-full rounded-xl border bg-white px-4 py-3 text-sm leading-7 text-stone-700 placeholder:text-stone-400 focus:outline-none',
            'border-rose-300 focus:border-rose-500' => $fieldError,
            'border-stone-200 focus:border-amber-400' => ! $fieldError,
        ]) }}
    >{{ $resolvedValue }}</textarea>
</x-newstech-admin::form.field>
