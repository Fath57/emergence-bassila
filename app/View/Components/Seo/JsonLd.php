<?php

namespace App\View\Components\Seo;

use Illuminate\View\Component;
use Illuminate\View\View;

class JsonLd extends Component
{
    public array $blocks;

    /**
     * @param array $data A single Schema.org block (associative array) or an array of blocks.
     */
    public function __construct(array $data)
    {
        // Normalize: if $data looks like a list of blocks (list of arrays), use as-is;
        // otherwise wrap in a single-element list.
        $this->blocks = array_is_list($data) && isset($data[0]) && is_array($data[0])
            ? $data
            : [$data];
    }

    public function render(): View
    {
        return view('components.seo.json-ld');
    }

    public function encode(array $block): string
    {
        $json = json_encode(
            $block,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        // Escape `<` to prevent HTML-in-JSON injection (e.g. </script>).
        return str_replace('<', '\u003c', $json);
    }
}
