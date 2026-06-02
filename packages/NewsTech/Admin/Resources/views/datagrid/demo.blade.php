<x-newstech-admin::layouts.app
    :title="config('newstech.admin.label').' | DataGrid Demo'"
    meta-description="NewsTech Phase 1.5 lightweight datagrid foundation for future admin listing pages."
>
    <div class="space-y-6">
        <x-newstech::panel class="space-y-5 border-white/10 bg-white/5 p-8 text-slate-100 shadow-black/20">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-sky-300">Phase 1.5</p>
            <h2 class="text-3xl font-black tracking-tight text-white">Admin table foundation is ready for future listing modules.</h2>
            <p class="max-w-3xl text-base leading-8 text-slate-300">
                This demo is intentionally static and read-only. It proves the reusable table foundation, row action structure, and placeholder toolbar patterns without introducing any real CRUD or database-backed grids yet.
            </p>
        </x-newstech::panel>

        <x-newstech-admin::datagrid :grid="$dataGrid" />
    </div>
</x-newstech-admin::layouts.app>
