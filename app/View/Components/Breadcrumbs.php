<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Breadcrumbs extends Component
{
    /**
     * @param array<int, array{name:string,url:?string}> $items
     */
    public function __construct(
        public array $items = [],
        public bool  $withJsonLd = false,
    ) {}

    public function render(): View
    {
        return view('components.breadcrumbs');
    }
}
