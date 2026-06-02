<?php

namespace NewsTech\Reader\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;
use NewsTech\Reader\Http\Requests\Admin\StoreReaderRequest;
use NewsTech\Reader\Http\Requests\Admin\UpdateReaderRequest;
use NewsTech\Reader\Models\Reader;
use NewsTech\Reader\Repositories\ReaderRepository;

class ReaderController
{
    public function __construct(protected ReaderRepository $readers) {}

    public function index(): View
    {
        $readers = $this->readers->orderedQuery()->get();

        $dataGrid = DataGridDefinition::make('readers', 'Readers')
            ->description('Manage frontend reader accounts without affecting admin authentication.')
            ->columns([
                ColumnDefinition::make('name', 'Reader'),
                ColumnDefinition::make('status_label', 'Status')->badge(toneMap: [
                    'Active' => 'success',
                    'Inactive' => 'warning',
                ]),
                ColumnDefinition::make('comments_count', 'Comments')->align('right'),
                ColumnDefinition::make('bookmarks_count', 'Bookmarks')->align('right'),
                ColumnDefinition::make('last_login_at', 'Last Login')->align('right'),
                ColumnDefinition::make('created_at', 'Joined')->align('right'),
            ])
            ->rows($readers->map(fn (Reader $reader): array => [
                'id' => $reader->getKey(),
                'name' => $reader->name.' · '.$reader->email,
                'status_label' => $reader->is_active ? 'Active' : 'Inactive',
                'comments_count' => (string) $reader->comments_count,
                'bookmarks_count' => (string) $reader->bookmarks_count,
                'last_login_at' => $reader->last_login_at?->format('M d, Y H:i') ?? 'Never',
                'created_at' => $reader->created_at?->format('M d, Y H:i') ?? 'Unknown',
            ])->all())
            ->rowActions([
                ActionDefinition::make('edit', 'Edit')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.readers.edit', $row['id'])),
                ActionDefinition::make('delete', 'Delete')
                    ->usingMethod('DELETE')
                    ->tone('danger')
                    ->url(fn (array $row): string => route('admin.newstech.readers.destroy', $row['id'])),
            ])
            ->emptyState(
                'No readers yet.',
                'Frontend registrations and admin-created readers will appear here.'
            );

        return view('newstech-admin::readers.index', [
            'dataGrid' => $dataGrid,
            'readerCount' => $readers->count(),
            'activeReaderCount' => $readers->where('is_active', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('newstech-admin::readers.create', [
            'reader' => new Reader([
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreReaderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->readers->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => $validated['is_active'],
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ]);

        return redirect()
            ->route('admin.newstech.readers.index')
            ->with('reader_status', 'Reader created successfully.');
    }

    public function edit(Reader $reader): View
    {
        $reader->loadCount(['comments', 'bookmarks']);

        return view('newstech-admin::readers.edit', [
            'reader' => $reader,
        ]);
    }

    public function update(UpdateReaderRequest $request, Reader $reader): RedirectResponse
    {
        $validated = $request->validated();

        $attributes = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
            'website' => $validated['website'] ?? null,
            'bio' => $validated['bio'] ?? null,
        ];

        if (filled($validated['password'] ?? null)) {
            $attributes['password'] = $validated['password'];
        }

        $this->readers->update($reader, $attributes);

        return redirect()
            ->route('admin.newstech.readers.edit', $reader)
            ->with('reader_status', 'Reader updated successfully.');
    }

    public function destroy(Reader $reader): RedirectResponse
    {
        $this->readers->delete($reader);

        return redirect()
            ->route('admin.newstech.readers.index')
            ->with('reader_status', 'Reader deleted successfully.');
    }
}
