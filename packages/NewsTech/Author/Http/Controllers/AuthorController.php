<?php

namespace NewsTech\Author\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Author\Http\Requests\StoreAuthorRequest;
use NewsTech\Author\Http\Requests\UpdateAuthorRequest;
use NewsTech\Author\Models\Author;
use NewsTech\Author\Repositories\AuthorRepository;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;

class AuthorController
{
    public function __construct(protected AuthorRepository $authors) {}

    public function index(): View
    {
        $authors = $this->authors->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('authors', 'Authors')
            ->description('News authors now use the same reusable admin table and form foundations as categories and tags, while keeping reporter profile data lightweight and editable.')
            ->columns([
                ColumnDefinition::make('name', 'Name')->sortable(),
                ColumnDefinition::make('slug', 'Slug'),
                ColumnDefinition::make('designation', 'Designation'),
                ColumnDefinition::make('email', 'Email'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'neutral',
                ]),
                ColumnDefinition::make('updated_at', 'Updated')->align('right')->sortable(),
            ])
            ->rows($authors->map(fn (Author $author): array => [
                'id' => $author->getKey(),
                'name' => $author->name,
                'slug' => $author->slug,
                'designation' => $author->designation ?: 'Not set',
                'email' => $author->email ?: 'Not set',
                'status_label' => $author->statusLabel(),
                'updated_at' => $author->updated_at?->format('M d, Y'),
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.authors.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.authors.destroy', $row['id'])),
            ])
            ->emptyState(
                'No authors yet.',
                'Create the first reporter profile so upcoming article modules can attach real bylines and editorial bios.'
            );

        return view('newstech-admin::authors.index', [
            'dataGrid' => $dataGrid,
            'authorCount' => $authors->count(),
            'activeAuthorCount' => $authors->where('status', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::authors.create', [
            'author' => new Author([
                'status' => true,
            ]),
        ]);
    }

    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        $this->authors->create($request->validated());

        return redirect()
            ->route('admin.newstech.authors.index')
            ->with('author_status', 'Author created successfully.');
    }

    public function edit(int|string $author): View
    {
        /** @var Author $author */
        $author = $this->authors->findOrFail($author);

        return view('newstech-admin::authors.edit', [
            'author' => $author,
        ]);
    }

    public function update(UpdateAuthorRequest $request, int|string $author): RedirectResponse
    {
        /** @var Author $author */
        $author = $this->authors->findOrFail($author);

        $this->authors->update($author, $request->validated());

        return redirect()
            ->route('admin.newstech.authors.index')
            ->with('author_status', 'Author updated successfully.');
    }

    public function destroy(int|string $author): RedirectResponse
    {
        /** @var Author $author */
        $author = $this->authors->findOrFail($author);

        $author->delete();

        return redirect()
            ->route('admin.newstech.authors.index')
            ->with('author_status', 'Author deleted successfully.');
    }
}
