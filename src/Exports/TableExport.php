<?php

namespace RSE\SuperTable\Exports;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Facades\Excel;

class TableExport
{
    protected string|Closure|null $label = null;

    protected string|Closure|null $icon = null;

    protected string|Closure|null $filename = null;

    protected string|object|null $handler = null;

    public function __construct(protected string $key) {}

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function label(string|Closure $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function icon(string|Closure $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function filename(string|Closure $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Accepts a Closure, an invokable class (string or instance), or a
     * Maatwebsite\Excel export class name — the export type is detected
     * automatically when it runs.
     */
    public function handler(string|object $handler): static
    {
        $this->handler = $handler;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return (string) ($this->evaluate($this->label) ?? $this->key);
    }

    public function getIcon(): ?string
    {
        return $this->evaluate($this->icon);
    }

    /**
     * Runs the export handler against the prepared (unpaginated) query and
     * returns whatever the handler produces — a download response, a
     * streamed response, etc.
     *
     * @param  array<string, mixed>  $context  Extra data made available to closures/invokables by parameter name (e.g. tableName, filters, search).
     */
    public function run(EloquentBuilder|QueryBuilder|Relation $query, array $context = []): mixed
    {
        $context = array_merge($context, [
            'query'    => $query,
            'export'   => $this,
            'filename' => $this->resolveFilename($context),
        ]);

        if (is_string($this->handler) && $this->isMaatwebsiteExport($this->handler)) {
            return Excel::download(new $this->handler($query), $context['filename']);
        }

        return app()->call($this->handler, $context);
    }

    protected function isMaatwebsiteExport(string $class): bool
    {
        if (! interface_exists(FromQuery::class)) {
            return false;
        }

        return is_subclass_of($class, FromQuery::class)
            || is_subclass_of($class, FromCollection::class)
            || is_subclass_of($class, FromView::class)
            || is_subclass_of($class, FromArray::class);
    }

    protected function resolveFilename(array $context): string
    {
        if ($this->filename) {
            return (string) $this->evaluate($this->filename, $context);
        }

        $table = $context['tableName'] ?? 'export';

        return str($table)->snake() . '-' . $this->key . '-' . now()->format('Ymd-His') . '.xlsx';
    }

    protected function evaluate(string|Closure|null $value, array $context = []): ?string
    {
        if ($value instanceof Closure) {
            return app()->call($value, $context);
        }

        return $value;
    }
}
