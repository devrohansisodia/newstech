<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Tags'"
    meta-description="Tag management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Tags</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.tags.create')"
                    tone="primary"
                >
                    Add Tag
                </x-newstech-admin::form.button>
            </div>

            @if (session('tag_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('tag_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-2">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Tags"
                :value="$tagCount"
                description="All topical tags currently configured in the admin panel."
            />

            <x-newstech-admin::stat-card
                eyebrow="Status"
                title="Active Tags"
                :value="$activeTagCount"
                description="Tags currently available for future article assignment and filtering."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
