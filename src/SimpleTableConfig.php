<?php

/** @noinspection PhpUnused */

namespace RSE\SuperTable;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use RSE\SuperTable\Exports\TableExport;

class SimpleTableConfig
{
    public ?string $viewName = null;

    public ?string $title = null;

    public ?string $icon = null;

    public ?Closure $query = null;

    public string $orderBy = 'id';

    public string $orderWay = 'desc';

    public string $pageName = 'page';

    /** @var array<string, TableExport> */
    public array $exports = [];

    public static function make(): self
    {
        return new self;
    }

    public function setViewName(string|View $viewName): self
    {
        if ($viewName instanceof View) {
            $viewName = $viewName->name();
        }

        $this->viewName = $viewName;

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

    public function setQuery(Closure $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function setOrderWay(string $orderWay): self
    {
        $this->orderWay = $orderWay;

        return $this;
    }

    public function setPageName(string $pageName): self
    {
        $this->pageName = $pageName;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getQuery(array $parameters): Builder|Relation|null
    {
        return ($this->query)($parameters);
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    public function getOrderWay(): string
    {
        return $this->orderWay;
    }

    public function getViewName(): string
    {
        return $this->viewName;
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
}
