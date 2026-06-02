@props([
    'type' => 'button',
    'tone' => 'neutral',
    'href' => null,
])

@php
    $toneClasses = [
        'primary' => 'border-amber-300 bg-amber-100 text-amber-800 hover:bg-amber-200',
        'neutral' => 'border-stone-200 bg-white text-stone-700 hover:bg-stone-100',
        'ghost' => 'border-transparent bg-transparent text-stone-600 hover:bg-stone-100',
    ];
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->class([
            'inline-flex cursor-pointer items-center justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100 disabled:cursor-not-allowed disabled:border-stone-200 disabled:bg-stone-100 disabled:text-stone-400',
            $toneClasses[$tone] ?? $toneClasses['neutral'],
        ]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->class([
            'inline-flex cursor-pointer items-center justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100 disabled:cursor-not-allowed disabled:border-stone-200 disabled:bg-stone-100 disabled:text-stone-400',
            $toneClasses[$tone] ?? $toneClasses['neutral'],
        ]) }}
    >
        {{ $slot }}
    </button>
@endif
