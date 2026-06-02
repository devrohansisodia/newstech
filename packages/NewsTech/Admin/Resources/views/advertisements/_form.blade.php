@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Advertisement';
    $pageTitle ??= 'Advertisement Form';
    $pageDescription ??= null;
    $formId = 'advertisement-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech advertisement form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.advertisements.index')" tone="ghost">
                    Back to Advertisements
                </x-newstech-admin::form.button>

                <x-newstech-admin::form.button type="submit" tone="primary" :form="$formId">
                    {{ $submitLabel }}
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        @if (session('advertisement_status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('advertisement_status') }}
            </div>
        @endif

        <form id="{{ $formId }}" method="POST" action="{{ $action }}" class="space-y-6">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(20rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Advertisement Basics"
                        description="Define the advertisement identity, slot assignment, type, and activation status."
                    >
                        <x-newstech-admin::form.input
                            name="name"
                            label="Name"
                            :value="old('name', $advertisement->name)"
                            placeholder="Homepage Summer Campaign"
                            hint="Internal admin name for the advertisement."
                            required
                        />

                        <x-newstech-admin::form.input
                            name="slug"
                            label="Slug"
                            :value="old('slug', $advertisement->slug)"
                            placeholder="homepage-summer-campaign"
                            hint="Optional unique identifier. Leave blank to keep this field empty."
                        />

                        <x-newstech-admin::form.select
                            name="slot_key"
                            label="Slot"
                            :options="$slotOptions"
                            :value="old('slot_key', $advertisement->slot_key)"
                            placeholder="Choose a slot"
                            hint="Managed ads render into one configured slot without editing frontend blades."
                            required
                        />

                        <x-newstech-admin::form.select
                            name="type"
                            label="Ad Type"
                            :options="\NewsTech\Advertisement\Models\Advertisement::typeOptions()"
                            :value="old('type', $advertisement->type)"
                            placeholder="Choose an ad type"
                            hint="Image ads use the shared media picker. HTML ads render trusted admin-managed markup."
                            required
                        />

                        <x-newstech-admin::form.select
                            name="status"
                            label="Status"
                            :options="\NewsTech\Advertisement\Models\Advertisement::statusOptions()"
                            :value="old('status', $advertisement->status)"
                            placeholder="Choose a status"
                            hint="Inactive or out-of-schedule ads do not render."
                            required
                        />

                        <x-newstech-admin::form.input
                            name="title"
                            label="Public Title"
                            :value="old('title', $advertisement->title)"
                            placeholder="Trusted partner campaign"
                            hint="Optional label used in the rendered advertisement card."
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Advertisement Content"
                        description="Provide the image asset or trusted HTML content used by this advertisement."
                    >
                        <x-newstech-admin::form.media-picker
                            name="image_path"
                            label="Image Asset"
                            :value="old('image_path', $advertisement->image_path)"
                            preview-label="Current advertisement image"
                            hint="Required for image advertisements."
                        />

                        <x-newstech-admin::form.input
                            name="target_url"
                            label="Target URL"
                            type="url"
                            :value="old('target_url', $advertisement->target_url)"
                            placeholder="https://example.com/campaign"
                            hint="Optional destination. When click tracking is enabled, NewsTech will redirect through its tracking route."
                        />

                        <x-newstech-admin::form.textarea
                            name="html_content"
                            label="HTML / Code Content"
                            :value="old('html_content', $advertisement->html_content)"
                            rows="8"
                            placeholder="<div class='promo'>Trusted HTML campaign markup</div>"
                            hint="Only trusted admins should manage HTML ads. This content is rendered as trusted admin markup."
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Schedule And Behavior"
                        description="Control when the advertisement becomes eligible to render and how its links behave."
                    >
                        <x-newstech-admin::form.input
                            name="starts_at"
                            label="Starts At"
                            type="datetime-local"
                            :value="old('starts_at', $advertisement->starts_at?->format('Y-m-d\TH:i'))"
                            hint="Optional future start time."
                        />

                        <x-newstech-admin::form.input
                            name="ends_at"
                            label="Ends At"
                            type="datetime-local"
                            :value="old('ends_at', $advertisement->ends_at?->format('Y-m-d\TH:i'))"
                            hint="Optional expiry time after which the ad stops rendering."
                        />

                        <x-newstech-admin::form.input
                            name="priority"
                            label="Priority"
                            type="number"
                            :value="old('priority', $advertisement->priority)"
                            hint="Higher priority ads win when multiple active ads target the same slot."
                        />

                        <x-newstech-admin::form.toggle
                            name="open_in_new_tab"
                            label="Open In New Tab"
                            :checked="(bool) old('open_in_new_tab', $advertisement->open_in_new_tab)"
                            hint="Adds target=_blank to linked image advertisements."
                        />

                        <x-newstech-admin::form.toggle
                            name="nofollow"
                            label="Nofollow"
                            :checked="(bool) old('nofollow', $advertisement->nofollow)"
                            hint="Adds the nofollow rel attribute when the ad is linked."
                        />

                        <x-newstech-admin::form.toggle
                            name="sponsored"
                            label="Sponsored"
                            :checked="(bool) old('sponsored', $advertisement->sponsored)"
                            hint="Adds the sponsored rel attribute when the ad is linked."
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Tracking Snapshot"
                        description="Read-only aggregate counts stored on the advertisement record."
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-newstech-admin::stat-card eyebrow="Impressions" title="Rendered" :value="$advertisement->impressions_count ?? 0" description="Incremented when managed rendering is enabled." />
                            <x-newstech-admin::stat-card eyebrow="Clicks" title="Redirects" :value="$advertisement->clicks_count ?? 0" description="Incremented by the click redirect route when tracking is enabled." />
                        </div>
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="Preview Notes"
                        description="Managed ads render through slot listeners. Use the public site to verify placement after saving."
                    >
                        <div class="rounded-2xl border border-stone-200 bg-white p-4 text-sm leading-7 text-stone-600">
                            Slot:
                            <span class="font-semibold text-stone-950">{{ $slotOptions[old('slot_key', $advertisement->slot_key)] ?? 'Choose a slot' }}</span>
                            <br>
                            Type:
                            <span class="font-semibold text-stone-950">{{ \NewsTech\Advertisement\Models\Advertisement::typeOptions()[old('type', $advertisement->type)] ?? 'Choose a type' }}</span>
                        </div>
                    </x-newstech-admin::form.section>

                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
