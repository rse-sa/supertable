<?php

use RSE\SuperTable\Exports\TableExport;
use RSE\SuperTable\Tests\Post;
use RSE\SuperTable\Tests\RecordingInvokableExport;

it('falls back to its key as the label and has no icon when none are set', function () {
    $export = TableExport::make('csv');

    expect($export->getKey())->toBe('csv');
    expect($export->getLabel())->toBe('csv');
    expect($export->getIcon())->toBeNull();
});

it('evaluates static and closure labels/icons', function () {
    $export = TableExport::make('csv')
        ->label('Export CSV')
        ->icon(fn () => 'fa-solid fa-file-csv');

    expect($export->getLabel())->toBe('Export CSV');
    expect($export->getIcon())->toBe('fa-solid fa-file-csv');
});

it('calls a closure handler, resolving query and context by parameter name', function () {
    Post::create(['title' => 'Alpha', 'active' => true]);

    $received = null;

    $export = TableExport::make('csv')->handler(
        function ($query, string $tableName) use (&$received) {
            $received = [$query->count(), $tableName];

            return 'handled';
        }
    );

    $result = $export->run(Post::query(), ['tableName' => 'posts']);

    expect($result)->toBe('handled');
    expect($received)->toBe([1, 'posts']);
});

it('calls an invokable class handler and passes the resolved filename', function () {
    RecordingInvokableExport::$receivedQuery    = null;
    RecordingInvokableExport::$receivedFilename = null;

    $export = TableExport::make('csv')->handler(RecordingInvokableExport::class);

    $result = $export->run(Post::query(), ['tableName' => 'posts']);

    expect($result)->toBe('invoked-response');
    expect(RecordingInvokableExport::$receivedQuery)->not->toBeNull();
    expect(RecordingInvokableExport::$receivedFilename)->toBe(
        'posts-csv-' . now()->format('Ymd-His') . '.xlsx'
    );
});

it('lets a custom filename override the generated default', function () {
    $received = null;

    $export = TableExport::make('csv')
        ->filename('custom-name.csv')
        ->handler(function (string $filename) use (&$received) {
            $received = $filename;
        });

    $export->run(Post::query());

    expect($received)->toBe('custom-name.csv');
});
