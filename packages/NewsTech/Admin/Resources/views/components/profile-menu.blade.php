@php
    $initials = str($currentAdminUser?->name ?? 'AD')
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $segment): string => str($segment)->substr(0, 1)->upper()->toString())
        ->implode('');
@endphp

@if ($currentAdminUser)
    <details class="nt-admin-profile-menu relative z-[2200]" data-admin-profile-menu>
        <summary
            class="flex cursor-pointer list-none items-center gap-3 rounded-2xl border border-stone-200 bg-white px-3 py-2 text-stone-700 transition hover:bg-stone-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300 focus-visible:ring-offset-2 focus-visible:ring-offset-stone-100"
            data-admin-profile-menu-trigger
        >
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-amber-200 bg-amber-50 text-sm font-black tracking-[0.18em] text-amber-700">
                {{ $initials !== '' ? $initials : 'AD' }}
            </span>
            <svg class="h-4 w-4 text-stone-500 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
            </svg>
        </summary>

        <div class="absolute right-0 top-full z-[2300] mt-3 w-72 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl shadow-stone-300/30" data-admin-profile-menu-panel>
            <div class="border-b border-stone-200 bg-stone-50 px-4 py-4">
                <p class="text-sm font-semibold text-stone-950">{{ $currentAdminUser->name }}</p>
                <p class="mt-1 break-all text-sm text-stone-500">{{ $currentAdminUser->email }}</p>
            </div>

            <div class="space-y-1 p-2">
                <a
                    href="{{ route('admin.newstech.profile.edit') }}"
                    class="flex items-center rounded-xl px-3 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-100"
                >
                    Edit Profile
                </a>

                <form method="POST" action="{{ route(config('newstech-admin.auth.logout_route')) }}">
                    @csrf

                    <button
                        type="submit"
                        class="flex w-full items-center rounded-xl px-3 py-2 text-left text-sm font-medium text-stone-700 transition hover:bg-stone-100"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </details>
@endif
