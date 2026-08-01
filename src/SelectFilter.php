<?php

namespace RSE\SuperTable;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;

class SelectFilter extends TextFilter
{
    protected Closure|array $options = [];

    protected ?Closure $filterCallback = null;

    public function apply(EloquentBuilder|QueryBuilder $builder): EloquentBuilder|QueryBuilder
    {
        $value = $this->getValue();

        if (empty($value) && $value != '0') {
            return $builder;
        }

        if ($this->filterCallback) {
            return ($this->filterCallback)($builder, $this->key, $value);
        }

        return $builder->where($this->key, $value);
    }

    /*
     * ---------------------
     * Setters
     * ---------------------
     */

    public function options(array|Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function customQuery(?Closure $callback): static
    {
        $this->filterCallback = $callback;

        return $this;
    }

    /*
     * ---------------------
     * Getters
     * ---------------------
     */

    public function getOptions(): array
    {
        return $this->evaluateArray('options');
    }

    /*
     * ---------------------
     * Render
     * ---------------------
     */

    public function render(): string
    {
        return Blade::render(
            <<<'HTML'
<x-twc::form.elements.select name='{{ $key }}' wire:model.live='filters.{{ $key }}' placeholder-color="text-slate-500">
<option value="" selected data-default>{{ $label }}</option>
@foreach($options as $key => $option)
<option @selected($value == $key) value="{{ $key }}" class="text-slate-700 dark:text-gray-200">{{ $option }}</option>
@endforeach
</x-twc::form.elements.select>
HTML
            ,
            [
                'key'     => $this->getKey(),
                'label'   => $this->getLabel(),
                'options' => $this->getOptions(),
                'value'   => $this->getValue(),
            ]
        );
    }
}
