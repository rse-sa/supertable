<?php

namespace RSE\SuperTable;

use Closure;
use Illuminate\Support\HtmlString;

class AnchorColumn extends Column
{
    public string|Closure|null $link = null;

    public function link(string|Closure $href): static
    {
        $this->link = $href;

        return $this;
    }

    public function getLabel(): string
    {
        return '';
    }

    public function render(): string|HtmlString
    {
        return new HtmlString(
            '<div class="flex justify-end">
                                        <a href="' . $this->evaluate('link') . '" class="inline-block p-2 px-4 rounded-lg hover:bg-slate-200 dark:hover:bg-gray-900">
                                            <i class="fa fa-chevron-left ltr:rotate-180"></i>
                                        </a>
                                    </div>'
        );
    }
}
