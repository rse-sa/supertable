<?php

namespace RSE\SuperTable;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RSE\SuperTable\Contracts\CurrentUserResolver;
use RSE\SuperTable\Contracts\PerPageStore;
use RSE\SuperTable\Livewire\SimpleTableComponent;
use RSE\SuperTable\Livewire\SuperTableComponent;
use RSE\SuperTable\Support\TableRegistry;

class SupertableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/supertable.php', 'supertable');

        $this->app->singleton(TableRegistry::class);

        $this->app->bind(CurrentUserResolver::class, config('supertable.user_resolver'));
        $this->app->bind(PerPageStore::class, config('supertable.per_page_store'));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'supertable');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'supertable');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Livewire::component('super-table', SuperTableComponent::class);
        Livewire::component('simple-table', SimpleTableComponent::class);

        $this->publishes([
            __DIR__ . '/../config/supertable.php' => config_path('supertable.php'),
        ], 'supertable-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/supertable'),
        ], 'supertable-views');
    }
}
