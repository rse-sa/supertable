<?php

namespace RSE\SuperTable\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RSE\SuperTable\Contracts\CurrentUserResolver;
use RSE\SuperTable\Contracts\PerPageStore;
use RSE\SuperTable\Models\SuperTableFilter;
use RSE\SuperTable\SuperTableConfig;
use RSE\SuperTable\SuperTableRepo;
use stdClass;

class SuperTableComponent extends Component
{
    use WithPagination;

    #[Url]
    public string $orderBy = 'id';

    #[Url]
    public string $orderWay = 'desc';

    #[Url]
    public int $perPage = 10;

    protected bool $collapsible = false;

    public string $searchText = '';

    #[Locked]
    public bool $mounted = false;

    #[Locked]
    public bool $hasFilters = false;

    public array $filters = [];

    #[Locked]
    public string $tableName;

    public bool $showSaveFilterModal = false;

    public string $filterName = '';

    public ?int $selectedFilterId = null;

    protected $listeners = ['searchTextUpdated'];

    public function mount(string $tableName): void
    {
        $this->tableName = $tableName;

        if (request()->has('q')) {
            $this->searchText = request()->query('q') ?? '';
        }

        $config = $this->getConfig();

        $this->sortBy(
            request('orderBy') ?? $config->orderBy ?? 'id',
            request('orderWay') ?? $config->orderWay ?? 'desc',
        );

        $this->perPage = app(PerPageStore::class)->get(
            $this->getPerpageKey(),
            10,
            app(CurrentUserResolver::class)->resolve()
        );

        foreach ($this->filters() as $filter) {
            $this->filters[$filter->getKey()] = $filter->getValue();
        }
    }

    public function getConfig(): SuperTableConfig
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return SuperTableRepo::getConfigInstance($this->tableName);
    }

    public function columns(): array
    {
        return $this->getConfig()->getColumns() ?? [];
    }

    public function filters(): array
    {
        return $this->getConfig()->getFilters() ?? [];
    }

    public function sortBy(string $column, string $direction): void
    {
        $sortableColumns = collect($this->columns())
            ->filter(fn ($column) => $column->isSortable())
            ->map(fn ($column) => $column->getKey())
            ->toArray();

        if (! in_array($direction, ['desc', 'asc']) || ! in_array($column, $sortableColumns)) {
            return;
        }

        $this->orderBy  = $column;
        $this->orderWay = $direction;
    }

    public function resetFilters(): void
    {
        foreach ($this->filters() as $filter) {
            $this->filters[$filter->getKey()] = $filter->getValue();
        }

        $this->searchText       = '';
        $this->selectedFilterId = null;

        $this->dispatch('input-reset');
    }

    public function getTitle(): string
    {
        return $this->getConfig()->getTitle();
    }

    public function getIcon(): ?string
    {
        return $this->getConfig()->getIcon();
    }

    public function query(): EloquentBuilder|QueryBuilder
    {
        return $this->getConfig()->getQuery();
    }

    protected function prepareQuery(): EloquentBuilder|QueryBuilder
    {
        $query = $this->query()->clone();

        $queryBefore = $query->toSql();
        foreach ($this->filters() as $filter) {
            $query = $filter->state($this->filters[$filter->getKey()] ?? null)->apply($query);
        }

        $this->hasFilters = $queryBefore != $query->toSql();

        if ($this->getConfig()->hasGlobalSearch()) {
            $query = $this->applySearch($query);
        }

        return $query;
    }

    public function results(): Collection|LengthAwarePaginator
    {
        $results = $this->prepareQuery()
            ->reorder($this->orderBy, $this->orderWay)
            ->paginate($this->perPage);

        if ($results->isNotEmpty() && head($results->items()) instanceof stdClass) {
            $collection = $results->getCollection()->map(function (stdClass $row) {
                return collect((array) $row);
            });

            return $results->setCollection($collection);
        }

        return $results;
    }

    public function searchTextUpdated($query): void
    {
        $this->searchText = $query;
    }

    public function export(string $exportKey): mixed
    {
        $export = $this->getConfig()->getExport($exportKey);

        abort_unless($export, 404);

        $query = $this->prepareQuery()->reorder($this->orderBy, $this->orderWay);

        return $export->run($query, [
            'tableName' => $this->tableName,
            'filters'   => $this->filters,
            'search'    => $this->searchText,
        ]);
    }

    public function applySearch(EloquentBuilder|QueryBuilder $builder): EloquentBuilder|QueryBuilder
    {
        if (empty($this->searchText)) {
            return $builder;
        }

        $callback = $this->getConfig()->getSearchCallback();

        return $callback($builder, $this->searchText);
    }

    protected function getPerpageKey(): string
    {
        return 'perpage_' . str($this->tableName)->snake()->toString();
    }

    public function updatingPerPage(string $value): void
    {
        if (! is_numeric($value)) {
            $value         = 10;
            $this->perPage = 10;
        }

        app(PerPageStore::class)->put(
            $this->getPerpageKey(),
            (int) $value,
            app(CurrentUserResolver::class)->resolve()
        );
    }

    public function updatedFilters(): void
    {
        if ($this->selectedFilterId !== null) {
            $this->selectedFilterId = null;
        }

        $this->resetPage();
    }

    public function updatedSearchText(): void
    {
        if ($this->selectedFilterId !== null) {
            $this->selectedFilterId = null;
        }

        $this->resetPage();
    }

    /*
     * ---------------------
     * Saved filters
     * ---------------------
     */

    public function getSavedFilters(): \Illuminate\Support\Collection
    {
        if (! $this->getConfig()->hasSavedFilters()) {
            return collect();
        }

        $user = app(CurrentUserResolver::class)->resolve();

        if (! $user) {
            return collect();
        }

        return SuperTableFilter::query()
            ->where('userable_type', $user->getMorphClass())
            ->where('userable_id', $user->getKey())
            ->where('key', $this->tableName)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function openSaveFilterModal(): void
    {
        $this->showSaveFilterModal = true;
        $this->filterName          = '';

        $this->dispatch('modal_custom_open', ['saveFilterModal']);
    }

    public function closeSaveFilterModal(): void
    {
        $this->showSaveFilterModal = false;
        $this->filterName          = '';

        $this->dispatch('modal_custom_close', ['saveFilterModal']);
    }

    public function saveCustomFilter(): void
    {
        $this->validate([
            'filterName' => 'required|string|max:255',
        ], [
            'filterName.required' => __('supertable::messages.filter_name_required'),
            'filterName.max'      => __('supertable::messages.filter_name_max'),
        ]);

        $user = app(CurrentUserResolver::class)->resolve();

        if (! $user) {
            $this->closeSaveFilterModal();

            return;
        }

        SuperTableFilter::create([
            'userable_type' => $user->getMorphClass(),
            'userable_id'   => $user->getKey(),
            'key'           => $this->tableName,
            'name'          => $this->filterName,
            'metadata'      => [
                'filters'    => $this->filters,
                'searchText' => $this->searchText,
            ],
        ]);

        $this->closeSaveFilterModal();
    }

    public function loadCustomFilter(int $filterId): void
    {
        $filter = $this->findOwnedFilter($filterId);

        if (! $filter) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'message' => __('supertable::messages.filter_not_found'),
            ]);

            return;
        }

        $metadata = $filter->metadata;

        $this->filters          = $metadata['filters'] ?? [];
        $this->searchText       = $metadata['searchText'] ?? '';
        $this->selectedFilterId = $filterId;

        $this->dispatch('input-reset');

        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => __('supertable::messages.filter_loaded'),
        ]);
    }

    public function deleteCustomFilter(int $filterId): void
    {
        $filter = $this->findOwnedFilter($filterId);

        if (! $filter) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'message' => __('supertable::messages.filter_not_found'),
            ]);

            return;
        }

        $filter->delete();

        if ($this->selectedFilterId === $filterId) {
            $this->selectedFilterId = null;
        }

        $this->dispatch('toast', [
            'type'    => 'success',
            'message' => __('supertable::messages.filter_deleted'),
        ]);
    }

    public function clearSelectedFilter(): void
    {
        $this->selectedFilterId = null;
        $this->resetFilters();
    }

    protected function findOwnedFilter(int $filterId): ?SuperTableFilter
    {
        $user = app(CurrentUserResolver::class)->resolve();

        if (! $user) {
            return null;
        }

        return SuperTableFilter::query()
            ->where('id', $filterId)
            ->where('userable_type', $user->getMorphClass())
            ->where('userable_id', $user->getKey())
            ->where('key', $this->tableName)
            ->first();
    }

    public function render(): View
    {
        $render = view('supertable::livewire.super-table', [
            'results'      => $this->results(),
            'savedFilters' => $this->getSavedFilters(),
        ]);

        $this->mounted = true;

        return $render;
    }
}
