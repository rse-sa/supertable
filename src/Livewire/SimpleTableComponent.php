<?php

namespace RSE\SuperTable\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use RSE\SuperTable\SimpleTableConfig;
use RSE\SuperTable\SimpleTableRepo;

class SimpleTableComponent extends Component
{
    use WithPagination;

    #[Locked]
    public string $orderBy = 'id';

    #[Locked]
    public string $orderWay = 'desc';

    public int $perPage = 10;

    #[Locked]
    public bool $mounted = false;

    #[Locked]
    public string $tableName;

    #[Locked]
    public array $parameters;

    /**
     * Filters picked by the user, merged into the query parameters.
     *
     * @var array<string, mixed>
     */
    public array $filters = [];

    public ?string $search = null;

    public function mount(string $tableName, array $parameters = [], array $filters = []): void
    {
        $this->tableName = $tableName;

        $this->parameters = $parameters;

        $this->filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        $config = $this->getConfig();

        $this->orderBy  = $config->getOrderBy();
        $this->orderWay = $config->getOrderWay();
    }

    public function getConfig(): SimpleTableConfig
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return SimpleTableRepo::getConfigInstance($this->tableName);
    }

    /**
     * Server-side parameters always win over what the browser sends.
     *
     * @return array<string, mixed>
     */
    protected function queryParameters(): array
    {
        return array_merge(
            $this->filters,
            $this->parameters,
            $this->search !== null ? ['q' => $this->search] : [],
        );
    }

    protected function prepareQuery(): Builder|Relation
    {
        return $this->getConfig()->getQuery($this->queryParameters());
    }

    #[On('simple-table-filter')]
    public function applyFilter(string $table, string $key, mixed $value = null): void
    {
        if ($table !== $this->tableName) {
            return;
        }

        if ($value === null || $value === '') {
            unset($this->filters[$key]);
        } else {
            $this->filters[$key] = $value;
        }

        $this->filtersChanged();
    }

    #[On('simple-table-clear-filters')]
    public function clearFilters(string $table): void
    {
        if ($table !== $this->tableName) {
            return;
        }

        $this->filters = [];

        $this->filtersChanged();
    }

    #[On('simple-table-search')]
    public function applySearch(string $table, ?string $q = null): void
    {
        if ($table !== $this->tableName) {
            return;
        }

        $this->search = $q;

        $this->resetPage($this->getConfig()->pageName);
    }

    protected function filtersChanged(): void
    {
        $this->resetPage($this->getConfig()->pageName);

        $this->dispatch('simple-table-filters', table: $this->tableName, filters: $this->filters);
    }

    public function results(): Collection|LengthAwarePaginator
    {
        return $this->prepareQuery()
            ->reorder($this->orderBy, $this->orderWay)
            ->paginate($this->perPage, ['*'], $this->getConfig()->pageName);
    }

    public function paginationView(): string
    {
        return 'twc::components.tailwind-paginator';
    }

    public function export(string $exportKey): mixed
    {
        $export = $this->getConfig()->getExport($exportKey);

        abort_unless($export, 404);

        $query = $this->prepareQuery()->reorder($this->orderBy, $this->orderWay);

        return $export->run($query, [
            'tableName' => $this->tableName,
        ]);
    }

    public function render(): View
    {
        $render = view('supertable::livewire.simple-table', [
            'config'  => $this->getConfig(),
            'results' => $this->results(),
        ]);

        $this->mounted = true;

        return $render;
    }
}
