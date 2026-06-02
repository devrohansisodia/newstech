<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Categories'"
    meta-description="Category management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Categories</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.categories.create')"
                    tone="primary"
                >
                    Add Category
                </x-newstech-admin::form.button>
            </div>

            @if (session('category_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('category_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Categories"
                :value="$categoryCount"
                description="All taxonomy categories currently defined in the admin panel."
            />

            <x-newstech-admin::stat-card
                eyebrow="Status"
                title="Active Categories"
                :value="$activeCategoryCount"
                description="Categories currently available for future article assignment."
            />

            <x-newstech-admin::stat-card
                eyebrow="Structure"
                title="Root Categories"
                :value="$rootCategoryCount"
                description="Top-level categories without a parent category."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
