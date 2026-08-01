<?php

namespace RSE\SuperTable\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SuperTableFilter extends Model
{
    protected $table = 'super_table_filters';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }
}
