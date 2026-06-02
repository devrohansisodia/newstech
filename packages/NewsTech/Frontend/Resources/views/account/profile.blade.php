@php
    $seo = \NewsTech\Core\Support\SeoData::make(
        config('newstech.brand.name').' | Reader Profile',
        'Update your NewsTech reader profile details.',
        route('newstech.account.profile')
    );
@endphp

<x-newstech-frontend::page-shell
    :seo="$seo"
    eyebrow="Reader Account"
    title="Profile settings"
    lead="Keep your reader account details current so saved articles and moderated comments stay tied to the right identity."
>
    <div class="space-y-6">
        @include('newstech-frontend::account.partials.nav')

        @if (session('account_status'))
            <x-newstech::panel class="border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('account_status') }}
            </x-newstech::panel>
        @endif

        <x-newstech::panel class="mx-auto max-w-3xl border-stone-200 bg-white p-6 sm:p-8">
            <form method="POST" action="{{ route('newstech.account.profile.update') }}" class="space-y-5">
                @csrf

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="reader-profile-name" class="text-sm font-semibold text-stone-900">Name</label>
                        <input id="reader-profile-name" name="name" type="text" value="{{ old('name', $reader?->name) }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                        @error('name')
                            <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="reader-profile-email" class="text-sm font-semibold text-stone-900">Email</label>
                        <input id="reader-profile-email" name="email" type="email" value="{{ old('email', $reader?->email) }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                        @error('email')
                            <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="reader-profile-website" class="text-sm font-semibold text-stone-900">Website</label>
                    <input id="reader-profile-website" name="website" type="url" value="{{ old('website', $reader?->website) }}" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                    @error('website')
                        <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="reader-profile-bio" class="text-sm font-semibold text-stone-900">Bio</label>
                    <textarea id="reader-profile-bio" name="bio" rows="5" class="w-full rounded-[1.5rem] border border-stone-200 bg-white px-4 py-3 text-sm leading-7 text-stone-700 focus:border-amber-300 focus:outline-none">{{ old('bio', $reader?->bio) }}</textarea>
                    @error('bio')
                        <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="reader-profile-password" class="text-sm font-semibold text-stone-900">New Password</label>
                        <input id="reader-profile-password" name="password" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                        @error('password')
                            <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="reader-profile-password-confirmation" class="text-sm font-semibold text-stone-900">Confirm New Password</label>
                        <input id="reader-profile-password-confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-700 focus:border-amber-300 focus:outline-none">
                    </div>
                </div>

                <button type="submit" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 transition hover:border-amber-300 hover:bg-amber-100">
                    Save Profile
                </button>
            </form>
        </x-newstech::panel>
    </div>
</x-newstech-frontend::page-shell>
