<?php

namespace NewsTech\Admin\Http\Controllers;

use Illuminate\Contracts\View\View;
use NewsTech\Core\Support\DataGrid\ActionDefinition;
use NewsTech\Core\Support\DataGrid\BulkActionDefinition;
use NewsTech\Core\Support\DataGrid\ColumnDefinition;
use NewsTech\Core\Support\DataGrid\DataGridDefinition;

class DataGridDemoController
{
    public function index(): View
    {
        $dataGrid = DataGridDefinition::make('editorial-demo', 'Reusable DataGrid Demo')
            ->description('This read-only example keeps the foundation lightweight while establishing reusable listing patterns for future admin modules.')
            ->searchPlaceholder('Search headlines, sections, or authors')
            ->filters([
                'Status: Published',
                'Section: Politics',
                'Visibility: Admin Demo',
            ])
            ->columns([
                ColumnDefinition::make('headline', 'Headline')->searchable()->sortable(),
                ColumnDefinition::make('section', 'Section')->searchable(),
                ColumnDefinition::make('status', 'Status')->badge(toneMap: [
                    'Published' => 'success',
                    'Scheduled' => 'warning',
                    'Draft' => 'neutral',
                ]),
                ColumnDefinition::make('author', 'Author'),
                ColumnDefinition::make('published_at', 'Published')->align('right')->sortable(),
            ])
            ->rows([
                [
                    'id' => 101,
                    'headline' => 'City Council Approves Late Budget After Midnight Session',
                    'section' => 'Politics',
                    'status' => 'Published',
                    'author' => 'Anika Sharma',
                    'published_at' => 'May 10, 2026',
                ],
                [
                    'id' => 102,
                    'headline' => 'Monsoon Readiness Audit Expands to Coastal Districts',
                    'section' => 'Infrastructure',
                    'status' => 'Scheduled',
                    'author' => 'Rohan Mehta',
                    'published_at' => 'May 12, 2026',
                ],
                [
                    'id' => 103,
                    'headline' => 'Campus Startup Grants Draw Record Weekend Applications',
                    'section' => 'Education',
                    'status' => 'Draft',
                    'author' => 'Sara Khan',
                    'published_at' => 'Pending',
                ],
            ])
            ->rowActions([
                ActionDefinition::make('preview', 'Preview')
                    ->tone('primary')
                    ->url(fn (array $row): string => route('admin.newstech.foundation.datagrid-demo.index', ['preview' => $row['id']])),
                ActionDefinition::make('duplicate', 'Duplicate')
                    ->tone('neutral')
                    ->url(fn (array $row): string => route('admin.newstech.foundation.datagrid-demo.index', ['duplicate' => $row['id']])),
            ])
            ->bulkActions([
                BulkActionDefinition::make('archive', 'Move to archive'),
                BulkActionDefinition::make('export', 'Export selection'),
            ])
            ->emptyState(
                'No demo records available.',
                'Static sample rows are used in this phase so future modules can plug real data into the same table foundation later.'
            );

        return view('newstech-admin::datagrid.demo', [
            'dataGrid' => $dataGrid,
        ]);
    }
}
