@props([
    'group',
])

<a
    href="{{ route('admin.newstech.settings.show', ['group' => $group['key']]) }}"
    class="block rounded-[1.75rem] transition hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
>
    <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-6 text-stone-700 shadow-stone-200/60">
        <div class="flex items-start gap-4">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-200 bg-amber-50 text-xs font-black tracking-[0.25em] text-amber-700">
                {{ $group['icon'] !== '' ? $group['icon'] : str($group['title'])->substr(0, 2)->upper() }}
            </span>

            <div class="min-w-0 flex-1 space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-lg font-semibold tracking-tight text-stone-950">{{ $group['title'] }}</p>
                    <span class="rounded-full border border-stone-200 bg-stone-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">
                        {{ count($group['sections']) }} {{ count($group['sections']) === 1 ? 'section' : 'sections' }}
                    </span>
                </div>

                <p class="text-sm leading-7 text-stone-600">{{ $group['description'] }}</p>

                @if ($group['summary_text'] ?? null)
                    <p class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                        {{ $group['summary_text'] }}
                    </p>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-stone-200 pt-4 text-sm font-semibold text-stone-700">
            <span>Open settings</span>
            <span aria-hidden="true">→</span>
        </div>
    </x-newstech::panel>
</a>
