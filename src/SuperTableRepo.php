<?php

namespace RSE\SuperTable;

use Exception;
use RSE\SuperTable\Support\TableRegistry;

class SuperTableRepo
{
    public static function register(string $tableName, SuperTableConfig $config): void
    {
        app(TableRegistry::class)->put('supertable.' . $tableName, $config);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function getConfigInstance(string $tableName): SuperTableConfig
    {
        $config = app(TableRegistry::class)->get('supertable.' . $tableName);

        if (! $config instanceof SuperTableConfig) {
            throw new Exception('Config Not found for superTable : ' . $tableName);
        }

        return $config;
    }
}
