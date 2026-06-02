@props([
    'grid',
])

@php
    $alignmentClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];

    $primaryColumnKey = $grid->columns[0]->key ?? null;

    $mutedValues = [
        'Pending',
        'Not set',
        'Not scheduled',
        'Root',
        'Standard',
        'Unassigned',
    ];

    $actionToneClasses = [
        'primary' => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
        'neutral' => 'border-stone-200 bg-white text-stone-700 hover:bg-stone-100',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100',
    ];
@endphp

<div class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
                <tr>
                    @foreach ($grid->columns as $column)
                        <th
                            scope="col"
                            @class([
                                'px-4 py-4 text-xs font-semibold uppercase tracking-[0.3em] text-stone-500',
                                $alignmentClasses[$column->alignment] ?? $alignmentClasses['left'],
                            ])
                        >
                            {{ $column->label }}
                        </th>
                    @endforeach

                    @if ($grid->hasRowActions())
                        <th scope="col" class="px-4 py-4 text-right text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-stone-200">
                @forelse ($grid->rows as $row)
                    @php
                        $primaryRowAction = collect($grid->rowActions)
                            ->first(fn ($action): bool => ! $action->usesFormSubmission());
                    @endphp

                    <tr class="align-top">
                        @foreach ($grid->columns as $column)
                            @php
                                $value = $column->resolveValue($row);
                                $primaryActionUrl = $primaryRowAction?->resolveUrl($row);
                            @endphp

                            <td
                                @class([
                                    'px-4 py-4 text-sm leading-7 text-stone-700',
                                    $alignmentClasses[$column->alignment] ?? $alignmentClasses['left'],
                                ])
                            >
                                @if ($column->type === 'badge')
                                    <x-newstech-admin::datagrid.badge :tone="$column->resolveTone($row)">
                                        {{ $value }}
                                    </x-newstech-admin::datagrid.badge>
                                @elseif ($column->key === $primaryColumnKey && $primaryActionUrl)
                                    <a
                                        href="{{ $primaryActionUrl }}"
                                        @class([
                                            'font-semibold text-stone-950 transition hover:text-amber-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-300/60 focus-visible:ring-offset-2 focus-visible:ring-offset-white',
                                            'text-stone-400' => in_array($value, $mutedValues, true),
                                        ])
                                    >
                                        {{ $value }}
                                    </a>
                                @else
                                    <span @class([
                                        'font-semibold text-stone-950' => $column->key === $primaryColumnKey,
                                        'text-stone-400' => in_array($value, $mutedValues, true),
                                    ])>
                                        {{ $value }}
                                    </span>
                                @endif
                            </td>
                        @endforeach

                        @if ($grid->hasRowActions())
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap justify-end gap-2">
                                    @foreach ($grid->rowActions as $action)
                                        @if ($action->usesFormSubmission())
                                            <form method="POST" action="{{ $action->resolveUrl($row) }}">
                                                @csrf
                                                @method($action->method)

                                                <button
                                                    type="submit"
                                                    @class([
                                                        'inline-flex items-center rounded-full border px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] transition',
                                                        $actionToneClasses[$action->tone] ?? $actionToneClasses['neutral'],
                                                    ])
                                                >
                                                    {{ $action->label }}
                                                </button>
                                            </form>
                                        @else
                                            <a
                                                href="{{ $action->resolveUrl($row) }}"
                                                @class([
                                                        'inline-flex items-center rounded-full border px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] transition',
                                                        $actionToneClasses[$action->tone] ?? $actionToneClasses['neutral'],
                                                    ])
                                            >
                                                {{ $action->label }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($grid->columns) + ($grid->hasRowActions() ? 1 : 0) }}" class="px-6 py-12 text-center">
                            <div class="space-y-2">
                                <p class="text-lg font-semibold text-stone-950">{{ $grid->emptyStateTitle }}</p>
                                <p class="mx-auto max-w-2xl text-sm leading-7 text-stone-500">{{ $grid->emptyStateDescription }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
