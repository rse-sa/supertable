<div>
    <x-twc::card class="mb-5">
        @if($config->getTitle() || $config->hasExports())
            <x-slot:header>
                <div class="w-full flex items-center justify-between">
                    <div class="text-primary-700 font-bold">
                        @if($config->getIcon())
                            <i class="{{ $config->getIcon() }} me-2"></i>
                        @endif
                        {{ $config->getTitle() }}
                    </div>

                    @if($config->hasExports())
                        <x-twc::dropdown title="{{ __('supertable::messages.export') }}" icon="fa-solid fa-file-export" caret>
                            @foreach($config->getExports() as $export)
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
                </div>
            </x-slot:header>
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

            <div wire:key="simple-table-{{ $this->tableName }}">
                @include($config->getViewName(), ['results' => $results, 'tableName' => $this->tableName])
            </div>
        </div>

        @if($results->hasPages())
            <div class="bg-table-header dark:bg-gray-900/50 flex justify-between items-center p-3">
                <div wire:loading.class="animate-pulse">
                    {{ $results->onEachSide(2)->links('twc::components.tailwind-paginator') }}
                </div>
            </div>
        @endif
    </x-twc::card>
</div>
