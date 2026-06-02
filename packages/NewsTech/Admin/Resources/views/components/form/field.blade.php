@props([
    'label',
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
])

<div {{ $attributes->class('space-y-3') }}>
    <div class="space-y-2">
        <label
            @if ($for) for="{{ $for }}" @endif
            class="block text-sm font-semibold tracking-tight text-stone-900"
        >
            {{ $label }}

            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>

        @if ($hint)
            <p class="text-sm leading-6 text-stone-500">{{ $hint }}</p>
        @endif
    </div>

    {{ $slot }}

    @if ($error)
        <x-newstech-admin::form.error :message="$error" />
    @endif
</div>
