@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Reader';
    $pageTitle ??= 'Reader Form';
    $pageDescription ??= null;
    $formId = 'reader-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech reader account form."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.readers.index')" tone="ghost">
                    Back to Readers
                </x-newstech-admin::form.button>

                <x-newstech-admin::form.button type="submit" tone="primary" :form="$formId">
                    {{ $submitLabel }}
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        <p class="max-w-3xl text-sm font-medium leading-7 text-stone-700">
            Reader accounts are public-facing identities for comments, bookmarks, and account flows. They are separate from admin users.
        </p>

        @if (session('reader_status'))
            <x-newstech::panel class="border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('reader_status') }}
            </x-newstech::panel>
        @endif

        <form id="{{ $formId }}" method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Reader Basics"
                        description="Primary reader identity and login details."
                    >
                        <x-newstech-admin::form.input
                            name="name"
                            label="Name"
                            :value="$reader->name"
                            placeholder="Reader name"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="email"
                            label="Email"
                            type="email"
                            :value="$reader->email"
                            placeholder="reader@example.com"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="website"
                            label="Website"
                            type="url"
                            :value="$reader->website"
                            placeholder="https://reader-site.example"
                        />

                        <x-newstech-admin::form.textarea
                            name="bio"
                            label="Bio"
                            :value="$reader->bio"
                            placeholder="Reader profile summary."
                            rows="5"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Password"
                        description="{{ $method === 'POST' ? 'Set the initial password for this reader account.' : 'Leave blank to keep the current password unchanged.' }}"
                    >
                        <x-newstech-admin::form.input
                            name="password"
                            label="Password"
                            type="password"
                            :required="$method === 'POST'"
                        />

                        <x-newstech-admin::form.input
                            name="password_confirmation"
                            label="Confirm Password"
                            type="password"
                            :required="$method === 'POST'"
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Account Status"
                        description="Inactive or deleted readers cannot sign in to the frontend."
                    >
                        <x-newstech-admin::form.toggle
                            name="is_active"
                            label="Active Reader"
                            :checked="(bool) $reader->is_active"
                            hint="Turn this off to prevent frontend login while preserving the account history."
                        />

                        <div class="rounded-2xl border border-stone-200 bg-white p-5 text-sm leading-7 text-stone-600">
                            <p><span class="font-semibold text-stone-950">Joined:</span> {{ $reader->created_at?->format('M d, Y H:i') ?? 'Not created yet' }}</p>
                            <p><span class="font-semibold text-stone-950">Last Login:</span> {{ $reader->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</p>
                            <p><span class="font-semibold text-stone-950">Email Verified:</span> {{ $reader->email_verified_at?->format('M d, Y H:i') ?? 'Not verified' }}</p>
                            <p><span class="font-semibold text-stone-950">Comments:</span> {{ $reader->comments_count ?? $reader->comments()->count() }}</p>
                            <p><span class="font-semibold text-stone-950">Bookmarks:</span> {{ $reader->bookmarks_count ?? $reader->bookmarks()->count() }}</p>
                        </div>
                    </x-newstech-admin::form.section>

                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
