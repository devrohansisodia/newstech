@props([
    'grid',
])

<x-newstech::panel {{ $attributes->class('space-y-6 border-stone-200 bg-stone-50/90 p-6 text-stone-700 shadow-stone-200/70') }}>
    {!! newstech_view_render_event('admin.datagrid.before', ['grid' => $grid]) !!}
    {!! newstech_view_render_event('admin.datagrid.'.$grid->name.'.before', ['grid' => $grid]) !!}
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="space-y-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-amber-600">Listing</p>
                <h3 class="mt-2 text-2xl font-black tracking-tight text-stone-950">{{ $grid->title }}</h3>
            </div>

            @if ($grid->description !== '')
                <p class="max-w-3xl text-sm leading-7 text-stone-500">
                    {{ $grid->description }}
                </p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <span class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600">
                {{ count($grid->columns) }} columns
            </span>

            <span class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm text-stone-600">
                {{ count($grid->rows) }} rows
            </span>

            @if ($grid->hasRowActions())
                <span class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-700">
                    {{ count($grid->rowActions) }} row actions
                </span>
            @endif
        </div>
    </div>

    @if ($grid->hasToolbar())
        <div class="grid gap-4 rounded-2xl border border-stone-200 bg-white p-4 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,1fr)]">
            <div class="space-y-3">
                <label for="{{ $grid->name }}-search" class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">
                    Search Placeholder
                </label>

                <input
                    id="{{ $grid->name }}-search"
                    type="search"
                    disabled
                    placeholder="{{ $grid->searchPlaceholder }}"
                    class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500 placeholder:text-stone-400"
                />

                <p class="text-xs leading-6 text-stone-400">
                    Search and filtering hooks are visible here, but real query handling is intentionally deferred to future modules.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Bulk Actions</p>

                    <select
                        disabled
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-500"
                    >
                        <option>Select an action</option>
                        @foreach ($grid->bulkActions as $bulkAction)
                            <option>{{ $bulkAction->label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Planned Filters</p>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($grid->filters as $filterLabel)
                            <span class="rounded-full border border-stone-200 bg-stone-50 px-3 py-2 text-xs font-medium text-stone-600">
                                {{ $filterLabel }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-newstech-admin::datagrid.table :grid="$grid" />
    {!! newstech_view_render_event('admin.datagrid.'.$grid->name.'.after', ['grid' => $grid]) !!}
    {!! newstech_view_render_event('admin.datagrid.after', ['grid' => $grid]) !!}
</x-newstech::panel>
