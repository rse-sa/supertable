<?php

namespace RSE\SuperTable\Contracts;

use Illuminate\Database\Eloquent\Model;

interface PerPageStore
{
    public function get(string $key, int $default, ?Model $user): int;

    public function put(string $key, int $value, ?Model $user): void;
}
