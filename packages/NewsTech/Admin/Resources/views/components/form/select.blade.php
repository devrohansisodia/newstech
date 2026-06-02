@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => 'Choose an option',
    'hint' => null,
    'required' => false,
    'multiple' => false,
    'errorName' => null,
])

@php
    $fieldId = str($name)->replace(['[', ']', '.'], '-');
    $resolvedErrorName = $errorName ?? str($name)->replace(['[]'], '')->toString();
    $fieldError = session('errors')?->first($resolvedErrorName);
    $resolvedValue = old($resolvedErrorName, $value);
    $resolvedValues = $multiple ? collect($resolvedValue ?? [])->map(fn ($selectedValue) => (string) $selectedValue)->all() : [];
@endphp

<x-newstech-admin::form.field
    :for="$fieldId"
    :label="$label"
    :hint="$hint"
    :error="$fieldError"
    :required="$required"
>
    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        @required($required)
        @if ($multiple) multiple @endif
        {{ $attributes->class([
            'w-full rounded-xl border bg-white px-4 py-3 text-sm text-stone-700 focus:outline-none',
            'min-h-48' => $multiple,
            'border-rose-300 focus:border-rose-500' => $fieldError,
            'border-stone-200 focus:border-amber-400' => ! $fieldError,
        ]) }}
    >
        @unless ($multiple)
            <option value="">{{ $placeholder }}</option>
        @endunless

        @foreach ($options as $optionValue => $optionLabel)
            <option
                value="{{ $optionValue }}"
                @selected($multiple
                    ? in_array((string) $optionValue, $resolvedValues, true)
                    : (string) $resolvedValue === (string) $optionValue)
            >
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
</x-newstech-admin::form.field>
