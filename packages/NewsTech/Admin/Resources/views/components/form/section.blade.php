@props([
    'title',
    'description' => null,
])

<x-newstech::panel {{ $attributes->class('space-y-6 border-stone-200 bg-stone-50/90 p-6 text-stone-700 shadow-stone-200/70') }}>
    <div class="space-y-2">
        <h3 class="text-xl font-semibold tracking-tight text-stone-950">{{ $title }}</h3>

        @if ($description)
            <p class="max-w-3xl text-sm leading-7 text-stone-500">{{ $description }}</p>
        @endif
    </div>

    <div class="grid gap-5">
        {{ $slot }}
    </div>
</x-newstech::panel>
