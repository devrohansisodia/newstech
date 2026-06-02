<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Login'"
    meta-description="Secure sign-in for the NewsTech admin panel."
    :show-navigation="false"
>
    <x-newstech::panel class="space-y-8 border-white/10 bg-slate-950/78 p-8 text-slate-100 shadow-black/30 sm:p-10">
        <div class="space-y-3">
            <x-newstech::brand-mark tone="dark" size="prominent" :logo-only="true" />
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.newstech.login.store') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="email" class="text-sm font-semibold text-slate-200">Email Address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="nt-admin-auth-input w-full rounded-2xl border border-white/12 px-4 py-3.5 text-base outline-none transition"
                    placeholder="admin@newstech.test"
                >
            </div>

            <div class="space-y-2">
                <label for="password" class="text-sm font-semibold text-slate-200">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="nt-admin-auth-input w-full rounded-2xl border border-white/12 px-4 py-3.5 text-base outline-none transition"
                    placeholder="Enter your password"
                >
            </div>

            <label class="flex items-center gap-3 text-sm text-slate-300">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="h-4 w-4 rounded border-white/20 bg-white/5 text-sky-400 focus:ring-sky-400"
                >
                <span>Remember me</span>
            </label>

            <div class="flex justify-end">
                <a
                    href="{{ route('admin.newstech.password.request') }}"
                    class="text-sm font-medium text-sky-300 transition hover:text-sky-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-200 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                >
                    Forgot password?
                </a>
            </div>

            <div class="flex justify-center pt-2">
                <button
                    type="submit"
                    class="inline-flex min-w-[13rem] items-center justify-center rounded-2xl bg-sky-400 px-6 py-3.5 text-base font-semibold text-slate-950 transition hover:bg-sky-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-200 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 sm:min-w-[16rem]"
                >
                    Sign In
                </button>
            </div>
        </form>
    </x-newstech::panel>
</x-newstech-admin::layouts.app>
