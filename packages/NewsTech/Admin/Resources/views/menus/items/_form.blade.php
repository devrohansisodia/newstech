@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Menu Item';
    $pageTitle ??= 'Menu Item Form';
    $pageDescription ??= null;
    $formId = 'menu-item-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech menu item form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.menus.edit', $menuGroup)" tone="ghost">
                    Back to Menu Group
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

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.9fr)]">
                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Menu Item Basics"
                        description="Choose the item label, item type, and linked target data for this stored frontend navigation entry."
                    >
                        <x-newstech-admin::form.input
                            name="label"
                            label="Label"
                            :value="$menuItem->label"
                            hint="Public label rendered in the frontend navigation."
                            placeholder="About"
                            required
                        />

                        <x-newstech-admin::form.select
                            name="type"
                            label="Type"
                            :options="$typeOptions"
                            :value="$menuItem->type"
                            placeholder="Choose an item type"
                            hint="Use a custom URL, a stored page, or an active category."
                            required
                        />

                        <x-newstech-admin::form.input
                            name="url"
                            label="Custom URL"
                            :value="$menuItem->url"
                            hint="Only required for custom URL items. Internal URLs like /contact are allowed."
                            placeholder="/contact"
                        />

                        <x-newstech-admin::form.select
                            name="page_id"
                            label="Page"
                            :options="$pageOptions"
                            :value="$menuItem->page_id"
                            placeholder="Choose a page"
                            hint="Used when the item type is Page."
                        />

                        <x-newstech-admin::form.select
                            name="category_id"
                            label="Category"
                            :options="$categoryOptions"
                            :value="$menuItem->category_id"
                            placeholder="Choose a category"
                            hint="Used when the item type is Category."
                        />
                    </x-newstech-admin::form.section>
                </div>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Structure & Publishing"
                        description="Use simple parent selection and numeric ordering until a richer menu builder UI is approved."
                    >
                        <x-newstech-admin::form.select
                            name="parent_id"
                            label="Parent Item"
                            :options="$parentOptions"
                            :value="$menuItem->parent_id"
                            placeholder="No parent item"
                            hint="Optional simple nesting within this same menu group."
                        />

                        <x-newstech-admin::form.input
                            name="sort_order"
                            label="Sort Order"
                            type="number"
                            :value="$menuItem->sort_order"
                            min="0"
                            hint="Lower values appear first."
                        />

                        <x-newstech-admin::form.toggle
                            name="status"
                            label="Active status"
                            :checked="$menuItem->status"
                            hint="Inactive items remain stored but are hidden from the public resolver."
                        />

                        <x-newstech-admin::form.toggle
                            name="opens_in_new_tab"
                            label="Open in new tab"
                            :checked="$menuItem->opens_in_new_tab"
                            hint="Adds target=_blank for this item on the public frontend."
                        />
                    </x-newstech-admin::form.section>

                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
