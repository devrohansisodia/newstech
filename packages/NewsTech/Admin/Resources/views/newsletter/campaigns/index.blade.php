<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Newsletter Campaigns'"
    meta-description="Newsletter campaign management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Campaign Module</p>
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Newsletter Campaigns</h2>
                    <p class="max-w-3xl text-base leading-8 text-stone-600">
                        Build and send editorial newsletter campaigns to the active subscriber audience using Laravel mail.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-newstech-admin::form.button
                        :href="route('admin.newstech.newsletter.index')"
                        tone="ghost"
                    >
                        Subscribers
                    </x-newstech-admin::form.button>

                    <x-newstech-admin::form.button
                        :href="route('admin.newstech.newsletter.campaigns.create')"
                        tone="primary"
                    >
                        Create Campaign
                    </x-newstech-admin::form.button>
                </div>
            </div>

            @if (session('newsletter_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('newsletter_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card eyebrow="Campaigns" title="Total Campaigns" :value="$campaignCount" description="All newsletter campaigns currently stored." />
            <x-newstech-admin::stat-card eyebrow="Sent" title="Sent Campaigns" :value="$sentCampaignCount" description="Campaigns that completed a send pass." />
            <x-newstech-admin::stat-card eyebrow="Audience" title="Active Subscribers" :value="$activeSubscriberCount" description="Subscribers currently eligible to receive a campaign." />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
