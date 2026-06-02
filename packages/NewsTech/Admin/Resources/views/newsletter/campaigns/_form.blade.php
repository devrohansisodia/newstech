@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Campaign';
    $pageTitle ??= 'Newsletter Campaign';
    $pageDescription ??= null;
    $formId = 'newsletter-campaign-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="Newsletter campaign form."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.newsletter.campaigns.index')" tone="ghost">
                    Back to Campaigns
                </x-newstech-admin::form.button>

                <x-newstech-admin::form.button type="submit" tone="primary" :form="$formId">
                    {{ $submitLabel }}
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        <form id="{{ $formId }}" method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(20rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Campaign Basics"
                        description="Define the campaign name, subject line, and optional preheader copy."
                    >
                        <x-newstech-admin::form.input
                            name="name"
                            label="Name"
                            :value="old('name', $campaign->name)"
                            placeholder="Morning Briefing - June 2026"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="subject"
                            label="Subject"
                            :value="old('subject', $campaign->subject)"
                            placeholder="Your NewsTech morning briefing"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="preheader"
                            label="Preheader"
                            :value="old('preheader', $campaign->preheader)"
                            placeholder="Top published stories and editorial picks for today"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Campaign Content"
                        description="Compose the campaign email body using the shared rich text editor."
                    >
                        <x-newstech-admin::form.rich-text-editor
                            name="content"
                            label="Content"
                            :value="old('content', $campaign->content)"
                            placeholder="<p>Newsletter campaign content</p>"
                            rows="18"
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Delivery Controls"
                        description="Draft campaigns can be edited. Scheduled status stores a future send time for later automation."
                    >
                        <x-newstech-admin::form.select
                            name="status"
                            label="Status"
                            :options="['draft' => 'Draft', 'scheduled' => 'Scheduled']"
                            :value="old('status', $campaign->status)"
                            required
                        />

                        <x-newstech-admin::form.input
                            name="scheduled_at"
                            label="Scheduled At"
                            type="datetime-local"
                            :value="old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\\TH:i'))"
                            hint="Stored for later scheduler automation. Manual send is still available from the campaign detail page."
                        />
                    </x-newstech-admin::form.section>

                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
