<?php

namespace RSE\SuperTable;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class TextFilter
{
    /**
     * @var string|float|Closure
     */
    public mixed $state = null;

    protected ?string $key = null;

    protected ?string $label = null;

    protected ?string $style = null;

    protected ?string $class = null;

    protected ?string $defaultValue = null;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function apply(EloquentBuilder|QueryBuilder $builder): EloquentBuilder|QueryBuilder
    {
        if (empty($this->getValue())) {
            return $builder;
        }

        return $builder->where($this->key, 'like', "%{$this->getValue()}%");
    }

    /*
     * ---------------------
     * Setters
     * ---------------------
     */

    public function label(string|HtmlString $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function style(string $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function class(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function defaultValue(string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function state(string|Closure|null $state): static
    {
        $this->state = $state;

        return $this;
    }

    /*
     * ---------------------
     * Getters
     * ---------------------
     */

    public function evaluate(string $key): string
    {
        if (is_callable($this->$key)) {
            return call_user_func($this->$key);
        }

        if (! empty($this->$key) || $this->$key == '0') {
            return $this->$key;
        }

        return '';
    }

    public function evaluateArray(string $key): array
    {
        if (is_callable($this->$key)) {
            return call_user_func($this->$key);
        }

        if (! empty($this->$key) || $this->$key == '0') {
            return $this->$key;
        }

        return [];
    }

    public function getValue(): string
    {
        return $this->evaluate('state') ?? $this->defaultValue ?? '';
    }

    public function getLabel(): string|HtmlString
    {
        return $this->label ?? $this->key;
    }

    public function getStyle(): ?string
    {
        return $this->style ?? '';
    }

    public function getClass(): ?string
    {
        return $this->class ?? '';
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /*
     * ---------------------
     * Render
     * ---------------------
     */

    public function render(): string
    {
        return Blade::render(<<<'HTML'
<x-twc::form.elements.text type='text' placeholder="{{ $label }}" name='{{ $key }}' wire:model.live='filters.{{ $key }}' class="placeholder-slate-500"/>
HTML
            , [
                'key'   => $this->getKey(),
                'label' => $this->getLabel(),
            ]);
    }
}
