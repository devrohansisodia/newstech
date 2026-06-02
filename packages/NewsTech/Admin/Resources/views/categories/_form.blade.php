@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Category';
    $pageTitle ??= 'Category Form';
    $pageDescription ??= null;
    $formId = 'category-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech category form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.categories.index')" tone="ghost">
                    Back to Categories
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
                        :value="$category->name"
                        hint="Use a clear editorial label readers and editors will recognize."
                        placeholder="Politics"
                        required
                    />

                    <x-newstech-admin::form.slug-input
                        name="slug"
                        label="Slug"
                        :value="$category->slug"
                        prefix="/categories/"
                        hint="Slug values are normalized server-side to stay SEO-friendly."
                        required
                    />

                    <x-newstech-admin::form.select
                        name="parent_id"
                        label="Parent Category"
                        :options="$parentOptions"
                        :value="$category->parent_id"
                        placeholder="No parent category"
                        hint="Optional hierarchy for grouping broad sections and sub-sections."
                    />

                    <x-newstech-admin::form.textarea
                        name="description"
                        label="Description"
                        :value="$category->description"
                        hint="Short internal or future frontend description for this category."
                        placeholder="Explain what kind of coverage belongs in this category."
                        rows="6"
                    />
                </x-newstech-admin::form.section>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Publishing Controls"
                        description="Simple reusable controls for status, ordering, and sidebar-friendly SEO fields."
                    >
                        <x-newstech-admin::form.toggle
                            name="status"
                            label="Active status"
                            :checked="$category->status"
                            hint="Inactive categories remain stored but can be treated as unavailable for future publishing flows."
                        />

                        <x-newstech-admin::form.input
                            name="sort_order"
                            label="Sort Order"
                            type="number"
                            :value="$category->sort_order"
                            min="0"
                            hint="Lower values appear first when categories are ordered."
                        />
                    </x-newstech-admin::form.section>

                    <x-newstech-admin::form.section title="SEO">
                        <x-newstech-admin::form.input
                            name="meta_title"
                            label="Meta Title"
                            :value="$category->meta_title"
                            hint="Optional SEO title override for future frontend category pages."
                            placeholder="Latest Politics News"
                        />

                        <x-newstech-admin::form.textarea
                            name="meta_description"
                            label="Meta Description"
                            :value="$category->meta_description"
                            hint="Optional description override for search and social previews."
                            placeholder="Breaking developments, analysis, and policy updates from the politics desk."
                            rows="5"
                        />
                    </x-newstech-admin::form.section>
                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
