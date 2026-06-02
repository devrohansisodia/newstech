@props([
    'eyebrow',
    'title',
    'value',
    'description',
])

<x-newstech::panel class="space-y-3 border-stone-200 bg-stone-50/90 p-5 text-stone-700 shadow-stone-200/70">
    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">{{ $eyebrow }}</p>
    <p class="text-3xl font-black tracking-tight text-stone-950">{{ $value }}</p>
    <div class="space-y-1">
        <h3 class="text-base font-semibold text-stone-950">{{ $title }}</h3>
        <p class="text-sm leading-6 text-stone-500">{{ $description }}</p>
    </div>
</x-newstech::panel>
