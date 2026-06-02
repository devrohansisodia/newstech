<?php

namespace NewsTech\Core\Support\DataGrid;

class DataGridDefinition
{
    /**
     * @param  array<int, ColumnDefinition>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, ActionDefinition>  $rowActions
     * @param  array<int, BulkActionDefinition>  $bulkActions
     * @param  array<int, string>  $filters
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $description = '',
        public array $columns = [],
        public array $rows = [],
        public array $rowActions = [],
        public array $bulkActions = [],
        public ?string $searchPlaceholder = null,
        public array $filters = [],
        public string $emptyStateTitle = 'No results yet.',
        public string $emptyStateDescription = 'Rows will appear here when data becomes available.',
    ) {}

    public static function make(string $name, string $title): self
    {
        return new self($name, $title);
    }

    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param  array<int, ColumnDefinition>  $columns
     */
    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function rows(array $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    /**
     * @param  array<int, ActionDefinition>  $rowActions
     */
    public function rowActions(array $rowActions): self
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    /**
     * @param  array<int, BulkActionDefinition>  $bulkActions
     */
    public function bulkActions(array $bulkActions): self
    {
        $this->bulkActions = $bulkActions;

        return $this;
    }

    public function searchPlaceholder(?string $searchPlaceholder): self
    {
        $this->searchPlaceholder = $searchPlaceholder;

        return $this;
    }

    /**
     * @param  array<int, string>  $filters
     */
    public function filters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function emptyState(string $title, string $description): self
    {
        $this->emptyStateTitle = $title;
        $this->emptyStateDescription = $description;

        return $this;
    }

    public function hasToolbar(): bool
    {
        return $this->searchPlaceholder !== null || $this->filters !== [] || $this->bulkActions !== [];
    }

    public function hasRowActions(): bool
    {
        return $this->rowActions !== [];
    }
}
