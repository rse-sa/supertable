<?php

namespace RSE\SuperTable\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Locked;
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

    public function mount(string $tableName, array $parameters = []): void
    {
        $this->tableName = $tableName;

        $this->parameters = $parameters;

        $config = $this->getConfig();

        $this->orderBy  = $config->getOrderBy();
        $this->orderWay = $config->getOrderWay();
    }

    public function getConfig(): SimpleTableConfig
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return SimpleTableRepo::getConfigInstance($this->tableName);
    }

    protected function prepareQuery(): Builder|Relation
    {
        return $this->getConfig()->getQuery($this->parameters);
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
