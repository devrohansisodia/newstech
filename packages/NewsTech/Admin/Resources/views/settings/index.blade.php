<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | Settings'"
    meta-description="Settings."
>
    <div class="space-y-6">
        @if (session('page_status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('page_status') }}
            </div>
        @endif

        <x-newstech::panel class="space-y-5 border-stone-200 bg-white p-8 text-stone-700 shadow-stone-200/60">
            <h2 class="text-3xl font-black tracking-tight text-stone-950">Settings</h2>
        </x-newstech::panel>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <x-newstech-admin::stat-card
                eyebrow="Architecture"
                title="Group Registry"
                :value="count($groups)"
                description="Available settings groups."
            />

            <x-newstech-admin::stat-card
                eyebrow="Editing"
                title="Focused Pages"
                value="One group at a time"
                description="Each group opens on its own page."
            />

            <x-newstech-admin::stat-card
                eyebrow="Storage"
                title="Persistence"
                value="Database-backed"
                description="Settings are stored in the database."
            />
        </div>

        {!! newstech_view_render_event('admin.settings.index.cards.before', ['groups' => $groups]) !!}

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($groups as $group)
                <x-newstech-admin::settings-group-card :group="$group" />
            @endforeach
        </div>

        {!! newstech_view_render_event('admin.settings.index.cards.after', ['groups' => $groups]) !!}
    </div>
</x-newstech-admin::layouts.app>
