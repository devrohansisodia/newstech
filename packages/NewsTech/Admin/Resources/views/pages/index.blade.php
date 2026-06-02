<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Pages'"
    meta-description="Page management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Pages</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.pages.create')"
                    tone="primary"
                >
                    Add Page
                </x-newstech-admin::form.button>
            </div>

            @if (session('page_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('page_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Pages"
                :value="$pageCount"
                description="All static content pages currently stored in the admin panel."
            />

            <x-newstech-admin::stat-card
                eyebrow="Status"
                title="Active Pages"
                :value="$activePageCount"
                description="Pages currently marked as active for future public use."
            />

            <x-newstech-admin::stat-card
                eyebrow="Content"
                title="Content Ready"
                :value="$contentReadyPageCount"
                description="Pages that already contain body content beyond the basic title and slug."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
