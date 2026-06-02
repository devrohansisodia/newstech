@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Verify Email',
        'Verify your NewsTech reader email address.',
        route('newstech.readers.verification.notice')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Access"
    title="Verify your email"
    lead="Your reader account is active, but you should verify your email address to secure account recovery and future reader features."
>
    <x-newstech::panel class="mx-auto max-w-2xl space-y-5 border-stone-200 bg-white p-6 sm:p-8">
        @if (session('verification_status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('verification_status') }}
            </div>
        @endif

        <p class="text-sm leading-7 text-stone-600">
            We sent a verification link to <span class="font-semibold text-stone-950">{{ $reader?->email }}</span>. If it did not arrive, you can request another link below.
        </p>

        <form method="POST" action="{{ route('newstech.readers.verification.send') }}">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                Resend Verification Email
            </button>
        </form>
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
