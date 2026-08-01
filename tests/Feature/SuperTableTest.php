<?php

use Illuminate\Support\Facades\Auth;
use RSE\SuperTable\Column;
use RSE\SuperTable\Livewire\SuperTableComponent;
use RSE\SuperTable\Models\SuperTableFilter;
use RSE\SuperTable\SelectFilter;
use RSE\SuperTable\SuperTableConfig;
use RSE\SuperTable\SuperTableRepo;
use RSE\SuperTable\Tests\Post;
use RSE\SuperTable\Tests\User;
use RSE\SuperTable\TextFilter;

function registerPostsTable(string $name = 'posts', bool $savedFilters = false): SuperTableConfig
{
    $config = SuperTableConfig::make()
        ->setTitle('Posts')
        ->setQuery(fn () => Post::query())
        ->setSearchCallback(fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
        ->setOrderBy('id')
        ->setOrderWay('asc')
        ->setColumns([
            Column::make('title')->label('Title')->sortable(),
            Column::make('active')->label('Active'),
        ])
        ->setFilters([
            TextFilter::make('title')->label('Title'),
            SelectFilter::make('active')->label('Active')->options(['1' => 'Yes', '0' => 'No']),
        ])
        ->withSavedFilters($savedFilters);

    SuperTableRepo::register($name, $config);

    return $config;
}

function mountSuperTable(string $tableName): SuperTableComponent
{
    $component = new SuperTableComponent;
    $component->mount($tableName);

    return $component;
}

it('filters rows via a text filter', function () {
    registerPostsTable();

    Post::create(['title' => 'Alpha', 'active' => true]);
    Post::create(['title' => 'Beta', 'active' => true]);

    $component                   = mountSuperTable('posts');
    $component->filters['title'] = 'Alpha';

    $titles = $component->results()->pluck('title');

    expect($titles)->toContain('Alpha')->not->toContain('Beta');
});

it('applies the global search callback', function () {
    registerPostsTable();

    Post::create(['title' => 'Findable', 'active' => true]);
    Post::create(['title' => 'Hidden', 'active' => true]);

    $component             = mountSuperTable('posts');
    $component->searchText = 'Findable';

    $titles = $component->results()->pluck('title');

    expect($titles)->toContain('Findable')->not->toContain('Hidden');
});

it('only sorts by sortable, registered columns', function () {
    registerPostsTable();

    $component = mountSuperTable('posts');

    $component->sortBy('title', 'asc');
    expect($component->orderBy)->toBe('title');
    expect($component->orderWay)->toBe('asc');

    // "active" is registered but not marked sortable() — must be ignored.
    $component->sortBy('active', 'desc');
    expect($component->orderBy)->toBe('title');

    // Bogus direction must be ignored too.
    $component->sortBy('title', 'sideways');
    expect($component->orderWay)->toBe('asc');
});

it('paginates results', function () {
    registerPostsTable();

    collect(range(1, 15))->each(
        fn ($i) => Post::create(['title' => "Post {$i}", 'active' => true])
    );

    $component           = mountSuperTable('posts');
    $component->perPage  = 10;

    $results = $component->results();

    expect($results->total())->toBe(15);
    expect($results->count())->toBe(10);
});

it('keeps saved filters disabled by default', function () {
    registerPostsTable(savedFilters: false);

    $component = mountSuperTable('posts');

    expect($component->getSavedFilters())->toBeEmpty();
});

it('saves, loads, and deletes a saved filter when enabled', function () {
    registerPostsTable('posts_with_saved_filters', savedFilters: true);

    $user = User::create(['name' => 'Tester']);
    Auth::login($user);

    $component                   = mountSuperTable('posts_with_saved_filters');
    $component->filters['title'] = 'Alpha';
    $component->filterName       = 'My filter';
    $component->saveCustomFilter();

    $saved = SuperTableFilter::query()->where('key', 'posts_with_saved_filters')->first();

    expect($saved)->not->toBeNull();
    expect($saved->name)->toBe('My filter');
    expect($saved->metadata['filters']['title'])->toBe('Alpha');
    expect($component->getSavedFilters())->toHaveCount(1);

    $loader = mountSuperTable('posts_with_saved_filters');
    $loader->loadCustomFilter($saved->id);

    expect($loader->filters['title'])->toBe('Alpha');
    expect($loader->selectedFilterId)->toBe($saved->id);

    $loader->deleteCustomFilter($saved->id);

    expect(SuperTableFilter::query()->find($saved->id))->toBeNull();
});

it('throws when a table name was never registered', function () {
    mountSuperTable('never-registered');
})->throws(Exception::class, 'Config Not found for superTable : never-registered');
