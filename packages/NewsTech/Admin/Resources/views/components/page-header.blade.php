@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between') }}>
    <div class="space-y-2">
        <h1 class="text-3xl font-black tracking-tight text-stone-950">{{ $title }}</h1>

        @if ($description)
            <p class="max-w-3xl text-sm leading-7 text-stone-600">{{ $description }}</p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex flex-wrap items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
