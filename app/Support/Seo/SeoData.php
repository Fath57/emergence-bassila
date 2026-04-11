<?php

namespace App\Support\Seo;

final class SeoData
{
    public function __construct(
        public readonly string  $title,
        public readonly string  $description,
        public readonly string  $canonical,
        public readonly string  $ogType = 'website',
        public readonly ?string $ogImage = null,
        public readonly ?string $ogImageAlt = null,
        public readonly bool    $noindex = false,
        public readonly string  $locale = 'fr_FR',
        public readonly array   $articleMeta = [],
    ) {}

    public static function default(): self
    {
        return new self(
            title:       (string) config('seo.default_title'),
            description: (string) config('seo.default_description'),
            canonical:   '',
            ogType:      'website',
            ogImage:     (string) config('seo.default_og_image'),
            ogImageAlt:  null,
            noindex:     false,
            locale:      (string) config('seo.locale', 'fr_FR'),
            articleMeta: [],
        );
    }

    public function withTitle(string $title): self
    {
        return $this->clone(['title' => $title]);
    }

    public function withDescription(string $description): self
    {
        return $this->clone(['description' => $description]);
    }

    public function withCanonical(string $url): self
    {
        $absolute = str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : rtrim((string) config('app.url'), '/') . '/' . ltrim($url, '/');

        return $this->clone(['canonical' => $absolute]);
    }

    public function withOgType(string $type): self
    {
        return $this->clone(['ogType' => $type]);
    }

    public function withOgImage(?string $url, ?string $alt = null): self
    {
        if ($url === null) {
            $url = (string) config('seo.default_og_image');
        }

        return $this->clone([
            'ogImage'    => $url,
            'ogImageAlt' => $alt,
        ]);
    }

    public function withNoindex(bool $noindex = true): self
    {
        return $this->clone(['noindex' => $noindex]);
    }

    public function withArticleMeta(array $meta): self
    {
        return $this->clone(['articleMeta' => $meta]);
    }

    private function clone(array $overrides): self
    {
        return new self(
            title:       $overrides['title']       ?? $this->title,
            description: $overrides['description'] ?? $this->description,
            canonical:   $overrides['canonical']   ?? $this->canonical,
            ogType:      $overrides['ogType']      ?? $this->ogType,
            ogImage:     array_key_exists('ogImage', $overrides)    ? $overrides['ogImage']    : $this->ogImage,
            ogImageAlt:  array_key_exists('ogImageAlt', $overrides) ? $overrides['ogImageAlt'] : $this->ogImageAlt,
            noindex:     $overrides['noindex']     ?? $this->noindex,
            locale:      $overrides['locale']      ?? $this->locale,
            articleMeta: $overrides['articleMeta'] ?? $this->articleMeta,
        );
    }
}
