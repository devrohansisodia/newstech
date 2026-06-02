<?php

namespace NewsTech\Page\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;
use NewsTech\Page\Http\Requests\StorePageRequest;
use NewsTech\Page\Http\Requests\UpdatePageRequest;
use NewsTech\Page\Models\Page;
use NewsTech\Page\Repositories\PageRepository;

class PageController
{
    public function __construct(protected PageRepository $pages) {}

    public function index(): View
    {
        $pages = $this->pages->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('pages', 'Pages')
            ->description('Static content pages now use the shared admin datagrid and form foundations without replacing the current hardcoded frontend routes yet.')
            ->columns([
                ColumnDefinition::make('title', 'Title')->sortable(),
                ColumnDefinition::make('slug', 'Slug'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('content_status', 'Content'),
                ColumnDefinition::make('updated_at', 'Updated')->align('right')->sortable(),
            ])
            ->rows($pages->map(fn (Page $page): array => [
                'id' => $page->getKey(),
                'title' => $page->title,
                'slug' => $page->slug,
                'status_label' => $page->statusLabel(),
                'content_status' => filled($page->content) ? 'Content added' : 'Empty draft',
                'updated_at' => $page->updated_at?->format('M d, Y'),
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.pages.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.pages.destroy', $row['id'])),
            ])
            ->emptyState(
                'No pages yet.',
                'Create the first static content page for future frontend database-driven page integration.'
            );

        return view('newstech-admin::pages.index', [
            'dataGrid' => $dataGrid,
            'pageCount' => $pages->count(),
            'activePageCount' => $pages->where('status', true)->count(),
            'contentReadyPageCount' => $pages->filter(fn (Page $page): bool => filled($page->content))->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::pages.create', [
            'page' => new Page([
                'status' => true,
            ]),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $this->pages->create($request->validated());

        return redirect()
            ->route('admin.newstech.pages.index')
            ->with('page_status', 'Page created successfully.');
    }

    public function edit(int|string $page): View
    {
        /** @var Page $page */
        $page = $this->pages->findOrFail($page);

        return view('newstech-admin::pages.edit', [
            'page' => $page,
        ]);
    }

    public function update(UpdatePageRequest $request, int|string $page): RedirectResponse
    {
        /** @var Page $page */
        $page = $this->pages->findOrFail($page);

        $this->pages->update($page, $request->validated());

        return redirect()
            ->route('admin.newstech.pages.index')
            ->with('page_status', 'Page updated successfully.');
    }

    public function destroy(int|string $page): RedirectResponse
    {
        /** @var Page $page */
        $page = $this->pages->findOrFail($page);

        $page->delete();

        return redirect()
            ->route('admin.newstech.pages.index')
            ->with('page_status', 'Page deleted successfully.');
    }
}
