<?php

namespace RSE\SuperTable;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class Column
{
    /**
     * @var string|float|Closure
     */
    public mixed $state = null;

    public Model|Collection $model;

    protected ?string $key = null;

    protected ?string $label = null;

    protected ?string $style = null;

    protected ?string $headerClass = null;

    protected ?string $class = null;

    protected ?string $width = null;

    protected ?string $direction = null;

    protected bool $sortable = false;

    public string|Closure|null $clickLink = null;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function make(string $key): static
    {
        return new static($key);
    }

    public function model(Model|Collection $model): static
    {
        $this->model = $model;

        return $this;
    }

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

    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function headerClass(string $headerClass): static
    {
        $this->headerClass = $headerClass;

        return $this;
    }

    public function class(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function state(string|Closure $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function direction(string|Closure $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function sortable(): static
    {
        $this->sortable = true;

        return $this;
    }

    public function clickLink(string|Closure $href): static
    {
        $this->clickLink = $href;

        return $this;
    }

    public function evaluate(string $key): string|HtmlString
    {
        if (property_exists($this, $key) && is_callable($this->$key)) {
            return call_user_func($this->$key, $this->model) ?? '';
        }

        if (property_exists($this, $key) && ! is_null($this->{$key})) {
            return $this->{$key};
        }

        if (
            $this->model instanceof Model
            && method_exists($this->model, $this->key)
            && is_object($this->model->{$this->key}())
        ) {
            return 'RELATION !';
        }

        $value = $this->model[$this->key];

        if (is_object($value)) {
            return 'ERROR';
        }

        return $value ?? '';
    }

    public function getValue(): string|HtmlString
    {
        return $this->evaluate('state');
    }

    public function getLabel(): string|HtmlString
    {
        return $this->label ?? $this->key;
    }

    public function getStyle(): ?string
    {
        return $this->style ?? '';
    }

    public function getHeaderClass(): ?string
    {
        return $this->headerClass ?? '';
    }

    public function getClass(): ?string
    {
        return $this->class ?? '';
    }

    public function getWidth(): ?string
    {
        return $this->width ?? null;
    }

    public function getDirection(): ?string
    {
        return $this->evaluate('direction') ?? null;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function render(): string|HtmlString
    {
        return $this->getValue();
    }
}
