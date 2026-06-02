@props([
    'tone' => 'neutral',
])

@php
    $toneClasses = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-700',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
        'neutral' => 'border-stone-200 bg-stone-50 text-stone-700',
    ];
@endphp

<span
    {{ $attributes->class([
        'inline-flex rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]',
        $toneClasses[$tone] ?? $toneClasses['neutral'],
    ]) }}
>
    {{ $slot }}
</span>
