@php
    $readerGuard = config('newstech-reader.auth.guard');
    $authenticatedReader = auth($readerGuard)->user();
    $initials = $authenticatedReader
        ? str($authenticatedReader->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => str($segment)->substr(0, 1)->upper()->toString())
            ->implode('')
        : '';
@endphp

<details class="nt-frontend-auth-menu relative z-40" data-frontend-auth-menu>
    <summary
        class="flex h-12 w-12 cursor-pointer list-none items-center justify-center rounded-full border border-stone-200 bg-white text-stone-700 shadow-sm shadow-stone-200/50 transition hover:border-amber-300 hover:bg-amber-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
        data-frontend-auth-menu-trigger
    >
        @if ($authenticatedReader && $initials !== '')
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-100 text-xs font-black tracking-[0.22em] text-amber-800">
                {{ $initials }}
            </span>
        @else
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M15 19a4 4 0 0 0-6 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" />
                <circle cx="12" cy="8.5" r="3.5" stroke-width="1.8" />
                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z" stroke-width="1.8" />
            </svg>
        @endif

        <span class="sr-only">
            {{ $authenticatedReader ? 'Open reader menu' : 'Open login and registration menu' }}
        </span>
    </summary>

    <div class="absolute right-0 top-full z-50 mt-3 w-72 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-300/25" data-frontend-auth-menu-panel>
        @if ($authenticatedReader)
            <div class="border-b border-stone-200 bg-stone-50 px-4 py-4">
                <p class="text-sm font-semibold text-stone-950">{{ $authenticatedReader->name }}</p>
                <p class="mt-1 break-all text-sm text-stone-500">{{ $authenticatedReader->email }}</p>
            </div>

            <div class="space-y-1 p-2 text-sm font-medium text-stone-700">
                <a href="{{ route('newstech.account.dashboard') }}" class="flex items-center rounded-2xl px-3 py-2.5 transition hover:bg-stone-100">
                    Account
                </a>
                <a href="{{ route('newstech.account.bookmarks') }}" class="flex items-center rounded-2xl px-3 py-2.5 transition hover:bg-stone-100">
                    Saved Articles
                </a>
                <a href="{{ route('newstech.account.history') }}" class="flex items-center rounded-2xl px-3 py-2.5 transition hover:bg-stone-100">
                    Reading History
                </a>
                <form method="POST" action="{{ route('newstech.readers.logout') }}">
                    @csrf

                    <button type="submit" class="flex w-full items-center rounded-2xl px-3 py-2.5 text-left transition hover:bg-stone-100">
                        Logout
                    </button>
                </form>
            </div>
        @else
            <div class="border-b border-stone-200 bg-stone-50 px-4 py-4">
                <p class="text-sm font-semibold text-stone-950">Reader account</p>
                <p class="mt-1 text-sm text-stone-500">Sign in to save articles and review your reading activity.</p>
            </div>

            <div class="space-y-1 p-2 text-sm font-medium text-stone-700">
                <a href="{{ route('newstech.readers.login') }}" class="flex items-center rounded-2xl px-3 py-2.5 transition hover:bg-stone-100">
                    Login
                </a>
                <a href="{{ route('newstech.readers.register') }}" class="flex items-center rounded-2xl px-3 py-2.5 transition hover:bg-stone-100">
                    Register
                </a>
                <a href="{{ route('newstech.readers.password.request') }}" class="flex items-center rounded-2xl px-3 py-2.5 transition hover:bg-stone-100">
                    Forgot Password
                </a>
            </div>
        @endif
    </div>
</details>
