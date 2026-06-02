@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Reader Registration',
        'Create a NewsTech reader account to save articles and manage your profile.',
        route('newstech.readers.register')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Access"
    title="Create your reader account"
    lead="Register once to save articles, keep a reader profile, and prepare for future personalized NewsTech features."
>
    <x-newstech::panel class="mx-auto max-w-2xl border-stone-200 bg-white p-6 sm:p-8">
        <form method="POST" action="{{ route('newstech.readers.register.store') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="reader-register-name" class="text-sm font-semibold text-stone-900">Name</label>
                <input id="reader-register-name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                @error('name')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="reader-register-email" class="text-sm font-semibold text-stone-900">Email</label>
                <input id="reader-register-email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                @error('email')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="space-y-2">
                    <label for="reader-register-password" class="text-sm font-semibold text-stone-900">Password</label>
                    <input id="reader-register-password" name="password" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                    @error('password')
                        <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="reader-register-password-confirmation" class="text-sm font-semibold text-stone-900">Confirm Password</label>
                    <input id="reader-register-password-confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                    Create Account
                </button>

                <p class="text-sm text-stone-500">
                    Already registered?
                    <a href="{{ route('newstech.readers.login') }}" class="font-semibold text-amber-700 underline underline-offset-4">Sign in</a>
                </p>
            </div>
        </form>
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
