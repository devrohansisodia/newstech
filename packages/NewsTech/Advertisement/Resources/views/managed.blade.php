@php
    $heading = $advertisement->title ?: $advertisement->name;
    $label = 'Advertisement';
@endphp

<aside
    aria-label="{{ $advertisement->name }}"
    data-managed-advertisement="{{ $advertisement->slot_key }}"
    class="w-full overflow-hidden rounded-2xl border border-stone-200 bg-white text-stone-700 shadow-sm shadow-stone-200/60"
>
    <div @class([
        'space-y-4',
        'p-4' => $compact,
        'p-5 sm:p-6' => ! $compact,
    ])>
        <div class="flex items-center justify-between gap-3">
            <p class="text-[11px] font-black uppercase tracking-[0.35em] text-amber-600">{{ $label }}</p>
            <span class="rounded-full border border-stone-200 bg-stone-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-500">
                {{ $slot['label'] ?? $advertisement->slot_key }}
            </span>
        </div>

        @if ($advertisement->type === \NewsTech\Advertisement\Models\Advertisement::TYPE_HTML)
            <div class="nt-prose max-w-none text-sm text-stone-700">
                {!! $advertisement->html_content !!}
            </div>
        @else
            @php
                $linkAttributes = [
                    'class' => 'group block overflow-hidden rounded-[1.5rem]',
                ];

                if ($advertisement->open_in_new_tab) {
                    $linkAttributes['target'] = '_blank';
                }

                if ($advertisement->relAttributes() !== '') {
                    $linkAttributes['rel'] = $advertisement->relAttributes();
                }
            @endphp

            @if ($clickUrl)
                <a href="{{ $clickUrl }}" @foreach($linkAttributes as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
                    @if ($resolvedImageUrl)
                        <img
                            src="{{ $resolvedImageUrl }}"
                            alt="{{ $heading }}"
                            class="w-full rounded-[1.5rem] border border-stone-200 object-cover transition group-hover:scale-[1.01]"
                        >
                    @endif
                </a>
            @elseif ($resolvedImageUrl)
                <img
                    src="{{ $resolvedImageUrl }}"
                    alt="{{ $heading }}"
                    class="w-full rounded-[1.5rem] border border-stone-200 object-cover"
                >
            @endif

            <div class="space-y-2">
                <h2 class="text-lg font-black tracking-tight text-stone-950">{{ $heading }}</h2>

                @if ($advertisement->target_url)
                    <p class="text-sm leading-7 text-stone-600">
                        Sponsored destination:
                        <span class="font-semibold text-stone-950">{{ $advertisement->target_url }}</span>
                    </p>
                @endif
            </div>
        @endif
    </div>
</aside>
