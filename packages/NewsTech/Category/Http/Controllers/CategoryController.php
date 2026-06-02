<?php

namespace NewsTech\Category\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NewsTech\Category\Http\Requests\StoreCategoryRequest;
use NewsTech\Category\Http\Requests\UpdateCategoryRequest;
use NewsTech\Category\Models\Category;
use NewsTech\Category\Repositories\CategoryRepository;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;

class CategoryController
{
    public function __construct(protected CategoryRepository $categories) {}

    public function index(Request $request): View
    {
        $categories = $this->categories->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('categories', 'Categories')
            ->description('News categories are now managed through the reusable admin table and form foundation built in earlier phases.')
            ->columns([
                ColumnDefinition::make('name', 'Name')->sortable(),
                ColumnDefinition::make('parent', 'Parent'),
                ColumnDefinition::make('slug', 'Slug'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('sort_order', 'Sort Order')->align('right')->sortable(),
                ColumnDefinition::make('updated_at', 'Updated')->align('right')->sortable(),
            ])
            ->rows($categories->map(fn (Category $category): array => [
                'id' => $category->getKey(),
                'name' => $category->name,
                'parent' => $category->parent?->name ?? 'Root',
                'slug' => $category->slug,
                'status_label' => $category->statusLabel(),
                'sort_order' => $category->sort_order,
                'updated_at' => $category->updated_at?->format('M d, Y'),
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.categories.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.categories.destroy', $row['id'])),
            ])
            ->emptyState(
                'No categories yet.',
                'Create the first taxonomy category to start organizing upcoming news coverage.'
            );

        return view('newstech-admin::categories.index', [
            'dataGrid' => $dataGrid,
            'categoryCount' => $categories->count(),
            'activeCategoryCount' => $categories->where('status', true)->count(),
            'rootCategoryCount' => $categories->whereNull('parent_id')->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::categories.create', [
            'category' => new Category([
                'status' => true,
                'sort_order' => $this->categories->nextSortOrder(),
            ]),
            'parentOptions' => $this->categories->parentOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categories->create($request->validated());

        return redirect()
            ->route('admin.newstech.categories.index')
            ->with('category_status', 'Category created successfully.');
    }

    public function edit(int|string $category): View
    {
        /** @var Category $category */
        $category = $this->categories->findOrFail($category);

        return view('newstech-admin::categories.edit', [
            'category' => $category,
            'parentOptions' => $this->categories->parentOptions((int) $category->getKey()),
        ]);
    }

    public function update(UpdateCategoryRequest $request, int|string $category): RedirectResponse
    {
        /** @var Category $category */
        $category = $this->categories->findOrFail($category);

        $this->categories->update($category, $request->validated());

        return redirect()
            ->route('admin.newstech.categories.index')
            ->with('category_status', 'Category updated successfully.');
    }

    public function destroy(int|string $category): RedirectResponse
    {
        /** @var Category $category */
        $category = $this->categories->findOrFail($category);

        $category->delete();

        return redirect()
            ->route('admin.newstech.categories.index')
            ->with('category_status', 'Category deleted successfully.');
    }
}
