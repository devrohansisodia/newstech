<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Newsletter Subscriber'"
    meta-description="Newsletter subscriber detail."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Subscriber Detail</p>
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">{{ $subscriber->email }}</h2>
                    <p class="max-w-3xl text-base leading-8 text-stone-600">
                        Update subscriber status, inspect source metadata, and review the most recent newsletter delivery records for this email.
                    </p>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.newsletter.index')"
                    tone="ghost"
                >
                    Back to subscribers
                </x-newstech-admin::form.button>
            </div>

            @if (session('newsletter_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('newsletter_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-4">
            <x-newstech-admin::stat-card eyebrow="Status" title="{{ $subscriber->statusLabel() }}" :value="$subscriber->statusLabel()" description="Current newsletter state for this subscriber record." />
            <x-newstech-admin::stat-card eyebrow="Source" title="{{ $subscriber->source ? str($subscriber->source)->headline()->toString() : 'Not set' }}" :value="$subscriber->source ? str($subscriber->source)->headline()->toString() : 'Not set'" description="Latest stored subscribe source." />
            <x-newstech-admin::stat-card eyebrow="Subscribed" title="{{ $subscriber->subscribed_at?->format('M d, Y') ?: 'Unknown' }}" :value="$subscriber->subscribed_at?->format('M d, Y') ?: 'Unknown'" description="Latest subscribe timestamp stored for this record." />
            <x-newstech-admin::stat-card eyebrow="Unsubscribed" title="{{ $subscriber->unsubscribed_at?->format('M d, Y') ?: 'Active' }}" :value="$subscriber->unsubscribed_at?->format('M d, Y') ?: 'Active'" description="Shown when the subscriber has opted out or been deactivated." />
        </div>

        <form method="POST" action="{{ route('admin.newstech.newsletter.subscribers.update', $subscriber) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Subscriber Controls"
                        description="Manage subscriber status and optional source labeling without touching the frontend signup forms."
                    >
                        <x-newstech-admin::form.input
                            name="email_display"
                            label="Email"
                            :value="$subscriber->email"
                            disabled
                        />

                        <x-newstech-admin::form.select
                            name="status"
                            label="Status"
                            :options="['active' => 'Active', 'unsubscribed' => 'Unsubscribed', 'inactive' => 'Inactive']"
                            :value="old('status', $subscriber->status)"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="source"
                            label="Source"
                            :value="old('source', $subscriber->source)"
                            placeholder="homepage"
                            hint="Optional source label such as homepage, article, footer, import, or manual."
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Request Metadata"
                        description="Request details stored when the subscriber most recently joined or was reactivated."
                    >
                        <x-newstech-admin::form.input
                            name="ip_address_display"
                            label="IP Address"
                            :value="$subscriber->ip_address"
                            disabled
                        />

                        <x-newstech-admin::form.textarea
                            name="user_agent_display"
                            label="User Agent"
                            :value="$subscriber->user_agent"
                            rows="4"
                            disabled
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Recent Deliveries"
                        description="Latest campaign recipient rows for this subscriber."
                    >
                        @forelse ($latestRecipients as $recipient)
                            <div class="rounded-2xl border border-stone-200 bg-white p-4 text-sm leading-7 text-stone-600">
                                <p class="font-semibold text-stone-950">{{ $recipient->campaign?->name ?? 'Deleted campaign' }}</p>
                                <p>Status: {{ $recipient->statusLabel() }}</p>
                                <p>Sent: {{ $recipient->sent_at?->format('M d, Y · H:i') ?: 'Not sent' }}</p>
                                <p>Failure: {{ $recipient->failure_reason ?: 'None' }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-4 py-5 text-sm text-stone-500">
                                No campaign deliveries recorded for this subscriber yet.
                            </div>
                        @endforelse
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Actions"
                        description="Save subscriber changes or remove the record entirely."
                    >
                        <x-newstech-admin::form.actions>
                            <x-newstech-admin::form.button type="submit" tone="primary">
                                Save subscriber
                            </x-newstech-admin::form.button>
                        </x-newstech-admin::form.actions>
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.newstech.newsletter.subscribers.destroy', $subscriber) }}">
            @csrf
            @method('DELETE')

            <x-newstech-admin::form.button type="submit" tone="danger">
                Delete subscriber
            </x-newstech-admin::form.button>
        </form>
    </div>
</x-newstech-admin::layouts.app>
