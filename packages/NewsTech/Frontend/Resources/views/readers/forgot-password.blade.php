@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Forgot Password',
        'Request a password reset link for your NewsTech reader account.',
        route('newstech.readers.password.request')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Access"
    title="Forgot your password?"
    lead="Enter your reader account email address and we will send you a reset link."
>
    <x-newstech::panel class="mx-auto max-w-2xl border-stone-200 bg-white p-6 sm:p-8">
        @if (session('reader_password_status'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('reader_password_status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('newstech.readers.password.email') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="reader-forgot-password-email" class="text-sm font-semibold text-stone-900">Email</label>
                <input id="reader-forgot-password-email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                @error('email')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                    Email Reset Link
                </button>

                <p class="text-sm text-stone-500">
                    Remembered it?
                    <a href="{{ route('newstech.readers.login') }}" class="font-semibold text-amber-700 underline underline-offset-4">Back to login</a>
                </p>
            </div>
        </form>
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
