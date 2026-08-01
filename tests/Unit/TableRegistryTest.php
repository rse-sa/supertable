<?php

use RSE\SuperTable\Support\TableRegistry;

it('holds no shared state between separate instances', function () {
    $first  = new TableRegistry;
    $second = new TableRegistry;

    $first->put('table', 'first-config');

    expect($first->get('table'))->toBe('first-config');
    expect($second->get('table'))->toBeNull();
});

it('is resolved as the same singleton within one container', function () {
    $a = app(TableRegistry::class);
    $b = app(TableRegistry::class);

    $a->put('shared', 'value');

    expect($b->get('shared'))->toBe('value');
});
