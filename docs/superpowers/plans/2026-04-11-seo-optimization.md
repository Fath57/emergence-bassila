# SEO Optimization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver complete SEO foundations for Bassila Émergence: sitemap, canonical URLs, Schema.org structured data, breadcrumbs, unified meta tags via a `SeoData` DTO, two new content pages, Core Web Vitals improvements, and Google Search Console hookup.

**Architecture:** A readonly `SeoData` DTO carries all meta info; a single `<x-seo.meta-tags>` component renders `<title>`, `<meta>`, `<link rel=canonical>`, Open Graph and Twitter. A `StructuredData` factory returns Schema.org arrays, rendered via `<x-seo.json-ld>`. A `<x-breadcrumbs>` component optionally emits matching `BreadcrumbList` JSON-LD via `@once`. Sitemap is served by `SitemapController` (cached 1 hour). Two new static pages (`/a-propos-de-bassila`, `/qui-sommes-nous`) are routed with `Route::view()`. Tracking is Google Search Console only — no analytics, no cookies, no banner.

**Tech Stack:** PHP 8.3, Laravel 13, Livewire 4, Pest 4, Tailwind (existing). All SEO code is plain Blade + Laravel — no new dependencies.

**Spec:** `docs/superpowers/specs/2026-04-11-seo-optimization-design.md`

---

## File Map

**New files:**

- `config/seo.php` — centralized SEO config (site name, default title/description, default OG image, locale, `GOOGLE_SITE_VERIFICATION`, socials)
- `app/Support/Seo/SeoData.php` — readonly DTO with fluent `withXxx()` methods
- `app/Support/Seo/StructuredData.php` — factory: `organization()`, `website()`, `article()`, `person()`, `breadcrumb()`
- `app/View/Components/Seo/MetaTags.php` — Blade component class
- `app/View/Components/Seo/JsonLd.php` — Blade component class
- `app/View/Components/Breadcrumbs.php` — Blade component class
- `app/Http/Controllers/SitemapController.php` — `GET /sitemap.xml`
- `resources/views/components/seo/meta-tags.blade.php` — meta tags template
- `resources/views/components/seo/json-ld.blade.php` — JSON-LD template
- `resources/views/components/breadcrumbs.blade.php` — breadcrumb template
- `resources/views/pages/a-propos-de-bassila.blade.php` — content page about Bassila
- `resources/views/pages/qui-sommes-nous.blade.php` — content page about the platform
- `public/images/og-default.png` — 1200×630 brand-coloured OG fallback
- `public/images/home/hero-community.{jpg,webp}` — replaces Unsplash hero
- `public/images/home/mission.{jpg,webp}` — replaces Unsplash mission image
- `tests/Unit/Seo/SeoDataTest.php`
- `tests/Unit/Seo/StructuredDataTest.php`
- `tests/Feature/Seo/SitemapTest.php`
- `tests/Feature/Seo/RobotsTxtTest.php`
- `tests/Feature/Seo/MetaTagsTest.php`
- `tests/Feature/Seo/JsonLdTest.php`
- `tests/Feature/Seo/BreadcrumbsTest.php`
- `tests/Feature/Seo/PerformanceHintsTest.php`
- `tests/Feature/Seo/StaticPagesTest.php`

**Modified files:**

- `routes/web.php` — add `/sitemap.xml`, `/a-propos-de-bassila`, `/qui-sommes-nous`
- `resources/views/layouts/app.blade.php` — refactor head to use `<x-seo.meta-tags>`
- `resources/views/welcome.blade.php` — `$seo` DTO + Organization/WebSite JSON-LD + local hero images + width/height
- `resources/views/blog/index.blade.php` — `$seo` DTO
- `resources/views/blog/show.blade.php` — `$seo` DTO, Article JSON-LD, breadcrumb component, width/height
- `resources/views/profile/show.blade.php` — `$seo` DTO, Person JSON-LD (if verified), breadcrumb component, width/height
- `resources/views/livewire/directory/search-directory.blade.php` — `$seo` DTO
- `resources/views/livewire/auth/*.blade.php` — `$seo` with `noindex=true`
- `resources/views/livewire/profile/create-profile.blade.php`, `edit-profile.blade.php` — `noindex=true`
- `resources/views/livewire/blog/create-post.blade.php`, `edit-post.blade.php`, `my-posts.blade.php`, `preview-post.blade.php` — `noindex=true`
- `resources/views/partials/nav.blade.php` — add "À propos" link
- `resources/views/partials/footer.blade.php` — add "À propos" column
- `public/robots.txt` — rewrite with explicit Disallow rules + hard-coded Sitemap URL
- `nginx_app.conf` — add location block for `/build/*` cache-control
- `.env.example` — add `GOOGLE_SITE_VERIFICATION=` placeholder

---

## Task 1: Config file + environment scaffold

**Files:**
- Create: `config/seo.php`
- Modify: `.env.example`

- [ ] **Step 1: Create `config/seo.php`**

```php
<?php

return [
    'site_name' => 'Bassila Émergence',

    'default_title' => 'Le réseau des Bassilais à travers le monde',

    'default_description' => 'La plateforme de networking des Bassilais à travers le monde. Retrouvez d\'anciens camarades, développez votre réseau professionnel et contribuez à l\'histoire de votre communauté d\'origine.',

    'default_og_image' => '/images/og-default.png',

    'locale' => 'fr_FR',

    'google_verification' => env('GOOGLE_SITE_VERIFICATION'),

    'socials' => [
        // e.g. 'https://www.facebook.com/bassila-emergence',
    ],

    'static_pages_lastmod' => '2026-04-11',
];
```

- [ ] **Step 2: Append to `.env.example`**

Add these two lines at the end of `.env.example`:

```
# SEO — Google Search Console verification token (meta tag method)
GOOGLE_SITE_VERIFICATION=
```

- [ ] **Step 3: Commit**

```bash
git add config/seo.php .env.example
git commit -m "feat(seo): add SEO config file and env scaffold"
```

---

## Task 2: SeoData DTO with unit tests

**Files:**
- Create: `app/Support/Seo/SeoData.php`
- Test: `tests/Unit/Seo/SeoDataTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Seo/SeoDataTest.php`:

```php
<?php

use App\Support\Seo\SeoData;

it('builds a default instance from config', function () {
    config()->set('seo.default_title', 'Cfg Title');
    config()->set('seo.default_description', 'Cfg desc');
    config()->set('seo.default_og_image', '/images/og-default.png');
    config()->set('seo.locale', 'fr_FR');

    $seo = SeoData::default();

    expect($seo->title)->toBe('Cfg Title')
        ->and($seo->description)->toBe('Cfg desc')
        ->and($seo->ogImage)->toBe('/images/og-default.png')
        ->and($seo->locale)->toBe('fr_FR')
        ->and($seo->ogType)->toBe('website')
        ->and($seo->noindex)->toBeFalse()
        ->and($seo->canonical)->toBe('');
});

it('withTitle returns a new instance with updated title', function () {
    $a = SeoData::default();
    $b = $a->withTitle('New Title');

    expect($b)->not->toBe($a)
        ->and($b->title)->toBe('New Title')
        ->and($a->title)->not->toBe('New Title');
});

it('withDescription returns a new instance', function () {
    $a = SeoData::default()->withDescription('New desc');
    expect($a->description)->toBe('New desc');
});

it('withCanonical converts relative path to absolute URL', function () {
    config()->set('app.url', 'https://example.test');

    $seo = SeoData::default()->withCanonical('/a-propos-de-bassila');

    expect($seo->canonical)->toBe('https://example.test/a-propos-de-bassila');
});

it('withCanonical accepts an absolute URL unchanged', function () {
    $seo = SeoData::default()->withCanonical('https://example.test/blog/foo');
    expect($seo->canonical)->toBe('https://example.test/blog/foo');
});

it('withOgType updates og type', function () {
    $seo = SeoData::default()->withOgType('article');
    expect($seo->ogType)->toBe('article');
});

it('withOgImage accepts optional alt text', function () {
    $seo = SeoData::default()->withOgImage('/img/x.png', 'Alt text');
    expect($seo->ogImage)->toBe('/img/x.png')
        ->and($seo->ogImageAlt)->toBe('Alt text');
});

it('withOgImage(null) resets to default og image', function () {
    $seo = SeoData::default()
        ->withOgImage('/custom.png')
        ->withOgImage(null);
    expect($seo->ogImage)->toBe('/images/og-default.png');
});

it('withNoindex flips the noindex flag', function () {
    $seo = SeoData::default()->withNoindex();
    expect($seo->noindex)->toBeTrue();

    $seo2 = $seo->withNoindex(false);
    expect($seo2->noindex)->toBeFalse();
});

it('withArticleMeta stores article metadata', function () {
    $seo = SeoData::default()->withArticleMeta([
        'publishedTime' => '2026-04-11T10:00:00+00:00',
        'author' => 'Jane Doe',
    ]);
    expect($seo->articleMeta)->toBe([
        'publishedTime' => '2026-04-11T10:00:00+00:00',
        'author' => 'Jane Doe',
    ]);
});
```

- [ ] **Step 2: Run tests to verify failure**

```bash
php artisan test --filter=SeoDataTest
```

Expected: tests fail with "Class App\Support\Seo\SeoData not found".

- [ ] **Step 3: Implement `SeoData`**

Create `app/Support/Seo/SeoData.php`:

```php
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
```

- [ ] **Step 4: Run tests to verify pass**

```bash
php artisan test --filter=SeoDataTest
```

Expected: all SeoData tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Support/Seo/SeoData.php tests/Unit/Seo/SeoDataTest.php
git commit -m "feat(seo): add SeoData DTO with fluent with* methods"
```

---

## Task 3: StructuredData factory with unit tests

**Files:**
- Create: `app/Support/Seo/StructuredData.php`
- Test: `tests/Unit/Seo/StructuredDataTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Unit/Seo/StructuredDataTest.php`:

```php
<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Profile;
use App\Models\User;
use App\Support\Seo\StructuredData;

beforeEach(function () {
    config()->set('app.url', 'https://example.test');
    config()->set('seo.site_name', 'Bassila Émergence');
    config()->set('seo.default_og_image', '/images/og-default.png');
});

it('organization() returns a valid Organization payload', function () {
    config()->set('seo.socials', ['https://facebook.com/example']);

    $data = StructuredData::organization();

    expect($data['@context'])->toBe('https://schema.org')
        ->and($data['@type'])->toBe('Organization')
        ->and($data['name'])->toBe('Bassila Émergence')
        ->and($data['url'])->toBe('https://example.test')
        ->and($data['logo'])->toBe('https://example.test/images/logo.png')
        ->and($data['sameAs'])->toBe(['https://facebook.com/example']);
});

it('website() exposes a SearchAction pointing to /annuaire', function () {
    $data = StructuredData::website();

    expect($data['@type'])->toBe('WebSite')
        ->and($data['potentialAction']['@type'])->toBe('SearchAction')
        ->and($data['potentialAction']['target'])
        ->toBe('https://example.test/annuaire?q={search_term_string}')
        ->and($data['potentialAction']['query-input'])->toBe('required name=search_term_string');
});

it('article() returns an Article payload with ISO 8601 dates', function () {
    $user = User::factory()->create();
    Profile::factory()->verified()->create([
        'user_id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);
    $category = BlogCategory::factory()->create(['name' => 'Actualités']);
    $post = BlogPost::factory()->published()->create([
        'user_id' => $user->id,
        'title' => 'My post',
        'slug' => 'my-post',
        'category_id' => $category->id,
        'featured_image_url' => 'https://example.test/img/hero.jpg',
    ]);

    $data = StructuredData::article($post);

    expect($data['@type'])->toBe('Article')
        ->and($data['headline'])->toBe('My post')
        ->and($data['image'])->toBe('https://example.test/img/hero.jpg')
        ->and($data['datePublished'])->toMatch('/^\d{4}-\d{2}-\d{2}T/')
        ->and($data['dateModified'])->toMatch('/^\d{4}-\d{2}-\d{2}T/')
        ->and($data['author']['@type'])->toBe('Person')
        ->and($data['author']['name'])->toBe('Jane Doe')
        ->and($data['publisher']['@type'])->toBe('Organization')
        ->and($data['publisher']['name'])->toBe('Bassila Émergence')
        ->and($data['articleSection'])->toBe('Actualités')
        ->and($data['mainEntityOfPage'])->toBe('https://example.test/blog/my-post');
});

it('article() falls back to default OG image when no featured image', function () {
    $post = BlogPost::factory()->published()->create([
        'featured_image_url' => null,
    ]);

    $data = StructuredData::article($post);

    expect($data['image'])->toBe('https://example.test/images/og-default.png');
});

it('person() returns a Person payload for a verified profile', function () {
    $profile = Profile::factory()->verified()->create([
        'first_name' => 'Amina',
        'last_name'  => 'Traoré',
        'job_title'  => 'Médecin',
        'city'       => 'Cotonou',
        'country'    => 'Bénin',
        'avatar_url' => 'https://example.test/avatar.jpg',
    ]);

    $data = StructuredData::person($profile);

    expect($data['@type'])->toBe('Person')
        ->and($data['name'])->toBe('Amina Traoré')
        ->and($data['jobTitle'])->toBe('Médecin')
        ->and($data['image'])->toBe('https://example.test/avatar.jpg')
        ->and($data['url'])->toBe("https://example.test/profils/{$profile->id}")
        ->and($data['address']['@type'])->toBe('PostalAddress')
        ->and($data['address']['addressLocality'])->toBe('Cotonou')
        ->and($data['address']['addressCountry'])->toBe('Bénin');
});

it('person() omits null fields', function () {
    $profile = Profile::factory()->verified()->create([
        'job_title' => null,
        'avatar_url' => null,
        'city' => null,
        'country' => null,
    ]);

    $data = StructuredData::person($profile);

    expect($data)->not->toHaveKey('jobTitle')
        ->and($data)->not->toHaveKey('image')
        ->and($data)->not->toHaveKey('address');
});

it('person() throws for an unverified profile', function () {
    $profile = Profile::factory()->create(['is_verified' => false]);

    StructuredData::person($profile);
})->throws(InvalidArgumentException::class);

it('breadcrumb() numbers items starting at position 1', function () {
    $data = StructuredData::breadcrumb([
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Blog',    'url' => 'https://example.test/blog'],
        ['name' => 'Mon article', 'url' => null],
    ]);

    expect($data['@type'])->toBe('BreadcrumbList')
        ->and($data['itemListElement'])->toHaveCount(3)
        ->and($data['itemListElement'][0]['position'])->toBe(1)
        ->and($data['itemListElement'][0]['name'])->toBe('Accueil')
        ->and($data['itemListElement'][0]['item'])->toBe('https://example.test/')
        ->and($data['itemListElement'][2]['position'])->toBe(3)
        ->and($data['itemListElement'][2])->not->toHaveKey('item');
});
```

- [ ] **Step 2: Run tests to verify failure**

```bash
php artisan test --filter=StructuredDataTest
```

Expected: tests fail with "Class StructuredData not found".

- [ ] **Step 3: Implement `StructuredData`**

Create `app/Support/Seo/StructuredData.php`:

```php
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
            'sameAs'   => [],
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

        // Omit empty sameAs arrays
        if ($data['sameAs'] === []) {
            unset($data['sameAs']);
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
```

- [ ] **Step 4: Run tests to verify pass**

```bash
php artisan test --filter=StructuredDataTest
```

Expected: all tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Support/Seo/StructuredData.php tests/Unit/Seo/StructuredDataTest.php
git commit -m "feat(seo): add StructuredData factory for Schema.org payloads"
```

---

## Task 4: MetaTags Blade component

**Files:**
- Create: `app/View/Components/Seo/MetaTags.php`
- Create: `resources/views/components/seo/meta-tags.blade.php`
- Test: *(covered later by MetaTagsTest feature tests once layout is wired)*

- [ ] **Step 1: Create the component class**

Create `app/View/Components/Seo/MetaTags.php`:

```php
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
```

- [ ] **Step 2: Create the Blade template**

Create `resources/views/components/seo/meta-tags.blade.php`:

```blade
<title>{{ $fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}">
<link rel="canonical" href="{{ $seo->canonical }}">

@if ($seo->noindex)
    <meta name="robots" content="noindex, nofollow">
@endif

@if (config('seo.google_verification'))
    <meta name="google-site-verification" content="{{ config('seo.google_verification') }}">
@endif

{{-- Open Graph --}}
<meta property="og:title" content="{{ $fullTitle() }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:locale" content="{{ $seo->locale }}">

@php $ogImage = $absoluteOgImage(); @endphp
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @if ($seo->ogImageAlt)
        <meta property="og:image:alt" content="{{ $seo->ogImageAlt }}">
    @endif
@endif

@if ($seo->ogType === 'article' && ! empty($seo->articleMeta))
    @isset ($seo->articleMeta['publishedTime'])
        <meta property="article:published_time" content="{{ $seo->articleMeta['publishedTime'] }}">
    @endisset
    @isset ($seo->articleMeta['modifiedTime'])
        <meta property="article:modified_time" content="{{ $seo->articleMeta['modifiedTime'] }}">
    @endisset
    @isset ($seo->articleMeta['author'])
        <meta property="article:author" content="{{ $seo->articleMeta['author'] }}">
    @endisset
    @isset ($seo->articleMeta['section'])
        <meta property="article:section" content="{{ $seo->articleMeta['section'] }}">
    @endisset
    @isset ($seo->articleMeta['tags'])
        @foreach ((array) $seo->articleMeta['tags'] as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endisset
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->description }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
    @if ($seo->ogImageAlt)
        <meta name="twitter:image:alt" content="{{ $seo->ogImageAlt }}">
    @endif
@endif
```

- [ ] **Step 3: Verify component auto-discovers**

Laravel auto-discovers components under `App\View\Components`. No service provider change needed. Run a quick render test to confirm:

```bash
php artisan view:clear
```

- [ ] **Step 4: Commit**

```bash
git add app/View/Components/Seo/MetaTags.php resources/views/components/seo/meta-tags.blade.php
git commit -m "feat(seo): add x-seo.meta-tags Blade component"
```

---

## Task 5: JsonLd Blade component

**Files:**
- Create: `app/View/Components/Seo/JsonLd.php`
- Create: `resources/views/components/seo/json-ld.blade.php`

- [ ] **Step 1: Create the component class**

Create `app/View/Components/Seo/JsonLd.php`:

```php
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
        // Normalize to list of blocks
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
```

- [ ] **Step 2: Create the Blade template**

Create `resources/views/components/seo/json-ld.blade.php`:

```blade
@foreach ($blocks as $block)
<script type="application/ld+json">{!! $encode($block) !!}</script>
@endforeach
```

- [ ] **Step 3: Commit**

```bash
git add app/View/Components/Seo/JsonLd.php resources/views/components/seo/json-ld.blade.php
git commit -m "feat(seo): add x-seo.json-ld Blade component"
```

---

## Task 6: Breadcrumbs component

**Files:**
- Create: `app/View/Components/Breadcrumbs.php`
- Create: `resources/views/components/breadcrumbs.blade.php`
- Test: `tests/Feature/Seo/BreadcrumbsTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Seo/BreadcrumbsTest.php`:

```php
<?php

use function Pest\Laravel\get;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/breadcrumb-test-without-jsonld', function () {
        return view('tests.breadcrumb-fixture', [
            'items' => [
                ['name' => 'Accueil', 'url' => url('/')],
                ['name' => 'Blog',    'url' => url('/blog')],
                ['name' => 'Mon article', 'url' => null],
            ],
            'withJsonLd' => false,
        ]);
    });

    Route::get('/breadcrumb-test-with-jsonld', function () {
        return view('tests.breadcrumb-fixture', [
            'items' => [
                ['name' => 'Accueil', 'url' => url('/')],
                ['name' => 'Blog',    'url' => url('/blog')],
                ['name' => 'Mon article', 'url' => null],
            ],
            'withJsonLd' => true,
        ]);
    });
});

it('renders 2 anchors and 1 span for a 3-item breadcrumb', function () {
    $response = get('/breadcrumb-test-without-jsonld');

    $html = $response->getContent();
    expect(substr_count($html, '<a ') >= 2)->toBeTrue()
        ->and(str_contains($html, '<span class="text-gray-700'))->toBeTrue()
        ->and(str_contains($html, 'Mon article'))->toBeTrue();
});

it('does NOT emit JSON-LD when with-json-ld is false', function () {
    $response = get('/breadcrumb-test-without-jsonld');
    expect(str_contains($response->getContent(), 'BreadcrumbList'))->toBeFalse();
});

it('emits one BreadcrumbList JSON-LD block when with-json-ld is true', function () {
    $response = get('/breadcrumb-test-with-jsonld');
    $html = $response->getContent();
    $count = substr_count($html, '"BreadcrumbList"');
    expect($count)->toBe(1);
});
```

Create the fixture view `resources/views/tests/breadcrumb-fixture.blade.php`:

```blade
<!DOCTYPE html>
<html><head>@stack('head')</head>
<body>
<x-breadcrumbs :items="$items" :with-json-ld="$withJsonLd" />
</body></html>
```

- [ ] **Step 2: Run tests to verify failure**

```bash
php artisan test --filter=BreadcrumbsTest
```

Expected: fails with "Unable to locate component [breadcrumbs]".

- [ ] **Step 3: Create the component class**

Create `app/View/Components/Breadcrumbs.php`:

```php
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
```

- [ ] **Step 4: Create the Blade template**

Create `resources/views/components/breadcrumbs.blade.php`:

```blade
@if (! empty($items))
<nav aria-label="Fil d'ariane" class="mb-6 text-sm text-gray-400 flex items-center gap-2 flex-wrap">
    @foreach ($items as $item)
        @if (! empty($item['url']))
            <a href="{{ $item['url'] }}" class="hover:text-[#0066CC] transition" wire:navigate>{{ $item['name'] }}</a>
        @else
            <span class="text-gray-700 truncate">{{ $item['name'] }}</span>
        @endif
        @unless ($loop->last)
            <span aria-hidden="true">/</span>
        @endunless
    @endforeach
</nav>

@if ($withJsonLd)
    @once
        @push('head')
            <x-seo.json-ld :data="\App\Support\Seo\StructuredData::breadcrumb($items)" />
        @endpush
    @endonce
@endif
@endif
```

- [ ] **Step 5: Run tests to verify pass**

```bash
php artisan test --filter=BreadcrumbsTest
```

Expected: all 3 tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/View/Components/Breadcrumbs.php resources/views/components/breadcrumbs.blade.php resources/views/tests/breadcrumb-fixture.blade.php tests/Feature/Seo/BreadcrumbsTest.php
git commit -m "feat(seo): add x-breadcrumbs component with optional BreadcrumbList JSON-LD"
```

---

## Task 7: Refactor `layouts/app.blade.php` to use `<x-seo.meta-tags>`

**Files:**
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Replace the `<head>` meta block**

Replace lines 4-34 (from `<meta charset>` through the `@stack('head')` line) — keep the charset, viewport, csrf-token, font links, Vite, Livewire, and stack. Only the SEO-related block changes.

New `<head>` contents:

```blade
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        use App\Support\Seo\SeoData;

        $seo = ($seo ?? SeoData::default())
            ->withCanonical(url()->current())
            ->withTitle(
                $__env->yieldContent('title') !== ''
                    ? $__env->yieldContent('title')
                    : ($seo->title ?? config('seo.default_title'))
            )
            ->withDescription(
                $__env->yieldContent('description') !== ''
                    ? $__env->yieldContent('description')
                    : ($seo->description ?? config('seo.default_description'))
            );
    @endphp

    <x-seo.meta-tags :seo="$seo" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')

    <style>
        body { background-color: #F9FAFB; }
    </style>
</head>
```

- [ ] **Step 2: Smoke-test existing views still render**

```bash
php artisan test --filter=HomePageTest
```

Expected: all existing home page tests pass. They use `@section('title')` / `@section('description')` which the refactor preserves via the yieldContent fallback.

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/app.blade.php
git commit -m "refactor(layout): delegate meta tags to x-seo.meta-tags component"
```

---

## Task 8: Wire welcome page with `$seo`, Organization + WebSite JSON-LD, local hero images

**Files:**
- Modify: `resources/views/welcome.blade.php`
- Create: `public/images/home/hero-community.jpg` *(placeholder — see Step 1)*
- Create: `public/images/home/hero-community.webp`
- Create: `public/images/home/mission.jpg`
- Create: `public/images/home/mission.webp`

- [ ] **Step 1: Download placeholder images and convert to webp**

Unless real community photos are available, download the two existing Unsplash URLs currently hard-coded in `welcome.blade.php:16` and `:126`, and place them locally. On a dev machine with `cwebp` installed:

```bash
mkdir -p public/images/home
curl -sSL "https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=1600&q=80" -o public/images/home/hero-community.jpg
curl -sSL "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80"  -o public/images/home/mission.jpg
cwebp -q 82 public/images/home/hero-community.jpg -o public/images/home/hero-community.webp
cwebp -q 82 public/images/home/mission.jpg        -o public/images/home/mission.webp
```

If `cwebp` is not available, install it: `sudo apt install webp` on Debian/Ubuntu; `brew install webp` on macOS. If the download fails (e.g. offline), use any 1600×1000 JPEG placeholder and commit — it's a placeholder, not a production asset.

- [ ] **Step 2: Add `$seo` DTO and Organization + WebSite JSON-LD to `welcome.blade.php`**

At the very top of `resources/views/welcome.blade.php` (before `@extends`), add:

```blade
@php
    use App\Support\Seo\SeoData;
    use App\Support\Seo\StructuredData;

    $seo = SeoData::default()
        ->withTitle('Le réseau des Bassilais à travers le monde')
        ->withDescription('Retrouvez d\'anciens camarades, développez votre réseau professionnel et contribuez à l\'histoire de votre communauté d\'origine. Bassila Émergence est la plateforme de networking des Bassilais du Bénin et de la diaspora.')
        ->withOgType('website');
@endphp
@extends('layouts.app')

@push('head')
    <x-seo.json-ld :data="App\Support\Seo\StructuredData::organization()" />
    <x-seo.json-ld :data="App\Support\Seo\StructuredData::website()" />
@endpush

@section('title', 'Le réseau des Bassilais à travers le monde')
@section('description', 'Retrouvez d\'anciens camarades, développez votre réseau professionnel et contribuez à l\'histoire de votre communauté d\'origine.')
```

The `@section` lines are left for backward compatibility with tests that grep for them. They will be cleaned up in Task 19.

- [ ] **Step 3: Replace Unsplash hero with local image + width/height + fetchpriority**

In `welcome.blade.php`, replace the `<img>` at line 16 (the hero) with:

```blade
<picture>
    <source srcset="{{ asset('images/home/hero-community.webp') }}" type="image/webp">
    <img
        src="{{ asset('images/home/hero-community.jpg') }}"
        alt="Communauté Bassilaise"
        width="1600" height="1000"
        class="w-full h-full object-cover object-center"
        loading="eager"
        fetchpriority="high"
        decoding="async"
    >
</picture>
```

And replace the mission `<img>` at line 126 with:

```blade
<picture>
    <source srcset="{{ asset('images/home/mission.webp') }}" type="image/webp">
    <img
        src="{{ asset('images/home/mission.jpg') }}"
        alt="Professionnels en réunion"
        width="900" height="675"
        class="w-full aspect-[4/3] object-cover"
        loading="lazy"
        decoding="async"
    >
</picture>
```

- [ ] **Step 4: Run existing home page tests**

```bash
php artisan test --filter=HomePageTest
```

Expected: all pass (the texts are preserved; the structure is compatible).

- [ ] **Step 5: Commit**

```bash
git add resources/views/welcome.blade.php public/images/home/
git commit -m "feat(seo): welcome page — SeoData DTO, Organization+WebSite JSON-LD, local hero images"
```

---

## Task 9: Wire blog index + blog show with SeoData, Article JSON-LD, breadcrumbs

**Files:**
- Modify: `resources/views/blog/index.blade.php`
- Modify: `resources/views/blog/show.blade.php`

- [ ] **Step 1: Modify `blog/index.blade.php` top**

Replace the existing `@section('title')` block (first lines) with:

```blade
@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Blog — Histoires & Actualités de la communauté Bassilaise')
        ->withDescription('Articles de la communauté Bassilaise : témoignages, conseils de carrière, actualités du Bénin et réflexions sur la diaspora.')
        ->withOgType('website');
@endphp
@extends('layouts.app')
@section('title', 'Blog')
@section('description', 'Articles de la communauté Bassilaise : témoignages, conseils de carrière, actualités du Bénin et réflexions sur la diaspora.')
```

- [ ] **Step 2: Modify `blog/show.blade.php` top**

Replace the current top block (lines 1-16, the `@extends`, `@section`, `@push('head')` that manually emit OG tags) with:

```blade
@php
    use App\Support\Seo\SeoData;
    use App\Support\Seo\StructuredData;

    $seo = SeoData::default()
        ->withTitle($post->resolved_meta_title)
        ->withDescription($post->resolved_meta_description)
        ->withOgType('article')
        ->withOgImage($post->featured_image_url, $post->title)
        ->withArticleMeta([
            'publishedTime' => optional($post->published_at)->toIso8601String(),
            'modifiedTime'  => optional($post->updated_at)->toIso8601String(),
            'author'        => $post->user->profile?->first_name . ' ' . $post->user->profile?->last_name,
            'section'       => $post->category?->name,
        ]);
@endphp
@extends('layouts.app')
@section('title', $post->resolved_meta_title)
@section('description', $post->resolved_meta_description)

@push('head')
    <x-seo.json-ld :data="App\Support\Seo\StructuredData::article($post)" />
@endpush

@section('content')
```

- [ ] **Step 3: Replace the manual breadcrumb with the component**

In `blog/show.blade.php`, replace lines 22-26 (the manual `<nav>` with Blog › title) with:

```blade
<x-breadcrumbs
    :items="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'Blog', 'url' => route('blog.index')],
        ['name' => $post->title, 'url' => null],
    ]"
    :with-json-ld="true"
/>
```

- [ ] **Step 4: Add `width`/`height` on the article image**

In `blog/show.blade.php`, the `<img>` rendering `$post->featured_image_url` at line 32: add `width="1200" height="630"` attributes (standard featured image aspect). If real dimensions differ, use a neutral 1200×630.

- [ ] **Step 5: Run blog tests**

```bash
php artisan test --filter=BlogPostTest
```

Expected: existing blog tests still pass.

- [ ] **Step 6: Commit**

```bash
git add resources/views/blog/index.blade.php resources/views/blog/show.blade.php
git commit -m "feat(seo): blog index+show — SeoData DTO, Article JSON-LD, breadcrumb component"
```

---

## Task 10: Wire profile show with SeoData, Person JSON-LD, breadcrumbs

**Files:**
- Modify: `resources/views/profile/show.blade.php`

- [ ] **Step 1: Inspect the current profile show view**

Run `head -30 resources/views/profile/show.blade.php` to see the current top block. Replace the existing `@extends` + `@section('title')` block with:

```blade
@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle($profile->first_name . ' ' . $profile->last_name . ($profile->job_title ? ' — ' . $profile->job_title : ''))
        ->withDescription($profile->bio ?: ($profile->first_name . ' ' . $profile->last_name . ' — membre du réseau Bassila Émergence.'))
        ->withOgType('profile')
        ->withOgImage($profile->avatar_url, $profile->first_name . ' ' . $profile->last_name);
@endphp
@extends('layouts.app')
@section('title', $profile->first_name . ' ' . $profile->last_name)
@section('description', $profile->bio ?: 'Membre du réseau Bassila Émergence')

@push('head')
    @if ($profile->is_verified)
        <x-seo.json-ld :data="App\Support\Seo\StructuredData::person($profile)" />
    @endif
@endpush

@section('content')
```

- [ ] **Step 2: Add breadcrumb at the top of the content section**

Inside the first container of the content section, add:

```blade
<x-breadcrumbs
    :items="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'Annuaire', 'url' => route('directory.index')],
        ['name' => $profile->first_name . ' ' . $profile->last_name, 'url' => null],
    ]"
    :with-json-ld="true"
/>
```

- [ ] **Step 3: Add width/height to avatar img**

Find the avatar `<img>` and add `width="160" height="160"` (or whatever the rendered size suggests). For smaller inline avatars, use `width="40" height="40"`.

- [ ] **Step 4: Run profile tests**

```bash
php artisan test --filter=Profile
```

Expected: existing profile tests still pass.

- [ ] **Step 5: Commit**

```bash
git add resources/views/profile/show.blade.php
git commit -m "feat(seo): profile show — SeoData DTO, Person JSON-LD, breadcrumb component"
```

---

## Task 11: Wire directory page with SeoData

**Files:**
- Modify: `resources/views/livewire/directory/search-directory.blade.php`

- [ ] **Step 1: Add `$seo` block**

At the very top of the file, before any existing content, add:

```blade
@php
    use App\Support\Seo\SeoData;

    $pageSeo = SeoData::default()
        ->withTitle('Annuaire des Bassilais — Diaspora et professionnels')
        ->withDescription('Retrouvez les professionnels Bassilais du Bénin et de la diaspora. Filtrez par secteur, pays, compétences et contactez directement les membres vérifiés.')
        ->withOgType('website');
@endphp
```

Note: Livewire full-page components inherit the layout. The layout reads `$seo` from its own scope; for Livewire full-page components we need a different mechanism. Instead, use `@section` at the top:

```blade
@section('title', 'Annuaire des Bassilais — Diaspora et professionnels')
@section('description', 'Retrouvez les professionnels Bassilais du Bénin et de la diaspora. Filtrez par secteur, pays, compétences et contactez directement les membres vérifiés.')
```

Place these `@section` lines outside the root element of the Livewire view (at the very top of the file). The layout's `yieldContent` fallback picks them up.

- [ ] **Step 2: Run directory tests**

```bash
php artisan test --filter=Directory
```

Expected: existing tests pass.

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/directory/search-directory.blade.php
git commit -m "feat(seo): directory page — add title and description sections"
```

---

## Task 12: Add `noindex` to auth + editing pages

**Files:**
- Modify: `resources/views/livewire/auth/login.blade.php`
- Modify: `resources/views/livewire/auth/register.blade.php`
- Modify: `resources/views/livewire/auth/forgot-password.blade.php`
- Modify: `resources/views/livewire/auth/reset-password.blade.php`
- Modify: `resources/views/livewire/auth/verify-email.blade.php`
- Modify: `resources/views/livewire/auth/accept-invitation.blade.php`
- Modify: `resources/views/livewire/profile/create-profile.blade.php`
- Modify: `resources/views/livewire/profile/edit-profile.blade.php`
- Modify: `resources/views/livewire/blog/create-post.blade.php`
- Modify: `resources/views/livewire/blog/edit-post.blade.php`
- Modify: `resources/views/livewire/blog/my-posts.blade.php`
- Modify: `resources/views/livewire/blog/preview-post.blade.php`

- [ ] **Step 1: Add noindex section + `<meta>` push to each view**

For each of the 12 views above, insert at the very top (before anything else):

```blade
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush
```

Add sensible `@section('title')` and `@section('description')` if the view does not already have them. Example for `login.blade.php`:

```blade
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush
@section('title', 'Connexion')
@section('description', 'Connectez-vous à votre compte Bassila Émergence.')
```

- [ ] **Step 2: Smoke-test auth pages render**

```bash
php artisan test --filter=Auth
```

Expected: existing auth tests pass.

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/auth/ resources/views/livewire/profile/create-profile.blade.php resources/views/livewire/profile/edit-profile.blade.php resources/views/livewire/blog/create-post.blade.php resources/views/livewire/blog/edit-post.blade.php resources/views/livewire/blog/my-posts.blade.php resources/views/livewire/blog/preview-post.blade.php
git commit -m "feat(seo): add noindex/nofollow to auth and editing pages"
```

---

## Task 13: SitemapController + route + feature tests

**Files:**
- Create: `app/Http/Controllers/SitemapController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Seo/SitemapTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Seo/SitemapTest.php`:

```php
<?php

use App\Models\BlogPost;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use function Pest\Laravel\get;

beforeEach(function () {
    Cache::forget('seo.sitemap');
});

it('serves sitemap.xml with correct content type and 200', function () {
    $response = get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('application/xml');
});

it('includes static URLs: home, blog index, directory, static pages', function () {
    $xml = get('/sitemap.xml')->getContent();

    expect($xml)->toContain('<loc>' . url('/') . '</loc>')
        ->and($xml)->toContain('<loc>' . url('/blog') . '</loc>')
        ->and($xml)->toContain('<loc>' . url('/annuaire') . '</loc>')
        ->and($xml)->toContain('<loc>' . url('/a-propos-de-bassila') . '</loc>')
        ->and($xml)->toContain('<loc>' . url('/qui-sommes-nous') . '</loc>');
});

it('includes published blog posts but not drafts', function () {
    BlogPost::factory()->published()->create(['slug' => 'visible-post']);
    BlogPost::factory()->create(['slug' => 'draft-post', 'status' => 'draft']);

    $xml = get('/sitemap.xml')->getContent();

    expect($xml)->toContain('/blog/visible-post</loc>')
        ->and($xml)->not->toContain('/blog/draft-post</loc>');
});

it('includes verified profiles but not unverified', function () {
    $visible = Profile::factory()->verified()->create();
    $hidden  = Profile::factory()->create(['is_verified' => false]);

    $xml = get('/sitemap.xml')->getContent();

    expect($xml)->toContain('/profils/' . $visible->id . '</loc>')
        ->and($xml)->not->toContain('/profils/' . $hidden->id . '</loc>');
});

it('does not contain admin or private URLs', function () {
    $xml = get('/sitemap.xml')->getContent();

    expect($xml)->not->toContain('/admin')
        ->and($xml)->not->toContain('/profil/creer')
        ->and($xml)->not->toContain('/mes-articles')
        ->and($xml)->not->toContain('/inscription')
        ->and($xml)->not->toContain('/newsletter/');
});

it('caches the sitemap output (second hit does not requery)', function () {
    BlogPost::factory()->published()->create(['slug' => 'cached-post']);

    $first = get('/sitemap.xml')->getContent();

    // If we create a new post AFTER the first hit, the cache should still serve the old one.
    BlogPost::factory()->published()->create(['slug' => 'uncached-post']);
    $second = get('/sitemap.xml')->getContent();

    expect($first)->toBe($second)
        ->and($second)->not->toContain('uncached-post');
});
```

- [ ] **Step 2: Run test — verify failure**

```bash
php artisan test --filter=SitemapTest
```

Expected: all fail (route does not exist).

- [ ] **Step 3: Implement `SitemapController`**

Create `app/Http/Controllers/SitemapController.php`:

```php
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
```

- [ ] **Step 4: Register the route**

In `routes/web.php`, add after the `/` home route (around line 60):

```php
// SEO — sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
```

- [ ] **Step 5: Run tests — verify pass**

```bash
php artisan test --filter=SitemapTest
```

Expected: all 6 tests pass.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/SitemapController.php routes/web.php tests/Feature/Seo/SitemapTest.php
git commit -m "feat(seo): add /sitemap.xml endpoint with 1h cache"
```

---

## Task 14: Rewrite `robots.txt`

**Files:**
- Modify: `public/robots.txt`
- Test: `tests/Feature/Seo/RobotsTxtTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Seo/RobotsTxtTest.php`:

```php
<?php

use function Pest\Laravel\get;

it('serves robots.txt with text/plain content type', function () {
    $response = get('/robots.txt');
    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('text/plain');
});

it('disallows admin and private paths', function () {
    $body = get('/robots.txt')->getContent();
    expect($body)->toContain('Disallow: /admin')
        ->and($body)->toContain('Disallow: /profil/creer')
        ->and($body)->toContain('Disallow: /inscription')
        ->and($body)->toContain('Disallow: /mes-articles')
        ->and($body)->toContain('Disallow: /newsletter/');
});

it('includes Sitemap directive', function () {
    $body = get('/robots.txt')->getContent();
    expect($body)->toContain('Sitemap: ')
        ->and($body)->toContain('/sitemap.xml');
});
```

- [ ] **Step 2: Run test — verify failure**

```bash
php artisan test --filter=RobotsTxtTest
```

Expected: 2 of 3 fail (current `robots.txt` is minimal and has no Sitemap line).

- [ ] **Step 3: Rewrite `public/robots.txt`**

Replace the contents entirely:

```
# Bassila Émergence
User-agent: *
Allow: /

# Private / admin zones
Disallow: /admin
Disallow: /admin/

# Auth & account flows
Disallow: /inscription
Disallow: /connexion
Disallow: /mot-de-passe-oublie
Disallow: /reinitialiser-mot-de-passe/
Disallow: /deconnexion
Disallow: /email/verify
Disallow: /invitation/

# Editing (auth required)
Disallow: /profil/creer
Disallow: /profil/modifier
Disallow: /blog/rediger
Disallow: /mes-articles
Disallow: /blog/preview/
Disallow: /blog/*/modifier

# Newsletter (transactional links only)
Disallow: /newsletter/

Sitemap: https://REPLACE-WITH-PROD-URL/sitemap.xml
```

The `REPLACE-WITH-PROD-URL` placeholder is edited manually at deploy time.

- [ ] **Step 4: Run tests — verify pass**

```bash
php artisan test --filter=RobotsTxtTest
```

Expected: all 3 tests pass.

- [ ] **Step 5: Commit**

```bash
git add public/robots.txt tests/Feature/Seo/RobotsTxtTest.php
git commit -m "feat(seo): rewrite robots.txt with explicit Disallow rules and Sitemap directive"
```

---

## Task 15: Create default OG image placeholder

**Files:**
- Create: `public/images/og-default.png`

- [ ] **Step 1: Create a brand-coloured 1200×630 PNG**

Use ImageMagick (usually pre-installed) to generate a brand-coloured placeholder. From the project root:

```bash
convert -size 1200x630 xc:"#0A1628" \
    -gravity center \
    -font DejaVu-Sans-Bold -pointsize 90 -fill white -annotate +0-60 "Bassila Émergence" \
    -font DejaVu-Sans -pointsize 36 -fill "#DC143C" -annotate +0+40 "Le réseau des Bassilais à travers le monde" \
    public/images/og-default.png
```

If ImageMagick is not installed: `sudo apt install imagemagick` on Debian/Ubuntu. If a different font is needed (e.g. DejaVu not available), replace `-font DejaVu-Sans-Bold` with `-font Helvetica-Bold` or any available font.

Verify the file exists and is valid:

```bash
file public/images/og-default.png
```

Expected: `PNG image data, 1200 x 630, 8-bit/color RGB, non-interlaced`.

- [ ] **Step 2: Commit**

```bash
git add public/images/og-default.png
git commit -m "feat(seo): add default Open Graph image (1200x630 brand placeholder)"
```

---

## Task 16: Create `/qui-sommes-nous` static page

**Files:**
- Create: `resources/views/pages/qui-sommes-nous.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Seo/StaticPagesTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Seo/StaticPagesTest.php`:

```php
<?php

use function Pest\Laravel\get;

it('serves /qui-sommes-nous with 200 and valid SEO meta', function () {
    $response = get('/qui-sommes-nous');
    $response->assertOk()
        ->assertSee('Qui sommes-nous', false)
        ->assertSee('<h1', false)
        ->assertSee('Bassila Émergence');

    $html = $response->getContent();
    expect($html)->toContain('<link rel="canonical"')
        ->and($html)->toContain(url('/qui-sommes-nous'))
        ->and($html)->toContain('BreadcrumbList');
});

it('serves /a-propos-de-bassila with 200 and valid SEO meta', function () {
    $response = get('/a-propos-de-bassila');
    $response->assertOk()
        ->assertSee('À propos de Bassila', false)
        ->assertSee('<h1', false);

    $html = $response->getContent();
    expect($html)->toContain('<link rel="canonical"')
        ->and($html)->toContain('BreadcrumbList');
});
```

- [ ] **Step 2: Run test — verify failure**

```bash
php artisan test --filter=StaticPagesTest
```

Expected: fails with 404.

- [ ] **Step 3: Create the view**

Create `resources/views/pages/qui-sommes-nous.blade.php`:

```blade
@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Qui sommes-nous')
        ->withDescription('Bassila Émergence est la plateforme qui réunit les Bassilais du Bénin et de la diaspora. Découvrez notre mission, nos valeurs et notre contact.')
        ->withOgType('website');
@endphp
@extends('layouts.app')
@section('title', 'Qui sommes-nous')
@section('description', 'Bassila Émergence est la plateforme qui réunit les Bassilais du Bénin et de la diaspora. Découvrez notre mission, nos valeurs et notre contact.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs
        :items="[
            ['name' => 'Accueil', 'url' => route('home')],
            ['name' => 'Qui sommes-nous', 'url' => null],
        ]"
        :with-json-ld="true"
    />

    <header class="mb-12">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">À propos</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight" style="font-family: 'Lora', serif;">
            Qui sommes-nous — Bassila Émergence
        </h1>
    </header>

    <section class="prose prose-lg max-w-none mb-12">
        <h2>Notre mission</h2>
        <p>
            Bassila Émergence est la plateforme numérique qui réunit les natifs de Bassila, commune du département de la Donga au Bénin, et leur diaspora à travers le monde. Notre mission est de créer un lien vivant entre les générations, les horizons et les parcours professionnels de notre communauté.
        </p>
        <p>
            Chaque profil raconte une histoire de résilience et de réussite. Chaque connexion est un pont entre une personne qui cherche ses racines et une communauté qui accueille. En rendant visible la richesse des Bassilais du Bénin comme de la diaspora, nous contribuons à renforcer l'identité, l'entraide et les opportunités au sein du réseau.
        </p>
    </section>

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-[#111827] mb-6">Nos valeurs</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Entraide</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    La solidarité entre Bassilais — au pays et à l'étranger — est le ciment de notre réseau. Chaque membre est à la fois bénéficiaire et contributeur.
                </p>
            </div>
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Transparence</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Les profils sont modérés et vérifiés avant publication. Nous préférons un réseau authentique à un annuaire volumineux mais approximatif.
                </p>
            </div>
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Fierté communautaire</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Bassila a une histoire, une culture et une diaspora dont nous sommes fiers. La plateforme met cette fierté au service des parcours individuels et collectifs.
                </p>
            </div>
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Ouverture</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Nous accueillons tous les secteurs, toutes les générations, toutes les géographies. La diversité des parcours est une force.
                </p>
            </div>
        </div>
    </section>

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-[#111827] mb-4">Comment ça marche</h2>
        <p class="text-gray-600 leading-relaxed mb-4">
            Créez votre compte en quelques secondes avec votre adresse email. Après vérification, renseignez votre profil : parcours, métier, localisation. Un administrateur vérifie et valide votre profil, puis vous rejoignez officiellement l'annuaire des Bassilais.
        </p>
        <a href="{{ route('register') }}" class="inline-block bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
            Créer mon profil
        </a>
    </section>

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-[#111827] mb-4">Contact</h2>
        <p class="text-gray-600 leading-relaxed">
            Pour toute question concernant la plateforme, écrivez-nous à
            <a href="mailto:{{ setting('contact.email', 'contact@bassila-emergence.org') }}" class="text-[#0066CC] font-semibold hover:underline">
                {{ setting('contact.email', 'contact@bassila-emergence.org') }}
            </a>.
        </p>
    </section>

</div>
@endsection
```

- [ ] **Step 4: Add the route**

In `routes/web.php`, add near the home route:

```php
Route::view('/qui-sommes-nous', 'pages.qui-sommes-nous')->name('pages.about-us');
```

- [ ] **Step 5: Commit**

```bash
git add resources/views/pages/qui-sommes-nous.blade.php routes/web.php tests/Feature/Seo/StaticPagesTest.php
git commit -m "feat(seo): add /qui-sommes-nous content page"
```

---

## Task 17: Create `/a-propos-de-bassila` static page

**Files:**
- Create: `resources/views/pages/a-propos-de-bassila.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create the view**

Create `resources/views/pages/a-propos-de-bassila.blade.php`:

```blade
@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('À propos de Bassila — Commune du Donga, Bénin')
        ->withDescription('Découvrez Bassila, commune du département de la Donga au Bénin : géographie, histoire, culture et diaspora. La plateforme du réseau des Bassilais.')
        ->withOgType('website');
@endphp
@extends('layouts.app')
@section('title', 'À propos de Bassila — Commune du Donga, Bénin')
@section('description', 'Découvrez Bassila, commune du département de la Donga au Bénin : géographie, histoire, culture et diaspora. La plateforme du réseau des Bassilais.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs
        :items="[
            ['name' => 'Accueil', 'url' => route('home')],
            ['name' => 'À propos de Bassila', 'url' => null],
        ]"
        :with-json-ld="true"
    />

    <header class="mb-12">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Territoire</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight mb-4" style="font-family: 'Lora', serif;">
            Bassila — Commune du nord-ouest du Bénin
        </h1>
        <p class="text-gray-600 text-lg leading-relaxed">
            Située dans le département de la Donga, Bassila est une commune au cœur du Bénin dont la population et la diaspora tissent un lien vivant entre territoire et monde.
        </p>
    </header>

    <article class="prose prose-lg max-w-none">

        <h2>Géographie</h2>
        <p>
            Bassila est une commune du département de la Donga, dans le nord-ouest du Bénin. Elle couvre un vaste territoire caractérisé par des paysages de savane arborée, une pluviométrie marquée par la saison sèche et la saison des pluies, et une mosaïque de villages qui structurent la vie communautaire. La commune est traversée par des axes routiers qui la relient aux grandes villes du Bénin et aux pays voisins.
        </p>
        <p>
            {{-- TODO éditorial — préciser la superficie, la population, les principales localités, la distance à Cotonou/Parakou --}}
        </p>

        <h2>Histoire</h2>
        <p>
            L'histoire de Bassila se lit à travers celle de ses habitants, de ses langues et de ses traditions. La commune est le fruit de plusieurs vagues de peuplement qui ont façonné une identité singulière dans le paysage béninois.
        </p>
        <p>
            {{-- TODO éditorial — origines, dates clés, figures historiques, rapport au royaume et à l'administration coloniale puis à l'indépendance --}}
        </p>

        <h2>Culture & traditions</h2>
        <p>
            La richesse culturelle de Bassila s'exprime à travers plusieurs langues parlées dans la commune, des fêtes traditionnelles qui rythment l'année, un artisanat local et une cuisine qui reflètent l'histoire des échanges avec les communautés voisines.
        </p>
        <p>
            {{-- TODO éditorial — lister les langues (Anii, Nagot, etc.), les fêtes principales, les artisanats emblématiques --}}
        </p>

        <h2>Économie locale</h2>
        <p>
            L'économie de Bassila est portée par l'agriculture, le petit commerce, l'artisanat et des secteurs en développement. Les Bassilais présents dans le réseau couvrent un large éventail de métiers, du secteur médical à l'ingénierie en passant par l'éducation et l'entrepreneuriat.
        </p>
        <p>
            <a href="{{ route('directory.index') }}" class="text-[#0066CC] font-semibold hover:underline">
                Explorer les secteurs représentés dans l'annuaire &rarr;
            </a>
        </p>

        <h2>La diaspora Bassilaise</h2>
        <p>
            Des centaines de Bassilais vivent aujourd'hui en Afrique, en Europe et à travers le monde. Ils sont médecins, ingénieurs, enseignants, entrepreneurs, artistes — et ils partagent un même attachement à leur commune d'origine. Bassila Émergence est leur maison numérique commune : un lieu où les générations se retrouvent, où les compétences se partagent, et où les ponts entre le pays et le monde se construisent.
        </p>

    </article>

    <section class="mt-16 bg-[#0066CC] text-white p-10">
        <h2 class="text-2xl font-bold mb-3" style="font-family: 'Lora', serif;">
            Faites partie du réseau
        </h2>
        <p class="text-white/85 mb-6 max-w-2xl">
            Si vous êtes Bassilais(e) — au Bénin ou à l'étranger — rejoignez la plateforme, créez votre profil et connectez-vous avec la communauté.
        </p>
        <a href="{{ route('register') }}" class="inline-block bg-white text-[#0066CC] font-semibold px-7 py-3 text-sm hover:bg-gray-100 transition">
            Créer mon profil
        </a>
    </section>

</div>
@endsection
```

- [ ] **Step 2: Add the route**

In `routes/web.php`, add near the other static page route:

```php
Route::view('/a-propos-de-bassila', 'pages.a-propos-de-bassila')->name('pages.about-bassila');
```

- [ ] **Step 3: Run static pages tests**

```bash
php artisan test --filter=StaticPagesTest
```

Expected: both static page tests pass.

- [ ] **Step 4: Commit**

```bash
git add resources/views/pages/a-propos-de-bassila.blade.php routes/web.php
git commit -m "feat(seo): add /a-propos-de-bassila content page"
```

---

## Task 18: Add navigation + footer links to static pages

**Files:**
- Modify: `resources/views/partials/nav.blade.php`
- Modify: `resources/views/partials/footer.blade.php`

- [ ] **Step 1: Add "À propos" link to main nav**

In `partials/nav.blade.php`, inside the `<nav class="hidden md:flex items-center gap-8">` block, after the existing Annuaire link (around line 25), add:

```blade
<a href="{{ route('pages.about-us') }}"
   class="text-sm font-medium {{ request()->routeIs('pages.*') ? 'text-[#0066CC]' : 'text-gray-600 hover:text-[#111827]' }} transition">
    À propos
</a>
```

- [ ] **Step 2: Add "À propos" column to footer**

In `partials/footer.blade.php`, change the grid from `md:grid-cols-4` to `md:grid-cols-5` and add a new column after the "Compte" column:

```blade
<div>
    <h4 class="text-white text-xs font-semibold uppercase tracking-widest mb-4">À propos</h4>
    <ul class="space-y-2 text-sm">
        <li><a href="{{ route('pages.about-us') }}" class="hover:text-white transition">Qui sommes-nous</a></li>
        <li><a href="{{ route('pages.about-bassila') }}" class="hover:text-white transition">À propos de Bassila</a></li>
    </ul>
</div>
```

And update the brand column's span if needed — `md:col-span-2` becomes `md:col-span-1` if the layout looks cramped at 5 columns (visually judge during step 3).

- [ ] **Step 3: Smoke-test home still renders**

```bash
php artisan test --filter=HomePageTest
```

Expected: all pass.

- [ ] **Step 4: Commit**

```bash
git add resources/views/partials/nav.blade.php resources/views/partials/footer.blade.php
git commit -m "feat(seo): add À propos link in main nav and footer"
```

---

## Task 19: Font preload + PerformanceHintsTest

**Files:**
- Modify: `resources/views/layouts/app.blade.php` *(if not already done in Task 7)*
- Test: `tests/Feature/Seo/PerformanceHintsTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Seo/PerformanceHintsTest.php`:

```php
<?php

use function Pest\Laravel\get;

it('home has at least one fetchpriority=high image', function () {
    $html = get('/')->getContent();
    expect($html)->toContain('fetchpriority="high"');
});

it('home has a preload link for the stylesheet', function () {
    $html = get('/')->getContent();
    expect($html)->toContain('rel="preload"')
        ->and($html)->toContain('as="style"');
});

it('home has no img without a width attribute', function () {
    $html = get('/')->getContent();

    // Crude but effective: count <img tags and count width= occurrences inside img tags
    preg_match_all('/<img\b[^>]*>/i', $html, $matches);
    $imgs = $matches[0] ?? [];

    $missing = array_filter($imgs, fn ($tag) => ! preg_match('/\bwidth\s*=/i', $tag));
    expect($missing)->toBe([]);
});
```

- [ ] **Step 2: Verify test state**

```bash
php artisan test --filter=PerformanceHintsTest
```

Expected: the preload test should already pass if Task 7 added the `<link rel="preload">`. The `fetchpriority` test should pass if Task 8 added `fetchpriority="high"` on the hero. The `width` test may fail if some `<img>` tags in `welcome.blade.php` still lack `width`/`height` — scan and fix all of them.

- [ ] **Step 3: Patch any remaining `<img>` without width**

In `welcome.blade.php`, find the featured profiles `<img>` and the blog card `<img>` (currently at roughly lines 338, 412) and add `width`/`height` attributes:

```blade
{{-- Blog card image --}}
<img src="{{ $post->featured_image_url }}"
     alt="{{ $post->title }}"
     width="800" height="450"
     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">

{{-- Featured profile avatar --}}
<img src="{{ $profile->avatar_url }}"
     alt="{{ $profile->full_name }}"
     width="48" height="48"
     class="w-12 h-12 object-cover shrink-0">
```

- [ ] **Step 4: Run tests — verify pass**

```bash
php artisan test --filter=PerformanceHintsTest
```

Expected: all 3 tests pass.

- [ ] **Step 5: Commit**

```bash
git add resources/views/welcome.blade.php tests/Feature/Seo/PerformanceHintsTest.php
git commit -m "perf(seo): add width/height on all home images, font preload check"
```

---

## Task 20: MetaTags feature tests

**Files:**
- Test: `tests/Feature/Seo/MetaTagsTest.php`

- [ ] **Step 1: Write the tests**

Create `tests/Feature/Seo/MetaTagsTest.php`:

```php
<?php

use App\Models\BlogPost;
use App\Models\Profile;
use function Pest\Laravel\get;

it('home has non-empty title, description, canonical, og:image', function () {
    $html = get('/')->getContent();

    expect($html)->toContain('<title>')
        ->and($html)->toContain('<meta name="description"')
        ->and($html)->toContain('<link rel="canonical"')
        ->and($html)->toContain('og:image');
});

it('home includes google-site-verification meta when env is set', function () {
    config()->set('seo.google_verification', 'test-token-123');

    $html = get('/')->getContent();

    expect($html)->toContain('google-site-verification')
        ->and($html)->toContain('test-token-123');
});

it('blog show uses og:type=article and article:published_time', function () {
    $post = BlogPost::factory()->published()->create(['slug' => 'my-slug']);

    $html = get('/blog/my-slug')->getContent();

    expect($html)->toContain('og:type')
        ->and($html)->toContain('article')
        ->and($html)->toContain('article:published_time');
});

it('profile show of a verified profile uses og:type=profile', function () {
    $profile = Profile::factory()->verified()->create();

    $html = get('/profils/' . $profile->id)->getContent();

    expect($html)->toContain('og:type')
        ->and($html)->toContain('profile');
});

it('login page has noindex meta', function () {
    $html = get('/connexion')->getContent();
    expect($html)->toContain('noindex');
});

it('a-propos-de-bassila has a single H1', function () {
    $html = get('/a-propos-de-bassila')->getContent();
    $count = substr_count($html, '<h1');
    expect($count)->toBe(1);
});
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --filter=MetaTagsTest
```

Expected: all tests pass. If any fails, inspect the HTML output and adjust the task 7–17 wiring accordingly.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Seo/MetaTagsTest.php
git commit -m "test(seo): add feature tests for meta tags across page types"
```

---

## Task 21: JsonLd feature tests

**Files:**
- Test: `tests/Feature/Seo/JsonLdTest.php`

- [ ] **Step 1: Write the tests**

Create `tests/Feature/Seo/JsonLdTest.php`:

```php
<?php

use App\Models\BlogPost;
use App\Models\Profile;
use function Pest\Laravel\get;

it('home contains Organization and WebSite JSON-LD blocks', function () {
    $html = get('/')->getContent();

    expect($html)->toContain('"@type":"Organization"')
        ->and($html)->toContain('"@type":"WebSite"')
        ->and($html)->toContain('"SearchAction"');
});

it('blog show contains one Article JSON-LD block', function () {
    BlogPost::factory()->published()->create(['slug' => 'json-test-post']);

    $html = get('/blog/json-test-post')->getContent();

    expect(substr_count($html, '"@type":"Article"'))->toBe(1);
});

it('verified profile contains one Person JSON-LD block', function () {
    $profile = Profile::factory()->verified()->create();

    $html = get('/profils/' . $profile->id)->getContent();

    expect(substr_count($html, '"@type":"Person"'))->toBe(1);
});

it('a-propos-de-bassila contains a BreadcrumbList', function () {
    $html = get('/a-propos-de-bassila')->getContent();
    expect($html)->toContain('"BreadcrumbList"');
});

it('all JSON-LD blocks are valid JSON', function () {
    BlogPost::factory()->published()->create(['slug' => 'valid-json-post']);

    $html = get('/blog/valid-json-post')->getContent();

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
    $blocks = $matches[1] ?? [];

    expect($blocks)->not->toBeEmpty();

    foreach ($blocks as $json) {
        // Reverse the <-escape so json_decode can parse
        $clean = str_replace('\u003c', '<', $json);
        $decoded = json_decode($clean, true);
        expect(json_last_error())->toBe(JSON_ERROR_NONE, 'Invalid JSON: ' . substr($clean, 0, 200));
        expect($decoded)->toBeArray();
    }
});
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --filter=JsonLdTest
```

Expected: all tests pass.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Seo/JsonLdTest.php
git commit -m "test(seo): add feature tests for JSON-LD on key page types"
```

---

## Task 22: nginx cache headers for hashed assets

**Files:**
- Modify: `nginx_app.conf`

- [ ] **Step 1: Add a location block for `/build/*`**

Current `nginx_app.conf`:

```
location / {
    try_files $uri /index.php$is_args$args;
}
```

Replace with:

```
location ^~ /build/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    try_files $uri =404;
}

location ^~ /images/ {
    expires 30d;
    add_header Cache-Control "public";
    try_files $uri =404;
}

location / {
    try_files $uri /index.php$is_args$args;
}
```

- [ ] **Step 2: Commit**

```bash
git add nginx_app.conf
git commit -m "perf(seo): cache /build and /images with long expiry in nginx"
```

Note: this change takes effect only after redeploy on Dokku. No local impact.

---

## Task 23: Run the full SEO test suite

**Files:** *(no changes — verification step)*

- [ ] **Step 1: Run all SEO tests at once**

```bash
php artisan test --filter=Seo
```

Expected: every test from tasks 2, 3, 6, 13, 14, 16, 17, 19, 20, 21 passes. If any fails, fix the referenced task before continuing.

- [ ] **Step 2: Run the entire test suite to confirm no regressions**

```bash
php artisan test
```

Expected: green across the board, including pre-existing blog, auth, profile, directory, newsletter, and admin tests.

- [ ] **Step 3: Commit nothing** — this is purely a verification step. Proceed only if both test runs are green.

---

## Task 24: Manual verification checklist (post-deploy only — not an execution task)

This task is **not** automated. Document it in a `docs/superpowers/manual-checks/2026-04-11-seo-verification.md` file for the deployer to run after production rollout. It is not a blocker for the plan's completion in dev.

**Files:**
- Create: `docs/superpowers/manual-checks/2026-04-11-seo-verification.md`

- [ ] **Step 1: Create the checklist document**

Create `docs/superpowers/manual-checks/2026-04-11-seo-verification.md`:

```markdown
# SEO Verification Checklist — Post-Deploy

Run this after the first production deploy of the SEO work.

## 1. robots.txt production URL

- [ ] Edit `public/robots.txt`, replace `REPLACE-WITH-PROD-URL` with the real production hostname (e.g. `https://bassila-emergence.org`).
- [ ] Redeploy.
- [ ] Visit `{prod}/robots.txt` and confirm the Sitemap line is correct.

## 2. Google Search Console verification

- [ ] Go to https://search.google.com/search-console and add the property with URL-prefix = production URL.
- [ ] Copy the "HTML tag" token Google provides.
- [ ] Set `GOOGLE_SITE_VERIFICATION=<token>` in the production `.env`.
- [ ] Redeploy.
- [ ] Back in Search Console, click "Verify". Expect "Property verified".

## 3. Sitemap submission

- [ ] In Search Console → Sitemaps → add `sitemap.xml` → submit.
- [ ] Within 24 h, check the "Discovered URLs" count matches the number of live URLs.

## 4. Rich Results validation

- [ ] Open https://search.google.com/test/rich-results
- [ ] Test `{prod}/` — expect Organization detected.
- [ ] Test `{prod}/blog/<any published slug>` — expect Article detected.
- [ ] Test `{prod}/profils/<any verified id>` — expect Person detected.
- [ ] Test `{prod}/qui-sommes-nous` — expect BreadcrumbList detected.

## 5. Social share previews

- [ ] Open https://cards-dev.twitter.com/validator and test the home URL. Expect a summary_large_image card.
- [ ] Open https://developers.facebook.com/tools/debug/ and test the home URL. Expect title, description, og:image.
- [ ] Repeat for a blog post URL.

## 6. Core Web Vitals baseline

- [ ] Open https://pagespeed.web.dev and test `{prod}/` on mobile.
- [ ] Record LCP, CLS, INP, total weight. Target: LCP < 2.5s, CLS < 0.05, INP < 200ms.
- [ ] If any metric is red, profile and iterate on image/font tuning.

## 7. Search Console first-week check

- [ ] One week post-verification, open Search Console → Performance. Confirm impressions are growing.
- [ ] Open Search Console → Core Web Vitals. Confirm no red URLs.
- [ ] Open Search Console → Coverage. Confirm no private URLs (/admin, /profil/creer) are reported as "excluded by robots.txt" in a problematic way.
```

- [ ] **Step 2: Commit**

```bash
git add docs/superpowers/manual-checks/2026-04-11-seo-verification.md
git commit -m "docs(seo): add post-deploy verification checklist"
```

---

## Self-Review

**Spec coverage** — spec sections mapped to tasks:

- §Architecture → Tasks 1-6 (config, DTO, factory, components)
- §SeoData DTO → Task 2
- §MetaTags component → Tasks 4 + 7 (layout integration)
- §Layout integration → Task 7
- §Views migration → Tasks 8-12
- §Sitemap → Task 13
- §robots.txt → Task 14
- §Structured data → Tasks 3 (factory) + 5 (component) + 8-10 + 21 (integration tests)
- §Breadcrumbs → Task 6 + wired in 9, 10, 16, 17
- §New static pages → Tasks 16, 17 (+ nav/footer in 18)
- §Default OG image → Task 15
- §Performance → Tasks 8, 19, 22
- §Search Console setup → Task 1 (env) + Task 24 (manual checklist)
- §Testing strategy → Tests co-located with every feature task + consolidation in Task 23

All spec sections have at least one task.

**Placeholder scan** — no "TBD", "TODO", "implement later" in plan steps. The `{{-- TODO éditorial --}}` markers in the Blade template for `/a-propos-de-bassila` are intentional content markers for future editorial work, not plan placeholders — they are visible to the human user when the plan is done.

**Type consistency check** — `SeoData` method signatures are identical everywhere they appear (Task 2 definition, Task 4 consumer, Tasks 8-12 usage): `withTitle(string)`, `withDescription(string)`, `withCanonical(string)`, `withOgType(string)`, `withOgImage(?string, ?string = null)`, `withNoindex(bool = true)`, `withArticleMeta(array)`. `StructuredData` static methods: `organization()`, `website()`, `article(BlogPost)`, `person(Profile)`, `breadcrumb(array)` — consistent across Task 3 and Tasks 8, 9, 10.

**Dependency order**: Tasks 1→2→3→4→5→6→7 must run in order (foundations + layout). Tasks 8-12 can run in parallel after Task 7. Task 13 depends on 1 only. Task 14 is independent. Tasks 15-17 depend on Tasks 1, 4, 6. Task 18 depends on 16+17. Tasks 19-21 are verification and depend on all prior wiring. Tasks 22-24 are final.

---

## Execution Handoff

**Plan complete and saved to `docs/superpowers/plans/2026-04-11-seo-optimization.md`. Two execution options:**

**1. Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration. Best for this plan because tasks 8–12 can parallelize and tasks 2–6 benefit from fresh-context review.

**2. Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints.

**Which approach?**
