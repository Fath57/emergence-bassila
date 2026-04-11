<?php

namespace App\Support\Seo;

use App\Models\BlogPost;
use App\Models\Profile;
use InvalidArgumentException;

final class StructuredData
{
    public static function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => (string) config('seo.site_name'),
            'url'      => rtrim((string) config('app.url'), '/'),
            'logo'     => self::absoluteUrl('/images/logo.png'),
            'sameAs'   => array_values((array) config('seo.socials', [])),
        ];
    }

    public static function website(): array
    {
        $base = rtrim((string) config('app.url'), '/');

        return [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => (string) config('seo.site_name'),
            'url'      => $base,
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $base . '/annuaire?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function article(BlogPost $post): array
    {
        $image = $post->featured_image_url ?: self::absoluteUrl((string) config('seo.default_og_image'));

        $data = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => mb_substr((string) $post->title, 0, 110),
            'description'      => $post->resolved_meta_description,
            'image'            => $image,
            'datePublished'    => optional($post->published_at)->toIso8601String(),
            'dateModified'     => optional($post->updated_at)->toIso8601String(),
            'mainEntityOfPage' => self::absoluteUrl('/blog/' . $post->slug),
            'publisher' => [
                '@type' => 'Organization',
                'name'  => (string) config('seo.site_name'),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => self::absoluteUrl('/images/logo.png'),
                ],
            ],
        ];

        $profile = $post->user?->profile;
        if ($profile) {
            $data['author'] = [
                '@type' => 'Person',
                'name'  => trim($profile->first_name . ' ' . $profile->last_name),
                'url'   => self::absoluteUrl('/profils/' . $profile->id),
            ];
        }

        if ($post->category) {
            $data['articleSection'] = $post->category->name;
        }

        return self::compact($data);
    }

    public static function person(Profile $profile): array
    {
        if (! $profile->is_verified) {
            throw new InvalidArgumentException('Person structured data can only be emitted for verified profiles.');
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => trim($profile->first_name . ' ' . $profile->last_name),
            'jobTitle' => $profile->job_title ?: null,
            'image'    => $profile->avatar_url ?: null,
            'url'      => self::absoluteUrl('/profils/' . $profile->id),
        ];

        if ($profile->city || $profile->country) {
            $data['address'] = [
                '@type' => 'PostalAddress',
            ];
            if ($profile->city) {
                $data['address']['addressLocality'] = $profile->city;
            }
            if ($profile->country) {
                $data['address']['addressCountry'] = $profile->country;
            }
        }

        return self::compact($data);
    }

    /**
     * @param array<int, array{name:string,url:?string}> $items
     */
    public static function breadcrumb(array $items): array
    {
        $list = [];
        foreach (array_values($items) as $i => $item) {
            $entry = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => (string) $item['name'],
            ];
            if (! empty($item['url'])) {
                $entry['item'] = $item['url'];
            }
            $list[] = $entry;
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    private static function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Recursively remove null and empty-array values.
     */
    private static function compact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = self::compact($value);
                if ($value === []) {
                    unset($data[$key]);
                } else {
                    $data[$key] = $value;
                }
            } elseif ($value === null) {
                unset($data[$key]);
            }
        }
        return $data;
    }
}
