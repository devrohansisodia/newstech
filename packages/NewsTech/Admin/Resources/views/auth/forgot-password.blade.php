<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Forgot Password'"
    meta-description="Request a password reset link for the NewsTech admin panel."
    :show-navigation="false"
>
    <x-newstech::panel class="space-y-8 border-white/10 bg-slate-950/78 p-8 text-slate-100 shadow-black/30 sm:p-10">
        <div class="space-y-3">
            <x-newstech::brand-mark tone="dark" size="prominent" :logo-only="true" />
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.newstech.password.email') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label for="admin-forgot-password-email" class="text-sm font-semibold text-slate-200">Email Address</label>
                <input
                    id="admin-forgot-password-email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    class="nt-admin-auth-input w-full rounded-2xl border border-white/12 px-4 py-3.5 text-base outline-none transition"
                    placeholder="admin@example.com"
                >
            </div>

            <div class="space-y-4 pt-2">
                <div class="flex justify-center">
                    <button
                        type="submit"
                        class="inline-flex min-w-[13rem] items-center justify-center rounded-2xl bg-sky-400 px-6 py-3.5 text-base font-semibold text-slate-950 transition hover:bg-sky-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-200 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950 sm:min-w-[16rem]"
                    >
                        Email Reset Link
                    </button>
                </div>

                <div class="text-center">
                    <a
                        href="{{ route('admin.newstech.login') }}"
                        class="text-sm font-medium text-sky-300 transition hover:text-sky-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-200 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950"
                    >
                        Back to sign in
                    </a>
                </div>
            </div>
        </form>
    </x-newstech::panel>
</x-newstech-admin::layouts.app>
