<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Authors'"
    meta-description="Author management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Authors</h2>
                    <p class="max-w-3xl text-sm font-medium leading-7 text-stone-700">
                        Authors are public content bylines and reporters, not admin login users.
                    </p>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.authors.create')"
                    tone="primary"
                >
                    Add Author
                </x-newstech-admin::form.button>
            </div>

            @if (session('author_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('author_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-2">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Authors"
                :value="$authorCount"
                description="All author and reporter profiles currently configured in the admin panel."
            />

            <x-newstech-admin::stat-card
                eyebrow="Status"
                title="Active Authors"
                :value="$activeAuthorCount"
                description="Profiles currently available for future article bylines and editorial attribution."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
