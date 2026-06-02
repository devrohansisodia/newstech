<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Newsletter Campaign'"
    meta-description="Newsletter campaign detail."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Campaign Detail</p>
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">{{ $campaign->name }}</h2>
                    <p class="max-w-3xl text-base leading-8 text-stone-600">
                        {{ $campaign->subject }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    @if ($campaign->canEdit())
                        <x-newstech-admin::form.button :href="route('admin.newstech.newsletter.campaigns.edit', $campaign)" tone="ghost">
                            Edit
                        </x-newstech-admin::form.button>
                    @endif

                    @if ($campaign->canSend())
                        <form method="POST" action="{{ route('admin.newstech.newsletter.campaigns.send', $campaign) }}">
                            @csrf
                            <x-newstech-admin::form.button type="submit" tone="primary">
                                Send Now
                            </x-newstech-admin::form.button>
                        </form>
                    @endif
                </div>
            </div>

            @if (session('newsletter_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('newsletter_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-4">
            <x-newstech-admin::stat-card eyebrow="Status" title="{{ $campaign->statusLabel() }}" :value="$campaign->statusLabel()" description="Current campaign delivery state." />
            <x-newstech-admin::stat-card eyebrow="Audience" title="Active Subscribers" :value="$activeSubscriberCount" description="Subscribers currently eligible to receive the campaign." />
            <x-newstech-admin::stat-card eyebrow="Delivery" title="Delivered" :value="$campaign->delivered_count" description="Recipient rows marked as successfully sent." />
            <x-newstech-admin::stat-card eyebrow="Failures" title="Failed" :value="$campaign->failed_count" description="Recipient rows that failed during mail sending." />
        </div>

        <x-newstech::panel class="space-y-4 border-stone-200 bg-white p-8 text-stone-700 shadow-stone-200/60">
            <div class="space-y-2">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Preheader</p>
                <p class="text-sm leading-7 text-stone-600">{{ $campaign->preheader ?: 'No preheader set.' }}</p>
            </div>

            <div class="space-y-2">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-amber-600">Content Preview</p>
                <div class="nt-prose max-w-none text-sm text-stone-700">
                    {!! $campaign->content !!}
                </div>
            </div>
        </x-newstech::panel>

        <x-newstech-admin::datagrid :grid="$recipientGrid" />
    </div>
</x-newstech-admin::layouts.app>
