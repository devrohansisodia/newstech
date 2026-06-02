@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Page';
    $pageTitle ??= 'Page Form';
    $pageDescription ??= null;
    $formId = 'page-form';
    $seoScorePanelConfig = [
        'type' => 'page',
        'analyzeUrl' => route('admin.newstech.seo.analyze'),
        'csrfToken' => csrf_token(),
        'enabled' => (bool) config('newstech-seo.enable_real_time_checks'),
        'showSocialPreview' => (bool) config('newstech-seo.enable_social_preview'),
        'scoreThresholdWarning' => (int) config('newstech-seo.score_threshold_warning'),
    ];
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech page form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.pages.index')" tone="ghost">
                    Back to Pages
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

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(18rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="General"
                    >
                        <x-newstech-admin::form.input
                            name="title"
                            label="Title"
                            :value="$page->title"
                            hint="Use a clear page title such as About, Contact, Privacy Policy, or Advertise."
                            placeholder="About NewsTech"
                            data-seo-source="title"
                            required
                        />

                        <x-newstech-admin::form.slug-input
                            name="slug"
                            label="Slug"
                            :value="$page->slug"
                            prefix="/pages/"
                            hint="Slug values are normalized server-side to stay consistent and SEO-friendly."
                            data-seo-source="slug"
                            required
                        />

                        <x-newstech-admin::form.rich-text-editor
                            name="content"
                            label="Content"
                            :value="$page->content"
                            hint="Use the shared Tiptap editor for page body copy."
                            placeholder="Static page content goes here."
                            data-seo-source="content"
                            rows="16"
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section
                        title="SEO"
                        description="Metadata and real-time preview checks for the public page."
                    >
                        <x-newstech-admin::form.input
                            name="meta_title"
                            label="Meta Title"
                            :value="$page->meta_title"
                            hint="Optional SEO title override for the public page route."
                            placeholder="About NewsTech | NewsTech"
                            data-seo-source="meta_title"
                        />

                        <x-newstech-admin::form.textarea
                            name="meta_description"
                            label="Meta Description"
                            :value="$page->meta_description"
                            hint="Optional description override for search and social previews."
                            placeholder="Learn about the NewsTech editorial platform, newsroom direction, and publishing approach."
                            data-seo-source="meta_description"
                            rows="5"
                        />

                        <x-newstech-admin::form.input
                            name="focus_keyword"
                            label="Focus Keyword"
                            :value="$page->focus_keyword"
                            hint="Optional target phrase used by the real-time SEO panel to review keyword placement."
                            placeholder="NewsTech editorial platform"
                            data-seo-source="focus_keyword"
                        />

                        <div
                            data-seo-score-panel-root="true"
                            data-vue-mount-status="pending"
                        >
                            <script type="application/json" data-seo-score-panel-config>
                                {!! json_encode($seoScorePanelConfig, JSON_THROW_ON_ERROR) !!}
                            </script>
                        </div>
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Publishing Controls"
                        description="Simple visibility control for this page."
                    >
                        <x-newstech-admin::form.toggle
                            name="status"
                            label="Active status"
                            :checked="$page->status"
                            hint="Inactive pages remain stored but can stay hidden from future public integration."
                            data-seo-source="status"
                        />
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
