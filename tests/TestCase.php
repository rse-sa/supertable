<?php

namespace RSE\SuperTable\Tests;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RSE\SuperTable\SupertableServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SupertableServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}

// ---------------------------------------------------------------------------
// Fixture models — used in feature tests
// ---------------------------------------------------------------------------

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;

    protected $table   = 'users';

    protected $guarded = [];
}

class Post extends Model
{
    protected $table = 'posts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}

class RecordingInvokableExport
{
    public static mixed $receivedQuery = null;

    public static ?string $receivedFilename = null;

    public function __invoke(mixed $query, ?string $filename = null): string
    {
        static::$receivedQuery    = $query;
        static::$receivedFilename = $filename;

        return 'invoked-response';
    }
}
