<?php

namespace RSE\SuperTable\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use RSE\SuperTable\Contracts\PerPageStore;

class SessionPerPageStore implements PerPageStore
{
    public function get(string $key, int $default, ?Model $user): int
    {
        return (int) Session::get($this->sessionKey($key), $default);
    }

    public function put(string $key, int $value, ?Model $user): void
    {
        Session::put($this->sessionKey($key), $value);
    }

    protected function sessionKey(string $key): string
    {
        return 'supertable.per_page.' . $key;
    }
}
