<?php

namespace NewsTech\Advertisement\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Advertisement\Http\Requests\StoreAdvertisementRequest;
use NewsTech\Advertisement\Http\Requests\UpdateAdvertisementRequest;
use NewsTech\Advertisement\Models\Advertisement;
use NewsTech\Advertisement\Repositories\AdvertisementRepository;
use NewsTech\Advertisement\Support\AdvertisementRenderer;
use NewsTech\Advertisement\Support\AdvertisementSlotManager;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;

class AdvertisementController
{
    public function __construct(
        protected AdvertisementRepository $advertisements,
        protected AdvertisementSlotManager $slots,
        protected AdvertisementRenderer $renderer,
    ) {}

    public function index(): View
    {
        $advertisements = $this->advertisements->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('advertisements', 'Advertisements')
            ->description('Manage slot-based advertisements that render through NewsTech render events without editing frontend blades.')
            ->columns([
                ColumnDefinition::make('name', 'Name')->sortable(),
                ColumnDefinition::make('slot_label', 'Slot'),
                ColumnDefinition::make('type_label', 'Type'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('impressions_count', 'Impressions')->align('right'),
                ColumnDefinition::make('clicks_count', 'Clicks')->align('right'),
                ColumnDefinition::make('priority', 'Priority')->align('right'),
            ])
            ->rows($advertisements->map(function (Advertisement $advertisement): array {
                return [
                    'id' => $advertisement->getKey(),
                    'name' => $advertisement->name,
                    'slot_label' => $this->slots->find($advertisement->slot_key)['label'] ?? $advertisement->slot_key,
                    'type_label' => $advertisement->typeLabel(),
                    'status_label' => $advertisement->statusLabel(),
                    'impressions_count' => (string) $advertisement->impressions_count,
                    'clicks_count' => (string) $advertisement->clicks_count,
                    'priority' => (string) $advertisement->priority,
                ];
            })->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.advertisements.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.advertisements.destroy', $row['id'])),
            ])
            ->emptyState(
                'No advertisements yet.',
                'Create the first managed advertisement to replace slot placeholders with real campaign output.'
            );

        return view('newstech-admin::advertisements.index', [
            'dataGrid' => $dataGrid,
            'advertisementCount' => $advertisements->count(),
            'activeAdvertisementCount' => $advertisements->where('status', Advertisement::STATUS_ACTIVE)->count(),
            'totalImpressions' => $advertisements->sum('impressions_count'),
            'totalClicks' => $advertisements->sum('clicks_count'),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::advertisements.create', [
            'advertisement' => new Advertisement([
                'type' => Advertisement::TYPE_IMAGE,
                'status' => Advertisement::STATUS_ACTIVE,
                'open_in_new_tab' => (bool) config('newstech-advertisement.default_open_in_new_tab', true),
                'nofollow' => (bool) config('newstech-advertisement.default_nofollow', false),
                'sponsored' => (bool) config('newstech-advertisement.default_sponsored', true),
                'priority' => 0,
                'slot_key' => 'homepage_top',
            ]),
            'slotOptions' => $this->slots->options(),
        ]);
    }

    public function store(StoreAdvertisementRequest $request): RedirectResponse
    {
        /** @var Advertisement $advertisement */
        $advertisement = $this->advertisements->create([
            ...$request->validated(),
            'created_by' => auth('admin')->id(),
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('admin.newstech.advertisements.edit', $advertisement)
            ->with('advertisement_status', 'Advertisement created successfully.');
    }

    public function edit(Advertisement $advertisement): View
    {
        return view('newstech-admin::advertisements.edit', [
            'advertisement' => $advertisement,
            'slotOptions' => $this->slots->options(),
            'previewHtml' => $this->renderer->renderSlot($advertisement->slot_key)->toHtml(),
        ]);
    }

    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $this->advertisements->update($advertisement, [
            ...$request->validated(),
            'updated_by' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('admin.newstech.advertisements.edit', $advertisement)
            ->with('advertisement_status', 'Advertisement updated successfully.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        $this->advertisements->delete($advertisement);

        return redirect()
            ->route('admin.newstech.advertisements.index')
            ->with('advertisement_status', 'Advertisement deleted successfully.');
    }
}
