@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Reset Password',
        'Reset your NewsTech reader account password.',
        route('newstech.readers.password.reset', ['token' => $token, 'email' => $email])
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Access"
    title="Reset your password"
    lead="Choose a new password for your NewsTech reader account."
>
    <x-newstech::panel class="mx-auto max-w-2xl border-stone-200 bg-white p-6 sm:p-8">
        <form method="POST" action="{{ route('newstech.readers.password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="space-y-2">
                <label for="reader-reset-password-email" class="text-sm font-semibold text-stone-900">Email</label>
                <input id="reader-reset-password-email" name="email" type="email" value="{{ old('email', $email) }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                @error('email')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="reader-reset-password-password" class="text-sm font-semibold text-stone-900">Password</label>
                    <input id="reader-reset-password-password" name="password" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                    @error('password')
                        <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="reader-reset-password-confirmation" class="text-sm font-semibold text-stone-900">Confirm Password</label>
                    <input id="reader-reset-password-confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                </div>
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                Reset Password
            </button>
        </form>
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
