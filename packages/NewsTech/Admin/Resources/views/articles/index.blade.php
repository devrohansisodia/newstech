<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Articles'"
    meta-description="Article management."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-stone-200 bg-stone-50/90 p-8 text-stone-700 shadow-stone-200/70">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <h2 class="text-3xl font-black tracking-tight text-stone-950">Articles</h2>
                </div>

                <x-newstech-admin::form.button
                    :href="route('admin.newstech.articles.create')"
                    tone="primary"
                >
                    Add Article
                </x-newstech-admin::form.button>
            </div>

            @if (session('article_status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('article_status') }}
                </div>
            @endif
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-3">
            <x-newstech-admin::stat-card
                eyebrow="Volume"
                title="Total Articles"
                :value="$articleCount"
                description="All editorial items currently stored in the admin panel."
            />

            <x-newstech-admin::stat-card
                eyebrow="Published"
                title="Published Articles"
                :value="$publishedArticleCount"
                description="Articles that are currently marked as published."
            />

            <x-newstech-admin::stat-card
                eyebrow="Scheduled"
                title="Scheduled Articles"
                :value="$scheduledArticleCount"
                description="Articles currently queued for future publication."
            />
        </div>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
