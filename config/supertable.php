<?php

use RSE\SuperTable\Support\AuthCurrentUserResolver;
use RSE\SuperTable\Support\SessionPerPageStore;

return [

    /*
    |--------------------------------------------------------------------------
    | Current User Resolver
    |--------------------------------------------------------------------------
    |
    | Resolves the "current user" for saved-filter ownership and per-page
    | preference persistence. Defaults to the framework's default guard.
    | Apps using a custom auth helper (multi-guard setups, etc.) should
    | bind their own RSE\SuperTable\Contracts\CurrentUserResolver here.
    |
    */

    'user_resolver' => AuthCurrentUserResolver::class,

    /*
    |--------------------------------------------------------------------------
    | Per-Page Store
    |--------------------------------------------------------------------------
    |
    | Persists the user's chosen page size per table. Defaults to the
    | session. Apps that already have a settings/preferences system should
    | bind their own RSE\SuperTable\Contracts\PerPageStore here.
    |
    */

    'per_page_store' => SessionPerPageStore::class,

];
