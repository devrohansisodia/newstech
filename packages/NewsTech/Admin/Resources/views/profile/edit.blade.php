<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Profile'"
    meta-description="Admin profile settings."
>
    <div class="space-y-6">
        @if (session('profile_status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('profile_status') }}
            </div>
        @endif

        <x-newstech-admin::page-header title="Profile" description="Update your account details and password.">
            <x-slot:actions>
                <x-newstech-admin::form.button
                    :href="route('admin.newstech.dashboard')"
                    tone="ghost"
                >
                    Back to Dashboard
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        <form method="POST" action="{{ route('admin.newstech.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.8fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section title="Account">
                        <x-newstech-admin::form.input
                            name="name"
                            label="Name"
                            :value="$adminUser?->name"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="email"
                            label="Email"
                            type="email"
                            :value="$adminUser?->email"
                            required
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section title="Password" description="Leave the password fields blank to keep your current password.">
                        <x-newstech-admin::form.input
                            name="current_password"
                            label="Current Password"
                            type="password"
                            hint="Required only when changing your password."
                        />

                        <x-newstech-admin::form.input
                            name="password"
                            label="New Password"
                            type="password"
                        />

                        <x-newstech-admin::form.input
                            name="password_confirmation"
                            label="Confirm New Password"
                            type="password"
                        />
                    </x-newstech-admin::form.section>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <x-newstech-admin::form.button type="submit" tone="primary">
                    Save Profile
                </x-newstech-admin::form.button>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
