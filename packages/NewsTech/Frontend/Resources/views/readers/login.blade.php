@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Reader Login',
        'Sign in to your NewsTech reader account to save articles and manage your profile.',
        route('newstech.readers.login')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Access"
    title="Sign in to your account"
    lead="Use your reader account to save articles, manage your profile, and participate in moderated discussions."
>
    <x-newstech::panel class="mx-auto max-w-2xl border-stone-200 bg-white p-6 sm:p-8">
        <form method="POST" action="{{ route('newstech.readers.login.store') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="reader-login-email" class="text-sm font-semibold text-stone-900">Email</label>
                <input id="reader-login-email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                @error('email')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="reader-login-password" class="text-sm font-semibold text-stone-900">Password</label>
                <input id="reader-login-password" name="password" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                @error('password')
                    <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-3 text-sm text-stone-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-amber-600 focus:ring-amber-300">
                <span>Keep me signed in</span>
            </label>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                    Sign In
                </button>

                <div class="space-y-1 text-sm text-stone-500">
                    <p>
                        New here?
                        <a href="{{ route('newstech.readers.register') }}" class="font-semibold text-amber-700 underline underline-offset-4">Create an account</a>
                    </p>
                    <p>
                        Forgot your password?
                        <a href="{{ route('newstech.readers.password.request') }}" class="font-semibold text-amber-700 underline underline-offset-4">Reset it here</a>
                    </p>
                </div>
            </div>
        </form>
    </x-newstech::panel>
</x-newstech-frontend::page-shell>
