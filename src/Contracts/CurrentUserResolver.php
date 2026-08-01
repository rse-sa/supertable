<?php

namespace RSE\SuperTable\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CurrentUserResolver
{
    public function resolve(): ?Model;
}
