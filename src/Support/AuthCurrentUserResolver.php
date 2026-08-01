<?php

namespace RSE\SuperTable\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use RSE\SuperTable\Contracts\CurrentUserResolver;

class AuthCurrentUserResolver implements CurrentUserResolver
{
    public function resolve(): ?Model
    {
        $user = Auth::user();

        return $user instanceof Model ? $user : null;
    }
}
