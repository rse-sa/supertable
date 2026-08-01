<?php

use RSE\SuperTable\Exports\TableExport;
use RSE\SuperTable\Livewire\SimpleTableComponent;
use RSE\SuperTable\SimpleTableConfig;
use RSE\SuperTable\SimpleTableRepo;
use RSE\SuperTable\Tests\Post;

it('resolves the query through the parameters closure and paginates it', function () {
    $config = SimpleTableConfig::make()
        ->setTitle('Simple posts')
        ->setViewName('some-app-supplied-view')
        ->setQuery(fn (array $parameters) => Post::query()->where('active', $parameters['active'] ?? true))
        ->setOrderBy('id')
        ->setOrderWay('asc');

    SimpleTableRepo::register('simple-posts', $config);

    collect(range(1, 3))->each(fn ($i) => Post::create(['title' => "Post {$i}", 'active' => true]));
    Post::create(['title' => 'Inactive post', 'active' => false]);

    $component = new SimpleTableComponent;
    $component->mount('simple-posts', ['active' => true]);

    $results = $component->results();

    expect($results->total())->toBe(3);
    expect($results->pluck('title'))->not->toContain('Inactive post');
    expect($component->getConfig()->getViewName())->toBe('some-app-supplied-view');
});

it('respects the parameters passed at mount time', function () {
    $config = SimpleTableConfig::make()
        ->setViewName('unused')
        ->setQuery(fn (array $parameters) => Post::query()->where('active', $parameters['active'] ?? true));

    SimpleTableRepo::register('simple-posts-inactive', $config);

    Post::create(['title' => 'Active post', 'active' => true]);
    Post::create(['title' => 'Inactive post', 'active' => false]);

    $component = new SimpleTableComponent;
    $component->mount('simple-posts-inactive', ['active' => false]);

    expect($component->results()->pluck('title'))->toContain('Inactive post');
});

it('throws when a table name was never registered', function () {
    (new SimpleTableComponent)->mount('never-registered', []);
})->throws(Exception::class, 'Config Not found for simpleTable : never-registered');

it('runs a registered export handler against the unpaginated query', function () {
    $received = null;

    $config = SimpleTableConfig::make()
        ->setViewName('unused')
        ->setQuery(fn (array $parameters) => Post::query())
        ->setExports([
            TableExport::make('csv')->handler(function ($query) use (&$received) {
                $received = $query->count();

                return 'exported';
            }),
        ]);

    SimpleTableRepo::register('simple-posts-exportable', $config);

    Post::create(['title' => 'A', 'active' => true]);
    Post::create(['title' => 'B', 'active' => true]);

    $component = new SimpleTableComponent;
    $component->mount('simple-posts-exportable', []);

    expect($component->export('csv'))->toBe('exported');
    expect($received)->toBe(2);
});
