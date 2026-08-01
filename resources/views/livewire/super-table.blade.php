<div>
    <x-twc::card class="mb-5" :without-point="$this->getIcon()">
        @if(!$this->getConfig()->withoutHeaderBar())
            <x-slot:header>
                <div class="w-full flex flex-col md:flex-row items-center justify-between">
                    <div class="text-primary-700 font-bold">
                        @if($this->getIcon())
                            <i class="{{ $this->getIcon() }} me-2"></i>
                        @endif
                        {{ $this->getTitle() }}
                    </div>
                    <div class="flex justify-end gap-4">
                        @if($this->getConfig()->hasGlobalSearch())
                            <div class="relative">
                                <button class="absolute start-2 top-3 z-10">
                                    <i class="fa-solid fa-search text-slate-400 dark:text-gray-600"></i>
                                </button>

                                <x-twc::form.elements.text
                                    placeholder="{{ __('supertable::messages.search_placeholder') }}"
                                    id="searchText"
                                    name="searchText"
                                    wire:model.live.debounce.250ms="searchText"
                                    class="w-full ps-8 pe-4 xl:w-96"
                                />
                            </div>
                        @endif

                        @if($this->getConfig()->hasExports())
                            <x-twc::dropdown title="{{ __('supertable::messages.export') }}" icon="fa-solid fa-file-export" caret>
                                @foreach($this->getConfig()->getExports() as $export)
                                    <x-twc::dropdown.item
                                        icon="{{ $export->getIcon() }}"
                                        label="{{ $export->getLabel() }}"
                                        link="#"
                                        wire:click="export('{{ $export->getKey() }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="export"
                                    />
                                @endforeach
                            </x-twc::dropdown>
                        @endif

                        @if($this->hasFilters || !empty($this->searchText))
                            <div class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-200 flex justify-center items-center dark:bg-gray-800 dark:hover:bg-gray-900" wire:click="resetFilters">
                                <i class="fa-solid fa-filter-circle-xmark text-slate-700"></i>
                            </div>

                            @if($this->getConfig()->hasSavedFilters())
                                <button
                                    class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-200 flex justify-center items-center dark:bg-gray-800 dark:hover:bg-gray-900"
                                    wire:click="openSaveFilterModal"
                                    type="button"
                                >
                                    <i class="fa-solid fa-save text-slate-700"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </x-slot:header>
        @endif

        @if($savedFilters->isNotEmpty())
            <div class="flex flex-wrap gap-2 p-3 bg-zinc-50/75 dark:bg-gray-800/75 border-b border-slate-200 dark:border-gray-700">
                <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-gray-300 font-semibold">
                    <i class="fa-solid fa-bookmark"></i>
                    <span>{{ __('supertable::messages.saved_filters') }}:</span>
                </div>
                @foreach($savedFilters as $savedFilter)
                    <div
                        class="flex items-center flex-wrap gap-2 px-3 py-2 rounded-lg text-sm transition-all cursor-pointer
                        {{ $selectedFilterId === $savedFilter->id ? 'bg-primary-500 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-900' }}"
                    >
                        <button wire:click="loadCustomFilter({{ $savedFilter->id }})" class="font-medium cursor-pointer">
                            {{ $savedFilter->name }}
                        </button>
                        <button
                            wire:click="deleteCustomFilter({{ $savedFilter->id }})"
                            wire:confirm="{{ __('supertable::messages.confirm_delete_filter') }}"
                            class="ms-1 hover:text-red-600 transition-colors cursor-pointer"
                            title="{{ __('supertable::messages.delete') }}">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        @if(count($this->filters) > 0)
            <div class="flex items-center gap-3">
                <div class="grow grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ $this->getConfig()->getFiltersPerRow() }} gap-4 p-3">
                    @foreach($this->filters() as $filter)
                        {!! $filter->state($this->filters[$filter->getKey()] ?? null)->render() !!}
                    @endforeach
                </div>

                @if($this->getConfig()->withoutHeaderBar() && ($this->hasFilters || !empty($this->searchText)))
                    <div class="p-2">
                        <div class="px-3 py-3 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer hover:bg-slate-200 flex justify-center items-center dark:bg-gray-800 dark:hover:bg-gray-900 dark:border-gray-700" wire:click="resetFilters">
                            <i class="fa-solid fa-filter-circle-xmark text-slate-700 dark:text-gray-200"></i>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div wire:loading.class="animate-pulse" class="relative">
            <div wire:loading.flex class="absolute inset-0 bg-slate-300/10 flex justify-center items-center">
                <style>
                    .loader {
                        width: 60px;
                        aspect-ratio: 4;
                        background: radial-gradient(circle closest-side, #68788f 90%, #0000) 0/calc(100% / 3) 100% space;
                        clip-path: inset(0 100% 0 0);
                        animation: l1 1s steps(4) infinite;
                    }

                    @keyframes l1 {
                        to {
                            clip-path: inset(0 -34% 0 0)
                        }
                    }
                </style>
                <div class="loader"></div>
            </div>
            <x-twc::table>
                <x-twc::table.thead class="[&>th]:border-x [&>th]:border-slate-100 dark:[&>th]:border-gray-900">
                    @foreach($this->columns() as $columnKey => $columnClass)
                            <?php
                            /** @var \RSE\SuperTable\Column|\RSE\SuperTable\BooleanColumn $columnClass */
                            ?>
                        <th class="{{ $columnClass->getHeaderClass() }}"
                            style="{{ $columnClass->getWidth() ? 'width:'. $columnClass->getWidth() .';' : '' }}{{ $columnClass->getStyle() }}"
                        >
                            <div class="flex justify-between">
                                <div>{{ $columnClass->getLabel() }}</div>
                                @if($columnClass->isSortable())
                                    <div>
                                        @if($this->orderBy == $columnClass->getKey() && $this->orderWay == 'desc')
                                            <a wire:click="sortBy('{{ $columnClass->getKey() }}', 'asc')" class="cursor-pointer text-indigo-600 hover:text-blue-600">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </a>
                                        @elseif($this->orderBy == $columnClass->getKey() && $this->orderWay == 'asc')
                                            <a wire:click="sortBy('{{ $columnClass->getKey() }}', 'desc')" class="cursor-pointer text-indigo-600 hover:text-blue-600">
                                                <i class="fa-solid fa-arrow-up"></i>
                                            </a>
                                        @else
                                            <a wire:click="sortBy('{{ $columnClass->getKey() }}', 'desc')" class="cursor-pointer text-slate-300 hover:text-blue-600">
                                                <i class="fa-solid fa-arrow-down"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </x-twc::table.thead>

                @forelse($results as $model)
                        <?php
                        $key = $model instanceof \Illuminate\Database\Eloquent\Model ? $model->getKey(
                        ) : ($model['id'] ?? \Illuminate\Support\Str::random(32));
                        ?>
                    <x-twc::table.row class="[&>td]:border-x [&>td]:border-slate-50 dark:[&>td]:border-gray-800" wire:key="{{ $this->tableName  }}_r1_{{ $key }}">
                        @foreach($this->columns() as $columnKey => $columnClass)
                                <?php
                                /** @var \RSE\SuperTable\Column|\RSE\SuperTable\BooleanColumn $columnClass */
                                $columnClass->model($model);
                                ?>
                            @if($loop->first && $this->collapsible)
                                <td class="align-middle {{ $columnClass->getClass() }}" dir="{{ $columnClass->getDirection() }}">
                                    <div class="flex justify-start items-center gap-4" x-data="{ opened : false }">
                                        <div>
                                            <a @click="$dispatch('open-collapsible', '{{ $key }}', !opened); opened = !opened;"
                                               href="#"
                                               class="inline-block p-1.5 px-2 text-xs rounded-lg  hover:bg-slate-200"
                                               :class="[opened ? 'text-blue-600' : 'text-slate-400']"
                                            >
                                                <i class="fa fa-chevron-down" :class="[opened ? 'rotate-180' : '']"></i>
                                            </a>
                                        </div>
                                        <div>
                                            {{ $columnClass->render() }}
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td class="align-middle {{ $columnClass->getClass() }}" dir="{{ $columnClass->getDirection() }}">
                                    @if($columnClass->clickLink)
                                        <a href="{{ $columnClass->evaluate('clickLink') }}" class="text-primary-600 hover:text-primary-800 dark:text-primary-200 dark:hover:text-primary-300 font-semibold transition-all cursor-pointer">
                                            {{ $columnClass->render() }}
                                        </a>
                                    @else
                                        {{ $columnClass->render() }}
                                    @endif
                                </td>
                            @endif
                        @endforeach
                    </x-twc::table.row>
                    @if($this->collapsible)
                        <x-twc::table.row
                            x-data="{ opened : false }"
                            x-on:open-collapsible.window="if($event.detail[0] == '{{ $key }}'){ opened = $event.detail[1]; }"
                            class="[&>td]:border-x [&>td]:border-slate-50 dark:[&>td]:border-gray-800" wire:key="{{ $this->tableName  }}_r2_{{ $key }}">
                            <td colspan="10">
                                {{ $columnData['collapsible']($model) }}
                            </td>
                        </x-twc::table.row>
                    @endif
                @empty
                    <x-twc::table.row>
                        <td colspan="10">
                            <x-twc::no-results-box/>
                        </td>
                    </x-twc::table.row>
                @endforelse
            </x-twc::table>
        </div>
        <div class="bg-table-header dark:bg-gray-900/50 flex justify-between items-center p-3">
            <div>
                <x-twc::form.elements.select name="perpage" wire:model.live="perPage">
                    <option>5</option>
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </x-twc::form.elements.select>
            </div>
            <div wire:loading.class="animate-pulse">
                @if($results->hasPages())
                    {{ $results->onEachSide(2)->links('twc::components.tailwind-paginator') }}
                @endif
            </div>
        </div>
    </x-twc::card>

    @if($this->getConfig()->hasSavedFilters())
        <x-twc::modals.custom id="saveFilterModal" title="{{ __('supertable::messages.save_filter') }}">
            <div class="p-4">
                <x-twc::form.elements.text
                    id="filterName"
                    name="filterName"
                    wire:model="filterName"
                    placeholder="{{ __('supertable::messages.filter_name') }}"
                    autofocus
                />
                @error('filterName')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="p-4 flex justify-center gap-3">
                <x-twc::link-button type="button" color="primary" wire:click="saveCustomFilter">
                    <i class="fa-solid fa-save me-2"></i>{{ __('supertable::messages.save_filter') }}
                </x-twc::link-button>
                <x-twc::link-button
                    type="button"
                    color="light"
                    @click.prevent="$dispatch('modal_custom_close', 'saveFilterModal')"
                    wire:click="closeSaveFilterModal"
                >
                    {{ __('supertable::messages.cancel') }}
                </x-twc::link-button>
            </div>
        </x-twc::modals.custom>
    @endif
</div>
