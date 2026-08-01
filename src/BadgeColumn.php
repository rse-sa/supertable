<?php

namespace RSE\SuperTable;

use Closure;
use Illuminate\Support\HtmlString;

class BadgeColumn extends Column
{
    protected ?Closure $colors = null;

    protected ?Closure $icon = null;

    protected string $size = 'lg';

    public function colors(Closure $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    public function icon(Closure $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function render(): string|HtmlString
    {
        $colorValue = $this->evaluate('colors');
        $color      = match ($colorValue) {
            'danger'  => 'bg-danger-100 text-danger-800',
            'primary' => 'bg-primary-100 text-primary-800',
            'warning' => 'bg-warning-100 text-warning-800',
            'success' => 'bg-success-alt-200 text-success-alt-800',
            default   => $colorValue,
        };

        $size = match ($this->evaluate('size')) {
            'lg' => 'px-3 py-1.5',
            'md' => 'px-2.5 py-1.5 text-sm',
            'sm' => 'px-2 py-1 text-xs',
        };

        $icon = $this->icon ? $this->evaluate('icon') : null;

        return new HtmlString('<div
         class="inline-block ' . $size . ' rounded-lg ' . $color . ' ' . $this->getClass() . '"
         style="' . $this->getStyle() . '"
        >' . ($icon ? '<i class="' . $icon . ' me-2"></i>' : '') .
            $this->evaluate('state') . '</div>');
    }
}
