<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Advertisements'"
    meta-description="Advertisement management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Advertisements</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.advertisements.create')"
                    tone="primary"
                >
                    Add Advertisement
                </x-newstech-admin::form.button>
            </div>

            @if (session('advertisement_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('advertisement_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-newstech-admin::stat-card eyebrow="Inventory" title="Total Ads" :value="$advertisementCount" description="Managed advertisements currently stored in the platform." />
            <x-newstech-admin::stat-card eyebrow="Status" title="Active Ads" :value="$activeAdvertisementCount" description="Advertisements currently eligible to render based on status and schedule." />
            <x-newstech-admin::stat-card eyebrow="Tracking" title="Impressions" :value="$totalImpressions" description="Aggregate impressions recorded across all managed advertisements." />
            <x-newstech-admin::stat-card eyebrow="Tracking" title="Clicks" :value="$totalClicks" description="Aggregate click-through events recorded through the click redirect route." />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
