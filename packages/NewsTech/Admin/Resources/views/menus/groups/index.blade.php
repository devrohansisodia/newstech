<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Menus'"
    meta-description="Menu management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Menus</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.menus.create')"
                    tone="primary"
                >
                    Add Menu Group
                </x-newstech-admin::form.button>
            </div>

            @if (session('menu_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('menu_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Menu Groups"
                :value="$menuGroupCount"
                description="All stored navigation groups currently available in admin."
            />

            <x-newstech-admin::stat-card
                eyebrow="Status"
                title="Active Groups"
                :value="$activeMenuGroupCount"
                description="Active groups available for public header, footer, or future mobile rendering."
            />

            <x-newstech-admin::stat-card
                eyebrow="Header"
                title="Header Groups"
                :value="$headerMenuGroupCount"
                description="Groups currently assigned to the header location."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
