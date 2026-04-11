<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Profile;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('seo.sitemap', 3600, fn () => $this->build());

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function build(): string
    {
        $urls = [];

        $lastPost    = BlogPost::published()->max('updated_at');
        $lastProfile = Profile::verified()->max('updated_at');
        $now         = now();

        // Home
        $urls[] = [
            'loc'        => url('/'),
            'lastmod'    => $this->maxDate([$lastPost, $lastProfile, $now]),
            'changefreq' => 'daily',
            'priority'   => '1.0',
        ];

        // Blog index
        $urls[] = [
            'loc'        => url('/blog'),
            'lastmod'    => $this->iso($lastPost ?? $now),
            'changefreq' => 'daily',
            'priority'   => '0.9',
        ];

        // Directory
        $urls[] = [
            'loc'        => url('/annuaire'),
            'lastmod'    => $this->iso($lastProfile ?? $now),
            'changefreq' => 'daily',
            'priority'   => '0.9',
        ];

        // Static content pages
        $staticLastmod = (string) config('seo.static_pages_lastmod', now()->toDateString());
        foreach (['/a-propos-de-bassila', '/qui-sommes-nous'] as $path) {
            $urls[] = [
                'loc'        => url($path),
                'lastmod'    => $staticLastmod,
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ];
        }

        // Published blog posts
        BlogPost::published()
            ->select('slug', 'updated_at')
            ->orderByDesc('updated_at')
            ->chunk(500, function ($chunk) use (&$urls) {
                foreach ($chunk as $post) {
                    $urls[] = [
                        'loc'        => url('/blog/' . $post->slug),
                        'lastmod'    => $this->iso($post->updated_at),
                        'changefreq' => 'weekly',
                        'priority'   => '0.8',
                    ];
                }
            });

        // Verified profiles
        Profile::verified()
            ->select('id', 'updated_at')
            ->orderByDesc('updated_at')
            ->chunk(500, function ($chunk) use (&$urls) {
                foreach ($chunk as $profile) {
                    $urls[] = [
                        'loc'        => url('/profils/' . $profile->id),
                        'lastmod'    => $this->iso($profile->updated_at),
                        'changefreq' => 'monthly',
                        'priority'   => '0.7',
                    ];
                }
            });

        return $this->render($urls);
    }

    private function render(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>' . "\n";
        return $xml;
    }

    private function iso(Carbon|string|null $date): string
    {
        if ($date === null) {
            return now()->toIso8601String();
        }
        if (is_string($date)) {
            return Carbon::parse($date)->toIso8601String();
        }
        return $date->toIso8601String();
    }

    private function maxDate(array $dates): string
    {
        $valid = array_filter($dates, fn ($d) => $d !== null);
        if ($valid === []) {
            return now()->toIso8601String();
        }
        $max = null;
        foreach ($valid as $d) {
            $c = $d instanceof Carbon ? $d : Carbon::parse($d);
            if ($max === null || $c->greaterThan($max)) {
                $max = $c;
            }
        }
        return $max->toIso8601String();
    }
}
