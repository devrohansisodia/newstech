@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Reader Account',
        'Manage your NewsTech reader account and saved articles.',
        route('newstech.account.dashboard')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Account"
    title="Account dashboard"
    lead="Your reader account is separate from the admin panel and powers personal features like saved articles."
>
    <div class="space-y-6">
        @include('newstech-frontend::account.partials.nav')

        <div class="grid gap-6 lg:grid-cols-3">
            <x-newstech::panel class="border-stone-200 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600">Reader</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-stone-950">{{ $reader?->name }}</h2>
                <p class="mt-2 text-sm text-stone-600">{{ $reader?->email }}</p>
            </x-newstech::panel>

            <x-newstech::panel class="border-stone-200 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600">Saved Articles</p>
                <p class="mt-3 text-4xl font-black tracking-tight text-stone-950">{{ $bookmarkCount }}</p>
                <p class="mt-2 text-sm text-stone-600">Published stories currently saved to your account.</p>
            </x-newstech::panel>

            <x-newstech::panel class="border-stone-200 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600">Profile</p>
                <p class="mt-3 text-sm leading-7 text-stone-600">
                    Update your display name, email, optional website, bio, or password from the profile screen.
                </p>
            </x-newstech::panel>
        </div>

        @if ($reader && ! $reader->hasVerifiedEmail())
            <x-newstech::panel class="border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-800">
                Your email address is not verified yet.
                <a href="{{ route('newstech.readers.verification.notice') }}" class="underline underline-offset-4">Review verification options</a>
            </x-newstech::panel>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <x-newstech::panel class="border-stone-200 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600">Bookmark Folders</p>
                <p class="mt-3 text-4xl font-black tracking-tight text-stone-950">{{ $folderCount }}</p>
                <p class="mt-2 text-sm text-stone-600">Organize saved articles into reader-specific folders.</p>
            </x-newstech::panel>

            <x-newstech::panel class="border-stone-200 bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-600">Reading History</p>
                <p class="mt-3 text-4xl font-black tracking-tight text-stone-950">{{ $historyCount }}</p>
                <p class="mt-2 text-sm text-stone-600">Published article views stored while you were signed in.</p>
            </x-newstech::panel>
        </div>
    </div>
</x-newstech-frontend::page-shell>
