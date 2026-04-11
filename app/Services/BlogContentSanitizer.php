<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Log;

class BlogContentSanitizer
{
    /** Below this ratio (kept/original bytes), emit a warning log. */
    private const SIGNIFICANT_STRIP_RATIO = 0.7;

    private HTMLPurifier $purifier;

    public function __construct()
    {
        $this->purifier = new HTMLPurifier($this->buildConfig());
    }

    public function clean(string $rawHtml): string
    {
        if ($rawHtml === '') {
            return '';
        }

        $before = strlen($rawHtml);
        $clean  = $this->purifier->purify($rawHtml);
        $after  = strlen($clean);

        if ($before > 0 && $after < $before * self::SIGNIFICANT_STRIP_RATIO) {
            Log::warning('BlogContentSanitizer stripped significant content', [
                'before_bytes' => $before,
                'after_bytes'  => $after,
                'ratio'        => round($after / $before, 3),
            ]);
        }

        return $clean;
    }

    private function buildConfig(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();

        $cachePath = storage_path('app/purifier');
        if (! is_dir($cachePath)) {
            @mkdir($cachePath, 0755, true);
        }

        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Core.EscapeNonASCIICharacters', false);

        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set(
            'HTML.Allowed',
            'p,br,hr,h2,h3,h4,strong,em,s,u,code,'.
            'ul,ol,li,blockquote,pre,'.
            'a[href|rel|target],'.
            'img[src|alt|title|width|height],'.
            'iframe[src|width|height|frameborder],'.
            'table,thead,tbody,tr,th[colspan|rowspan],td[colspan|rowspan]'
        );

        // Whitelisted YouTube + Vimeo iframes
        $config->set('HTML.SafeIframe', true);
        $config->set(
            'URI.SafeIframeRegexp',
            '%^(https://www\.youtube\.com/embed/|https://player\.vimeo\.com/video/)%'
        );

        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);

        $config->set('Attr.AllowedRel', ['nofollow', 'noopener', 'noreferrer']);
        $config->set('URI.AllowedSchemes', [
            'http'   => true,
            'https'  => true,
            'mailto' => true,
        ]);

        // No inline CSS at all
        $config->set('CSS.AllowedProperties', []);

        // Force rel="noopener noreferrer" and target="_blank" behavior is
        // left to the editor; sanitizer only enforces the whitelist.
        return $config;
    }
}
