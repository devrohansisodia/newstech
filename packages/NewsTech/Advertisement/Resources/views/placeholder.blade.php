@php
    $slotConfig = config('newstech-advertisement.slots.'.$key);
    $placeholdersEnabled = (bool) config('newstech-advertisement.placeholders_enabled', false);
    $slotEnabled = is_array($slotConfig) && (bool) ($slotConfig['enabled'] ?? false);
    $attributes = $attributes ?? new \Illuminate\View\ComponentAttributeBag;
@endphp

@if ($placeholdersEnabled && $slotEnabled && is_array($slotConfig))
    <aside
        aria-label="{{ $slotConfig['label'] }}"
        data-ad-slot="{{ $key }}"
        {{ $attributes->class([
            'w-full overflow-hidden rounded-2xl border border-dashed border-amber-200 bg-amber-50/70 text-stone-700',
            'p-4' => $compact ?? false,
            'p-5 sm:p-6' => ! ($compact ?? false),
            $slotConfig['min_height'] ?? 'min-h-[6rem]',
        ]) }}
    >
        <div class="flex h-full flex-col justify-between gap-4">
            <div class="space-y-2">
                <p class="text-[11px] font-black uppercase tracking-[0.35em] text-amber-600">Advertisement Placeholder</p>
                <h2 @class([
                    'font-black tracking-tight text-stone-950',
                    'text-lg' => $compact ?? false,
                    'text-xl sm:text-2xl' => ! ($compact ?? false),
                ])>{{ $slotConfig['label'] }}</h2>
                <p class="max-w-3xl text-sm leading-7 text-stone-600">{{ $slotConfig['description'] }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-stone-500 sm:gap-3 sm:tracking-[0.28em]">
                <span class="rounded-xl border border-stone-200 bg-white px-3 py-2">Slot Key: {{ $key }}</span>
                <span class="rounded-xl border border-stone-200 bg-white px-3 py-2">Event Driven</span>
                <span class="rounded-xl border border-stone-200 bg-white px-3 py-2">Placeholder</span>
            </div>
        </div>
    </aside>
@endif
