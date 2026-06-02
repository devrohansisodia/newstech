<x-newstech-frontend::layouts.app
    title="Newsletter Unsubscribe"
    meta-description="Manage your newsletter subscription status."
>
    <div class="space-y-10">
        <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.25em] text-stone-500">
            <a href="{{ route('newstech.home') }}" class="transition hover:text-stone-950">Home</a>
            <span class="h-1 w-1 rounded-full bg-stone-300"></span>
            <span class="text-stone-700">Newsletter</span>
        </nav>

        <section class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-600">Newsletter</p>
            <h1 class="text-4xl font-black tracking-tight text-stone-950">You have been unsubscribed</h1>
            <p class="max-w-3xl text-base leading-8 text-stone-600">
                This email address has been removed from future newsletter campaign sends. You can resubscribe later if newsletter resubscribe is allowed.
            </p>
        </section>

        <x-newstech::panel class="border-stone-200 bg-white p-6 text-sm leading-7 text-stone-600 shadow-sm shadow-stone-200/60">
            <p class="font-semibold text-stone-950">{{ $subscriber->email }}</p>
            <p>Status: {{ $subscriber->statusLabel() }}</p>
            <p>Unsubscribed at: {{ $subscriber->unsubscribed_at?->format('M d, Y · H:i') ?: 'Just now' }}</p>
        </x-newstech::panel>

        @if (config('newstech-newsletter.allow_resubscribe', true))
            <x-newstech::panel class="border-stone-200 bg-white p-6 text-sm leading-7 text-stone-600 shadow-sm shadow-stone-200/60">
                You can subscribe again later from any newsletter form across the site.
            </x-newstech::panel>
        @endif
    </div>
</x-newstech-frontend::layouts.app>
