<aside class="border-b border-stone-200 bg-stone-50/95 px-4 py-6 backdrop-blur sm:px-6 lg:border-r lg:border-b-0">
    <div class="space-y-8">
        <div class="flex items-center">
            <x-newstech::brand-mark logo-only />
        </div>

        {!! newstech_view_render_event('admin.sidebar.navigation.before', ['adminMenu' => $adminMenu]) !!}
        <nav aria-label="Admin navigation" class="space-y-3">
            @foreach ($adminMenu as $item)
                {!! newstech_view_render_event('admin.sidebar.group.before', ['item' => $item]) !!}
                <section class="space-y-3 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm shadow-stone-200/50">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-xs font-black tracking-[0.25em] text-amber-700">
                            {{ $item['icon'] ?: str($item['name'])->substr(0, 2)->upper() }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <div>
                                @if ($item['url'] && $item['children'] === [])
                                    <a href="{{ $item['url'] }}" class="text-sm font-semibold tracking-tight text-stone-950 hover:text-amber-700">
                                        {{ $item['name'] }}
                                    </a>
                                @else
                                    <p class="text-sm font-semibold tracking-tight text-stone-950">{{ $item['name'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($item['children'] !== [])
                        <ul class="space-y-2 border-l border-stone-200 pl-4 text-sm text-stone-600">
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a
                                        href="{{ $child['url'] }}"
                                        @class([
                                            'flex items-center justify-between rounded-xl px-3 py-2 transition',
                                            'bg-amber-50 text-amber-700 ring-1 ring-amber-200' => $child['is_active'],
                                            'hover:bg-stone-100' => ! $child['is_active'],
                                        ])
                                    >
                                        <span>{{ $child['name'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
                {!! newstech_view_render_event('admin.sidebar.group.after', ['item' => $item]) !!}
            @endforeach
        </nav>
        {!! newstech_view_render_event('admin.sidebar.navigation.after', ['adminMenu' => $adminMenu]) !!}
    </div>
</aside>
