<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Newsletter Subscribers'"
    meta-description="NewsTech newsletter subscriber management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Audience Module</p>
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Newsletter Subscribers</h2>
                    <p class="max-w-3xl text-base leading-8 text-stone-600">
                        Review subscriber status, manage unsubscribe or inactive records, and hand off the active audience to newsletter campaigns.
                    </p>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.newsletter.campaigns.index')"
                    tone="primary"
                >
                    Campaigns
                </x-newstech-admin::form.button>
            </div>

            @if (session('newsletter_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('newsletter_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card eyebrow="Audience" title="Total Subscribers" :value="$subscriberCount" description="All newsletter subscriber records stored in the platform." />
            <x-newstech-admin::stat-card eyebrow="Active" title="Active Subscribers" :value="$activeSubscriberCount" description="Recipients eligible for newsletter campaign delivery." />
            <x-newstech-admin::stat-card eyebrow="Homepage" title="Homepage Signups" :value="$homepageSubscriberCount" description="Subscribers collected from the homepage signup form source." />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
