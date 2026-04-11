<?php

namespace App\View\Components\Seo;

use App\Support\Seo\SeoData;
use Illuminate\View\Component;
use Illuminate\View\View;

class MetaTags extends Component
{
    public function __construct(public SeoData $seo) {}

    public function render(): View
    {
        return view('components.seo.meta-tags');
    }

    public function fullTitle(): string
    {
        $siteName = (string) config('seo.site_name');
        $title = trim($this->seo->title);

        if ($title === '' || $title === $siteName) {
            return $siteName;
        }

        return $title . ' — ' . $siteName;
    }

    public function absoluteOgImage(): ?string
    {
        $img = $this->seo->ogImage;
        if ($img === null || $img === '') {
            return null;
        }
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            return $img;
        }
        return rtrim((string) config('app.url'), '/') . '/' . ltrim($img, '/');
    }
}
