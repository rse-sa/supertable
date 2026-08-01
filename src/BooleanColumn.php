<?php

namespace RSE\SuperTable;

use Illuminate\Support\HtmlString;

class BooleanColumn extends Column
{
    public function render(): string|HtmlString
    {
        $value = (bool) $this->getValue();

        return $value
            ? new HtmlString('<i class="fa-regular fa-circle-check fa-lg text-green-600"></i>')
            : new HtmlString('<i class="fa-regular fa-circle-xmark fa-lg text-amber-600"></i>');
    }
}
