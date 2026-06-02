<nav class="flex flex-wrap gap-3">
    <a href="{{ route('newstech.account.dashboard') }}" class="rounded-full border {{ request()->routeIs('newstech.account.dashboard') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-stone-200 bg-white text-stone-700' }} px-4 py-2 text-sm font-semibold transition hover:border-amber-300 hover:bg-amber-50">
        Account Overview
    </a>
    <a href="{{ route('newstech.account.profile') }}" class="rounded-full border {{ request()->routeIs('newstech.account.profile') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-stone-200 bg-white text-stone-700' }} px-4 py-2 text-sm font-semibold transition hover:border-amber-300 hover:bg-amber-50">
        Profile
    </a>
    <a href="{{ route('newstech.account.bookmarks') }}" class="rounded-full border {{ request()->routeIs('newstech.account.bookmarks') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-stone-200 bg-white text-stone-700' }} px-4 py-2 text-sm font-semibold transition hover:border-amber-300 hover:bg-amber-50">
        Saved Articles
    </a>
    <a href="{{ route('newstech.account.history') }}" class="rounded-full border {{ request()->routeIs('newstech.account.history') ? 'border-amber-300 bg-amber-50 text-amber-700' : 'border-stone-200 bg-white text-stone-700' }} px-4 py-2 text-sm font-semibold transition hover:border-amber-300 hover:bg-amber-50">
        Reading History
    </a>
</nav>
