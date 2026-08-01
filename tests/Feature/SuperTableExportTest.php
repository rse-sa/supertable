<?php

use RSE\SuperTable\Column;
use RSE\SuperTable\Exports\TableExport;
use RSE\SuperTable\Livewire\SuperTableComponent;
use RSE\SuperTable\SuperTableConfig;
use RSE\SuperTable\SuperTableRepo;
use RSE\SuperTable\Tests\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function registerExportablePostsTable(string $name, array $exports): SuperTableConfig
{
    $config = SuperTableConfig::make()
        ->setTitle('Posts')
        ->setQuery(fn () => Post::query())
        ->setSearchCallback(fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
        ->setColumns([Column::make('title')->label('Title')])
        ->setExports($exports);

    SuperTableRepo::register($name, $config);

    return $config;
}

it('exposes registered exports on the config', function () {
    $export = TableExport::make('csv')->label('Export CSV');

    $config = registerExportablePostsTable('exportable-posts', [$export]);

    expect($config->hasExports())->toBeTrue();
    expect($config->getExport('csv'))->toBe($export);
    expect($config->getExport('missing'))->toBeNull();
});

it('runs the matching export handler against the filtered, unpaginated query', function () {
    Post::create(['title' => 'Findable', 'active' => true]);
    Post::create(['title' => 'Hidden', 'active' => true]);

    $received = null;

    registerExportablePostsTable('exportable-posts-filtered', [
        TableExport::make('csv')->handler(function ($query) use (&$received) {
            $received = $query->pluck('title')->all();

            return 'exported';
        }),
    ]);

    $component             = new SuperTableComponent;
    $component->mount('exportable-posts-filtered');
    $component->searchText = 'Findable';

    $result = $component->export('csv');

    expect($result)->toBe('exported');
    expect($received)->toBe(['Findable']);
});

it('picks the right handler when multiple exports are registered', function () {
    $csvRan   = false;
    $excelRan = false;

    registerExportablePostsTable('exportable-posts-multi', [
        TableExport::make('csv')->handler(function () use (&$csvRan) {
            $csvRan = true;

            return 'csv';
        }),
        TableExport::make('xlsx')->handler(function () use (&$excelRan) {
            $excelRan = true;

            return 'xlsx';
        }),
    ]);

    $component = new SuperTableComponent;
    $component->mount('exportable-posts-multi');

    expect($component->export('xlsx'))->toBe('xlsx');
    expect($excelRan)->toBeTrue();
    expect($csvRan)->toBeFalse();
});

it('aborts with a 404 when the export key was never registered', function () {
    registerExportablePostsTable('exportable-posts-unknown', [
        TableExport::make('csv')->handler(fn () => 'csv'),
    ]);

    $component = new SuperTableComponent;
    $component->mount('exportable-posts-unknown');

    $component->export('does-not-exist');
})->throws(NotFoundHttpException::class);
