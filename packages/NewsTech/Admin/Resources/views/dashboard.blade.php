<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Dashboard'"
    meta-description="Admin dashboard."
>
    @php
        $resolvedTopViewedArticles = $topViewedArticles ?? collect();
    @endphp

    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-white p-8 text-stone-700 shadow-stone-200/60">
            <h2 class="text-3xl font-black tracking-tight text-stone-950">Dashboard</h2>
        </x-newstech::panel>

        {!! newstech_view_render_event('admin.dashboard.cards.before') !!}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-newstech-admin::stat-card
                eyebrow="Navigation"
                title="Visible Menu Groups"
                :value="count($adminMenu)"
                description="Top-level sections available in admin."
            />

            <x-newstech-admin::stat-card
                eyebrow="Access"
                title="ACL Roots"
                :value="count($adminAcl)"
                description="Permission groups configured for admin access."
            />

            <x-newstech-admin::stat-card
                eyebrow="Identity"
                title="Signed-In Admin"
                :value="$currentAdminUser?->name ?? 'Unknown'"
                :description="$currentAdminUser?->email ?? 'No active admin session.'"
            />

            <x-newstech-admin::stat-card
                eyebrow="Audience"
                title="Top Article Views"
                :value="$resolvedTopViewedArticles->first()?->view_count ?? 0"
                description="Highest public article view count."
            />
        </div>
        {!! newstech_view_render_event('admin.dashboard.cards.after') !!}

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(20rem,0.9fr)]">
            <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-6 text-stone-700 shadow-stone-200/60">
                <div>
                    <h3 class="text-xl font-semibold text-stone-950">Menu overview</h3>
                </div>

                <ul class="grid gap-3 md:grid-cols-2">
                    @foreach ($adminMenu as $item)
                        <li class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-base font-semibold text-stone-950">{{ $item['name'] }}</span>
                                <span class="text-xs uppercase tracking-[0.25em] text-stone-400">{{ count($item['children']) }} items</span>
                            </div>
                            <p class="mt-2 text-sm leading-6 text-stone-500">{{ $item['key'] }}</p>
                        </li>
                    @endforeach
                </ul>
            </x-newstech::panel>

            <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-6 text-stone-700 shadow-stone-200/60">
                <div>
                    <h3 class="text-xl font-semibold text-stone-950">Top viewed published articles</h3>
                </div>

                @if ($resolvedTopViewedArticles->isEmpty())
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4 text-sm leading-7 text-stone-500">
                        Top viewed article data will appear here once published stories receive public visits.
                    </div>
                @else
                    <ul class="space-y-2 text-sm leading-7 text-stone-700">
                        @foreach ($resolvedTopViewedArticles as $article)
                            <li class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-stone-950">{{ $article->title }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.25em] text-stone-400">
                                            {{ $article->category?->name ?? 'Unassigned Category' }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold text-amber-700">
                                        {{ number_format($article->view_count) }} views
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-newstech::panel>
        </div>
    </div>
</x-newstech-admin::layouts.app>
