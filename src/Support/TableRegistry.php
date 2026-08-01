<?php

namespace RSE\SuperTable\Support;

class TableRegistry
{
    protected array $items = [];

    public function put(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }
}
