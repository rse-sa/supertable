# SuperTable By RSE.SA

> Livewire-backed data-table abstraction for Laravel — a fluent config builder plus two ready-made
> Livewire components: a rich, filterable/sortable **SuperTable**, and a lightweight **SimpleTable**
> for when you just need pagination around a query and your own row markup.

---

## Requirements

| Dependency | Version        |
|------------|----------------|
| PHP        | ^8.2           |
| Laravel    | ^11.0 \| ^12.0 |
| Livewire   | ^3.0 \| ^4.0   |

The package's own Blade views hardcode the `x-twc::` component namespace (card, table, form
elements, modal). Your app needs to make that namespace resolve — either by requiring
`rse-sa/tw-components` directly, or by registering your own `Blade::anonymousComponentPath($path, 'twc')` /
`loadViewsFrom($path, 'twc')` pointing at equivalent components.

## Installation

Add a path repository if the package doesn't live in your global Composer path-repo config, then require it:

```json
"repositories": [
    { "type": "path", "url": "packages/supertable", "options": { "symlink": true } }
]
```

```bash
composer require rse-sa/supertable:dev-master
```

Publish the config if you want to override the defaults:

```bash
php artisan vendor:publish --tag=supertable-config
```

## SuperTable

Register a table once (typically from a service provider boot method or a controller's static
`registerTables()` method called from there):

```php
use RSE\SuperTable\SuperTableConfig;
use RSE\SuperTable\SuperTableRepo;
use RSE\SuperTable\Column;
use RSE\SuperTable\AnchorColumn;
use RSE\SuperTable\TextFilter;
use RSE\SuperTable\SelectFilter;

SuperTableRepo::register('contracts', SuperTableConfig::make()
    ->setTitle(__('Contracts'))
    ->setQuery(fn () => Document::query()->where('type', 'contract'))
    ->setSearchCallback(fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
    ->setOrderBy('created_at')
    ->setOrderWay('desc')
    ->setColumns([
        Column::make('title')->label(__('Title'))->sortable(),
        Column::make('status')->label(__('Status')),
        AnchorColumn::make('link')->link(fn ($document) => route('contracts.show', $document)),
    ])
    ->setFilters([
        TextFilter::make('title')->label(__('Title')),
        SelectFilter::make('status')->label(__('Status'))->options(fn () => Status::options()),
    ])
);
```

Then render it anywhere:

```blade
<livewire:super-table table-name="contracts" />
```

### Saved filters

Off by default. Turn it on per table:

```php
SuperTableConfig::make()->withSavedFilters();
```

This activates a saved-filters strip (backed by the package's own `super_table_filters` table,
migrated automatically) and a save/load/delete UI, scoped to the user resolved by your bound
`CurrentUserResolver`.

## SimpleTable

For cases where you'd rather write your own row markup than describe columns declaratively:

```php
use RSE\SuperTable\SimpleTableConfig;
use RSE\SuperTable\SimpleTableRepo;

SimpleTableRepo::register('recent-logins', SimpleTableConfig::make()
    ->setTitle(__('Recent logins'))
    ->setViewName('admin.partials.recent-logins-rows')
    ->setQuery(fn (array $parameters) => LoginLog::query()->where('user_id', $parameters['userId']))
);
```

```blade
<livewire:simple-table table-name="recent-logins" :parameters="['userId' => $user->id]" />
```

Your view receives `$results` (a `LengthAwarePaginator`) and `$tableName`.

## Exporting

Both `SuperTable` and `SimpleTable` support registering one or more export options. Each one
renders as an entry in an "Export" dropdown button, and can be handled by a closure, an invokable
class, or a `Maatwebsite\Excel` export class — the type is detected automatically:

```php
use RSE\SuperTable\Exports\TableExport;

SuperTableConfig::make()
    // ...
    ->setExports([
        // A Maatwebsite\Excel export — instantiated as `new $class($query)` and streamed via Excel::download().
        TableExport::make('excel')
            ->label(__('Export Excel'))
            ->icon('fa-solid fa-file-excel')
            ->handler(ContractsExport::class),

        // A closure — return anything Livewire can turn into a download response.
        TableExport::make('csv')
            ->label(__('Export CSV'))
            ->icon('fa-solid fa-file-csv')
            ->handler(function ($query, string $filename) {
                return response()->streamDownload(function () use ($query) {
                    // write CSV rows from $query->cursor() ...
                }, $filename);
            }),

        // An invokable class — same signature rules as a closure.
        TableExport::make('pdf')
            ->label(__('Export PDF'))
            ->icon('fa-solid fa-file-pdf')
            ->handler(ContractsPdfExport::class),
    ]);
```

`->handler()` is dispatched through `app()->call()` (except for `Maatwebsite\Excel` classes, which
are constructed directly), so closures and invokable classes can type-hint any subset of these by
parameter name:

| Parameter   | Type                | Description                                                                                         |
|-------------|---------------------|-----------------------------------------------------------------------------------------------------|
| `query`     | `Builder\|Relation` | The table's query with filters, search, and ordering applied — no pagination.                       |
| `filename`  | `string`            | Generated as `{tableName}-{exportKey}-{timestamp}.xlsx`, or whatever `->filename(...)` resolves to. |
| `tableName` | `string`            | The registered table key.                                                                           |
| `filters`   | `array`             | Current filter values (`SuperTable` only).                                                          |
| `search`    | `string`            | Current global search text (`SuperTable` only).                                                     |
| `export`    | `TableExport`       | The export definition itself.                                                                       |

`->label()`, `->icon()`, and `->filename()` all accept either a plain string or a closure (also
resolved via `app()->call()`).

`maatwebsite/excel` is only required if you actually register a `Maatwebsite\Excel` export class —
the package detects it via `interface_exists()` and works fine without it for closure/invokable-only
setups.

## Swappable seams

Both components need to know "who is the current user" (saved-filter ownership, per-page
preference key) and "where to persist the chosen page size." Both default to framework-native
behavior and are overridable via `config/supertable.php`:

```php
use RSE\SuperTable\Contracts\CurrentUserResolver;

class MyUserResolver implements CurrentUserResolver
{
    public function resolve(): ?\Illuminate\Database\Eloquent\Model
    {
        return getUser(); // or auth()->guard('admin')->user(), etc.
    }
}
```

```php
// config/supertable.php
'user_resolver' => \App\Support\MyUserResolver::class,
'per_page_store' => \App\Support\MySettingsPerPageStore::class,
```

Implement `RSE\SuperTable\Contracts\PerPageStore` the same way to plug in an existing
settings/preferences system instead of the session-based default.

## Testing

```bash
cd packages/supertable
composer install
vendor/bin/pest
```
