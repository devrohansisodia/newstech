@props([
    'seo',
    'eyebrow',
    'title',
    'lead',
])

<x-newstech-frontend::layouts.app
    :title="$seo->title"
    :meta-description="$seo->description"
    :seo="$seo"
>
    <div class="space-y-10">
        @if (request()->routeIs('newstech.account.dashboard'))
            {!! newstech_view_render_event('frontend.account.dashboard.top') !!}
        @endif

        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>
            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">{{ $title }}</span>
        </nav>

        <section class="space-y-6">
            <x-newstech-frontend::section-heading
                :eyebrow="$eyebrow"
                :title="$title"
                :description="$lead"
            />
        </section>

        <div class="space-y-8">
            {{ $slot }}
        </div>

        @if (request()->routeIs('newstech.account.dashboard'))
            {!! newstech_view_render_event('frontend.account.dashboard.bottom') !!}
        @endif
    </div>
</x-newstech-frontend::layouts.app>
