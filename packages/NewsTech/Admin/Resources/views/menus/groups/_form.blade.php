@php
    $method ??= 'POST';
    $submitLabel ??= 'Save Menu Group';
    $pageTitle ??= 'Menu Group Form';
    $pageDescription ??= null;
    $formId = 'menu-group-form';
@endphp

<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | '.$pageTitle"
    meta-description="NewsTech menu group form module."
>
    <div class="space-y-6">
        <x-newstech-admin::page-header :title="$pageTitle" :description="$pageDescription">
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.menus.index')" tone="ghost">
                    Back to Menus
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

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.9fr)]">
                <x-newstech-admin::form.section
                    title="Menu Group Basics"
                    description="Define a simple named menu group and choose where it should be used on the public frontend."
                >
                    <x-newstech-admin::form.input
                        name="name"
                        label="Name"
                        :value="$menuGroup->name"
                        hint="Internal admin label for this group."
                        placeholder="Primary Header Menu"
                        required
                    />

                    <x-newstech-admin::form.select
                        name="location"
                        label="Location"
                        :options="$locationOptions"
                        :value="$menuGroup->location"
                        placeholder="Choose a location"
                        hint="Header and footer are currently rendered publicly; mobile is reserved for a later phase."
                        required
                    />
                </x-newstech-admin::form.section>

                <div class="space-y-6">
                    <x-newstech-admin::form.section
                        title="Publishing Controls"
                        description="Keep the group stored but inactive until the menu is ready to replace the fallback links."
                    >
                        <x-newstech-admin::form.toggle
                            name="status"
                            label="Active status"
                            :checked="$menuGroup->status"
                            hint="Only active groups are used by the frontend menu resolver."
                        />
                    </x-newstech-admin::form.section>

                </div>
            </div>
        </form>
    </div>
</x-newstech-admin::layouts.app>
