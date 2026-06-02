@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Tag';
    $pageTitle ??= 'Tag Form';
    $pageDescription ??= null;
    $formId = 'tag-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech tag form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.tags.index')" tone="ghost">
                    Back to Tags
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
                <x-newstech-admin::form.section title="General">
                    <x-newstech-admin::form.input
                        name="name"
                        label="Name"
                        :value="$tag->name"
                        hint="Use a concise topical label that can be reused across many future articles."
                        placeholder="Election 2026"
                        required
                    />

                    <x-newstech-admin::form.slug-input
                        name="slug"
                        label="Slug"
                        :value="$tag->slug"
                        prefix="/tags/"
                        hint="Slug values are normalized server-side to stay URL-friendly."
                        required
                    />

                    <x-newstech-admin::form.textarea
                        name="description"
                        label="Description"
                        :value="$tag->description"
                        hint="Optional editorial note or future frontend summary for this tag."
                        placeholder="Short description for this topical tag."
                        rows="6"
                    />
                </x-newstech-admin::form.section>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Publishing Controls"
                        description="Minimal status and SEO controls so the tag module stays lightweight and reusable."
                    >
                        <x-newstech-admin::form.toggle
                            name="status"
                            label="Active status"
                            :checked="$tag->status"
                            hint="Inactive tags remain stored but can be excluded from future publishing workflows."
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section title="SEO">
                        <x-newstech-admin::form.input
                            name="meta_title"
                            label="Meta Title"
                            :value="$tag->meta_title"
                            hint="Optional SEO title override for future frontend tag pages."
                            placeholder="Election 2026 News Tag"
                        />

                        <x-newstech-admin::form.textarea
                            name="meta_description"
                            label="Meta Description"
                            :value="$tag->meta_description"
                            hint="Optional description override for future search and social previews."
                            placeholder="Latest articles, analysis, and updates tagged with Election 2026."
                            rows="5"
                        />
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
