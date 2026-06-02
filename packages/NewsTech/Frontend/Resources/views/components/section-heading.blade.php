@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="space-y-3">
    @if ($eyebrow)
        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-600">
            {{ $eyebrow }}
        </p>
    @endif

    <div class="space-y-2">
        <h2 class="text-2xl font-black tracking-tight text-stone-950 sm:text-3xl">
            {{ $title }}
        </h2>

        @if ($description)
            <p class="max-w-3xl text-sm leading-7 text-stone-600 sm:text-base">
                {{ $description }}
            </p>
        @endif
    </div>
</div>
