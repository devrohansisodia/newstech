<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Edit Menu Group'"
    meta-description="NewsTech menu group editing module."
>
    <div class="space-y-6">
        @if (session('menu_status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('menu_status') }}
            </div>
        @endif

        <x-newstech-admin::page-header
            title="Edit Menu Group"
            description="Update the group details and manage the attached menu items."
        >
            <x-slot:actions>
                <x-newstech-admin::form.button :href="route('admin.newstech.menus.index')" tone="ghost">
                    Back to Menus
                </x-newstech-admin::form.button>

                <x-newstech-admin::form.button type="submit" tone="primary" form="menu-group-edit-form">
                    Update Menu Group
                </x-newstech-admin::form.button>
            </x-slot:actions>
        </x-newstech-admin::page-header>

        <form id="menu-group-edit-form" method="POST" action="{{ route('admin.newstech.menus.update', $menuGroup) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(18rem,0.9fr)]">
                <x-newstech-admin::form.section
                    title="Menu Group Basics"
                    description="Define the stored location and activation state for this frontend navigation group."
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
                        description="Keep the group stored but inactive until it is ready to replace the fallback navigation."
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

        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Menu Items</p>
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Manage items for {{ $menuGroup->name }}.</h2>
                    <p class="max-w-3xl text-base leading-8 text-stone-600">
                        Use standard forms for labels, targets, linked pages or categories, and simple numeric ordering. Drag-and-drop stays out of scope for this phase.
                    </p>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.menus.items.create', $menuGroup)"
                    tone="primary"
                >
                    Add Menu Item
                </x-newstech-admin::form.button>
            </div>
        </x-newstech::panel>

        <x-newstech-admin::datagrid :grid="$itemsGrid" />
    </div>
</x-newstech-admin::layouts.app>
