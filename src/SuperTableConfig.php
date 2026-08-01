<?php

/** @noinspection PhpUnused */

namespace RSE\SuperTable;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use RSE\SuperTable\Exports\TableExport;

class SuperTableConfig
{
    public array $columns = [];

    public array $filters = [];

    /** @var array<string, TableExport> */
    public array $exports = [];

    protected int $filtersPerRow = 4;

    public ?string $title = null;

    public ?string $icon = null;

    public EloquentBuilder|QueryBuilder|Closure|null $query = null;

    public bool $globalSearchBox = true;

    public bool $withoutHeaderBar = false;

    public bool $savedFilters = false;

    public ?Closure $searchCallback = null;

    public string $orderBy = 'id';

    public string $orderWay = 'desc';

    public static function make(): self
    {
        return new self;
    }

    public function setColumns(array $columns): self
    {
        $this->columns = array_filter($columns);

        return $this;
    }

    public function setFilters(array $filters): self
    {
        $this->filters = array_filter($filters);

        return $this;
    }

    public function addExport(TableExport $export): self
    {
        $this->exports[$export->getKey()] = $export;

        return $this;
    }

    public function setExports(array $exports): self
    {
        foreach ($exports as $export) {
            $this->addExport($export);
        }

        return $this;
    }

    public function setFiltersPerRow(int $perRow): self
    {
        $this->filtersPerRow = $perRow;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function setQuery(EloquentBuilder|QueryBuilder|Closure|null $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function setSearchCallback(?Closure $searchCallback): self
    {
        $this->searchCallback = $searchCallback;

        return $this;
    }

    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function withSavedFilters(bool $enabled = true): self
    {
        $this->savedFilters = $enabled;

        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    /** @return array<string, TableExport> */
    public function getExports(): array
    {
        return $this->exports;
    }

    public function getExport(string $key): ?TableExport
    {
        return $this->exports[$key] ?? null;
    }

    public function hasExports(): bool
    {
        return $this->exports !== [];
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getQuery(): EloquentBuilder|QueryBuilder|null
    {
        if ($this->query instanceof Closure) {
            return ($this->query)();
        }

        return $this->query;
    }

    public function getSearchCallback(): ?Closure
    {
        return $this->searchCallback;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function getOrderWay(): string
    {
        return $this->orderWay;
    }

    public function setOrderWay(string $orderWay): self
    {
        $this->orderWay = $orderWay;

        return $this;
    }

    public function getFiltersPerRow(): int
    {
        return $this->filtersPerRow;
    }

    public function hasGlobalSearch(): bool
    {
        return $this->globalSearchBox;
    }

    public function withoutHeaderBar(): bool
    {
        return $this->withoutHeaderBar;
    }

    public function hasSavedFilters(): bool
    {
        return $this->savedFilters;
    }

    public function withGlobalSearch(bool $globalSearchBox): self
    {
        $this->globalSearchBox = $globalSearchBox;

        return $this;
    }

    public function hideHeaderBar(bool $withoutHeaderBar): self
    {
        $this->withoutHeaderBar = $withoutHeaderBar;

        return $this;
    }
}
