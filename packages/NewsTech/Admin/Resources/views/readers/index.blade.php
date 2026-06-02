<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Readers'"
    meta-description="NewsTech reader account management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Readers</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.readers.create')"
                    tone="primary"
                >
                    Add Reader
                </x-newstech-admin::form.button>
            </div>

            @if (session('reader_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('reader_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-2">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Readers"
                :value="$readerCount"
                description="All frontend reader accounts currently stored in NewsTech."
            />

            <x-newstech-admin::stat-card
                eyebrow="Status"
                title="Active Readers"
                :value="$activeReaderCount"
                description="Active readers can sign in, save articles, and join moderated discussions."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
