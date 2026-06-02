<?php

namespace NewsTech\Tag\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;
use NewsTech\Tag\Http\Requests\StoreTagRequest;
use NewsTech\Tag\Http\Requests\UpdateTagRequest;
use NewsTech\Tag\Models\Tag;
use NewsTech\Tag\Repositories\TagRepository;

class TagController
{
    public function __construct(protected TagRepository $tags) {}

    public function index(): View
    {
        $tags = $this->tags->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('tags', 'Tags')
            ->description('News tags now reuse the same admin datagrid and form foundations used by categories, while keeping the module lightweight and editorially focused.')
            ->columns([
                ColumnDefinition::make('name', 'Name')->sortable(),
                ColumnDefinition::make('slug', 'Slug'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('updated_at', 'Updated')->align('right')->sortable(),
            ])
            ->rows($tags->map(fn (Tag $tag): array => [
                'id' => $tag->getKey(),
                'name' => $tag->name,
                'slug' => $tag->slug,
                'status_label' => $tag->statusLabel(),
                'updated_at' => $tag->updated_at?->format('M d, Y'),
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.tags.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.tags.destroy', $row['id'])),
            ])
            ->emptyState(
                'No tags yet.',
                'Create the first topical tag so upcoming articles can support finer-grained discovery and search.'
            );

        return view('newstech-admin::tags.index', [
            'dataGrid' => $dataGrid,
            'tagCount' => $tags->count(),
            'activeTagCount' => $tags->where('status', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::tags.create', [
            'tag' => new Tag([
                'status' => true,
            ]),
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->tags->create($request->validated());

        return redirect()
            ->route('admin.newstech.tags.index')
            ->with('tag_status', 'Tag created successfully.');
    }

    public function edit(int|string $tag): View
    {
        /** @var Tag $tag */
        $tag = $this->tags->findOrFail($tag);

        return view('newstech-admin::tags.edit', [
            'tag' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, int|string $tag): RedirectResponse
    {
        /** @var Tag $tag */
        $tag = $this->tags->findOrFail($tag);

        $this->tags->update($tag, $request->validated());

        return redirect()
            ->route('admin.newstech.tags.index')
            ->with('tag_status', 'Tag updated successfully.');
    }

    public function destroy(int|string $tag): RedirectResponse
    {
        /** @var Tag $tag */
        $tag = $this->tags->findOrFail($tag);

        $tag->delete();

        return redirect()
            ->route('admin.newstech.tags.index')
            ->with('tag_status', 'Tag deleted successfully.');
    }
}
