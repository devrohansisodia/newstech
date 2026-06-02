@props([
    'sidebar',
    'featuredArticles',
    'latestArticles',
])

@php
    $hasConfiguredSidebar = filled($sidebar['title'] ?? null)
        || filled($sidebar['content'] ?? null)
        || filled($sidebar['link_label'] ?? null);
@endphp

<div class="space-y-5">
    @if ($hasConfiguredSidebar)
        <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-6 text-stone-700 shadow-stone-200/60">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Homepage Sidebar</p>
                <h2 class="text-xl font-black tracking-tight text-stone-950">
                    {{ $sidebar['title'] ?: 'Inside NewsTech' }}
                </h2>
                @if (filled($sidebar['content']))
                    <p class="text-sm leading-7 text-stone-600">{{ $sidebar['content'] }}</p>
                @endif
            </div>

            @if (filled($sidebar['link_label']) && filled($sidebar['link_url']))
                <a
                    href="{{ $sidebar['link_url'] }}"
                    class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:border-amber-300 hover:bg-amber-100 sm:w-auto"
                >
                    {{ $sidebar['link_label'] }}
                </a>
            @endif
        </x-newstech::panel>
    @else
        <x-newstech::panel class="space-y-5 border-stone-200 bg-white p-6 text-stone-700 shadow-stone-200/60">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Sidebar Fallback</p>
                <h2 class="text-xl font-black tracking-tight text-stone-950">Use this space for featured updates.</h2>
                <p class="text-sm leading-7 text-stone-600">
                    Add a homepage sidebar title and content from admin settings to replace this fallback block.
                </p>
            </div>

            <div class="space-y-3">
                @forelse ($featuredArticles->take(3) as $article)
                    <x-newstech-frontend::article-list-item :article="$article" />
                @empty
                    @foreach ($latestArticles->take(3) as $article)
                        <x-newstech-frontend::article-list-item :article="$article" />
                    @endforeach
                @endforelse
            </div>
        </x-newstech::panel>
    @endif

    <x-newstech-frontend::newsletter-form
        source="homepage-sidebar"
        title="Stay close to the newsroom"
        description="Readers can subscribe from the homepage sidebar while newsletter delivery workflows continue to evolve later."
        compact
    />
</div>
