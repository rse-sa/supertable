<?php

namespace RSE\SuperTable;

use Exception;
use RSE\SuperTable\Support\TableRegistry;

class SimpleTableRepo
{
    public static function register(string $tableName, SimpleTableConfig $config): void
    {
        app(TableRegistry::class)->put('simpletable.' . $tableName, $config);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function getConfigInstance(string $tableName): SimpleTableConfig
    {
        $config = app(TableRegistry::class)->get('simpletable.' . $tableName);

        if (! $config instanceof SimpleTableConfig) {
            throw new Exception('Config Not found for simpleTable : ' . $tableName);
        }

        return $config;
    }
}
