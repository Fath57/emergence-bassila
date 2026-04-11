# SEO Optimization — Design

**Date:** 2026-04-11
**Branch:** `001-bassila-network-platform`
**Status:** Design approved — ready for implementation plan

## Context

Bassila Émergence is a networking platform for the Bassila community (natives of Bassila, Bénin, and its worldwide diaspora). The public site already has:

- `resources/views/layouts/app.blade.php` with basic `<title>`, `<meta description>`, Open Graph and Twitter Card
- Blog posts with `resolved_meta_title` / `resolved_meta_description` + `article`-type Open Graph
- French URLs (`/annuaire`, `/inscription`, `/blog`, `/profils/{id}`, etc.)
- A permissive `public/robots.txt`

The site is missing: sitemap, JSON-LD structured data, canonical URLs, breadcrumbs, a dedicated informational page about Bassila, a "Who we are" page, default OG image, Search Console verification, and several Core Web Vitals wins. This spec closes those gaps.

## Goals

1. **Technical SEO foundations**: crawlability, indexability, canonical URLs, Schema.org structured data.
2. **Content SEO for three audience priorities**: diaspora (P1), natives in Bénin (P1), informational queries about Bassila the town (P2).
3. **Core Web Vitals in the green**: LCP < 2.5s, CLS < 0.05, INP < 200ms.
4. **Free, simple observability** via Google Search Console only (no GA, no cookie banner, no budget).

Non-goals:
- Google Analytics, Matomo, Plausible, or any behavioural tracking.
- Sector or country landing pages (premium scope, deferred).
- Automated image optimization pipeline (manual one-shot instead).
- Critical CSS inlining.

## Target audiences & keyword focus

| Priority | Audience | Sample queries |
|---|---|---|
| P1 | Bassila diaspora abroad | "diaspora Bassila", "Bassilais France", "réseau Bassila monde" |
| P1 | Natives in Bénin | "réseau Bassila Bénin", "professionnels Bassila", "annuaire Bassila" |
| P2 | Informational queries about Bassila (the town) | "Bassila Bénin", "commune de Bassila", "Donga Bassila", "histoire Bassila" |

P1 is served by the home, `/annuaire`, `/qui-sommes-nous`, and profile pages. P2 is served by `/a-propos-de-bassila`.

## Architecture

```
app/
├── Http/Controllers/
│   └── SitemapController.php
├── View/Components/
│   ├── Seo/MetaTags.php
│   ├── Seo/JsonLd.php
│   └── Breadcrumbs.php
└── Support/Seo/
    ├── SeoData.php           (readonly DTO)
    └── StructuredData.php    (factory for Schema.org arrays)

config/seo.php                (site name, default title/description, default OG image, Google verification token, socials)

resources/views/
├── layouts/app.blade.php     (refactored: uses <x-seo.meta-tags>)
├── components/
│   ├── seo/meta-tags.blade.php
│   ├── seo/json-ld.blade.php
│   └── breadcrumbs.blade.php
└── pages/
    ├── a-propos-de-bassila.blade.php
    └── qui-sommes-nous.blade.php

public/
├── robots.txt                (rewritten, static, hard-coded Sitemap URL)
└── images/
    ├── og-default.png        (1200x630, brand placeholder)
    └── home/
        ├── hero-community.webp   (replaces Unsplash line 16 in welcome.blade.php)
        ├── hero-community.jpg    (fallback)
        ├── mission.webp          (replaces Unsplash line 126)
        └── mission.jpg           (fallback)

routes/web.php                (+ /sitemap.xml, + /a-propos-de-bassila, + /qui-sommes-nous)
```

**Design principles:**

- **Single entry point for meta tags**: `<x-seo.meta-tags :seo="$seo" />` reads a `SeoData` DTO. No more scattered `@section('title')`.
- **Single factory for JSON-LD**: `StructuredData::article($post)`, `::person($profile)`, `::organization()`, `::website()`, `::breadcrumb($items)`. Each returns a plain array.
- **Soft backward compatibility**: the layout still honours `@section('title')` and `@section('description')` as a fallback during migration; once all views use `SeoData`, the fallback is removed.
- **Config-driven defaults**: `config/seo.php` holds the site name, default title, default description, default OG image path, locale (`fr_FR`), Google Site Verification token (from env), and social profile URLs.

## Detailed design

### 1. `SeoData` DTO

`app/Support/Seo/SeoData.php` — readonly class:

```php
final readonly class SeoData
{
    public function __construct(
        public string  $title,
        public string  $description,
        public string  $canonical,
        public string  $ogType = 'website',      // website | article | profile
        public ?string $ogImage = null,
        public ?string $ogImageAlt = null,
        public bool    $noindex = false,
        public string  $locale = 'fr_FR',
        public array   $articleMeta = [],         // publishedTime, modifiedTime, author, section, tags
    ) {}

    public static function default(): self;      // reads config/seo.php
    public function withTitle(string $title): self;
    public function withDescription(string $description): self;
    public function withCanonical(string $url): self;
    public function withOgType(string $type): self;
    public function withOgImage(?string $url, ?string $alt = null): self;
    public function withNoindex(bool $noindex = true): self;
    public function withArticleMeta(array $meta): self;
}
```

Each `withXxx()` returns a new instance (immutable). `withCanonical()` accepts a relative path and converts to absolute via `url()`.

### 2. `<x-seo.meta-tags>` component

Renders, in order:

- `<title>{{ $title }} — Bassila Émergence</title>` (suffix is omitted when the title equals the site name, to avoid "Bassila Émergence — Bassila Émergence" on the home).
- `<meta name="description" content="...">`
- `<link rel="canonical" href="{{ $canonical }}">` (always)
- `<meta name="robots" content="noindex, nofollow">` **only if** `$noindex === true`
- `<meta name="google-site-verification" content="{{ config('seo.google_verification') }}">` if the env var is set
- Open Graph: `og:title`, `og:description`, `og:url`, `og:type`, `og:site_name`, `og:locale`, `og:image`, `og:image:width`, `og:image:height`, `og:image:alt`
- If `ogType === 'article'`: `article:published_time`, `article:modified_time`, `article:author`, `article:section`, one `article:tag` per tag
- Twitter: `twitter:card=summary_large_image`, `twitter:title`, `twitter:description`, `twitter:image`, `twitter:image:alt`

If `ogImage` is `null`, fall back to `config('seo.default_og_image')` → `/images/og-default.png` (1200×630, brand placeholder).

### 3. Layout integration

`resources/views/layouts/app.blade.php` becomes:

```blade
@php
    $seo = ($seo ?? \App\Support\Seo\SeoData::default())
        ->withCanonical(url()->current())
        ->withTitle($__env->yieldContent('title') ?: config('seo.default_title'))
        ->withDescription($__env->yieldContent('description') ?: config('seo.default_description'));
@endphp

<x-seo.meta-tags :seo="$seo" />
```

Pages opt into the new style by defining `$seo` before `@extends`:

```blade
@php
    use App\Support\Seo\SeoData;
    $seo = SeoData::default()
        ->withTitle($post->resolved_meta_title)
        ->withDescription($post->resolved_meta_description)
        ->withOgType('article')
        ->withOgImage($post->featured_image_url)
        ->withArticleMeta([
            'publishedTime' => $post->published_at?->toIso8601String(),
            'modifiedTime'  => $post->updated_at?->toIso8601String(),
            'author'        => $post->user->profile?->full_name,
            'section'       => $post->category?->name,
            'tags'          => $post->tags ?? [],
        ]);
@endphp
@extends('layouts.app')
```

**Views to migrate** in one pass:

| View | ogType | Notes |
|---|---|---|
| `welcome.blade.php` | website | Default OG image |
| `blog/index.blade.php` | website | Default OG image |
| `blog/show.blade.php` | article | featured_image_url or default, articleMeta populated |
| `profile/show.blade.php` | profile | avatar_url or default |
| `livewire/directory/search-directory.blade.php` | website | Default OG image |
| `pages/a-propos-de-bassila.blade.php` | website | Hero image |
| `pages/qui-sommes-nous.blade.php` | website | Default OG image |
| Auth pages (`login`, `register`, `forgot-password`, `reset-password`, `verify-email`, `accept-invitation`) | website | `noindex=true` |
| `profile/create-profile.blade.php`, `edit-profile.blade.php` | website | `noindex=true` |
| `blog/create-post.blade.php`, `edit-post.blade.php`, `my-posts.blade.php`, `preview-post.blade.php` | website | `noindex=true` |

### 4. Sitemap

**Route**: `GET /sitemap.xml` → `SitemapController@index` returning `text/xml` with a `Cache-Control: public, max-age=3600` header.

**Cache**: `Cache::remember('seo.sitemap', 3600, fn () => $this->build())`. The DB queries use `select('id', 'slug', 'updated_at')` only.

**Content** (flat URL set, no sitemap index — valid while total URLs < 50 000):

| URL | priority | changefreq | lastmod |
|---|---|---|---|
| `/` | 1.0 | daily | `max(last blog updated, last verified profile updated)` |
| `/annuaire` | 0.9 | daily | last verified profile `updated_at` |
| `/blog` | 0.9 | daily | last published post `updated_at` |
| `/blog/{slug}` (each published) | 0.8 | weekly | `updated_at` |
| `/profils/{id}` (each `is_verified=true`) | 0.7 | monthly | `updated_at` |
| `/a-propos-de-bassila` | 0.6 | monthly | deploy time (config constant) |
| `/qui-sommes-nous` | 0.6 | monthly | deploy time (config constant) |

**Excluded**: `/admin*`, `/profil/creer`, `/profil/modifier`, `/blog/rediger`, `/mes-articles`, `/blog/preview/*`, `/blog/*/modifier`, all auth routes, `/email/verify*`, `/invitation/*`, `/newsletter/*`, unverified profiles, draft posts.

### 5. `robots.txt`

Static file at `public/robots.txt`. The `Sitemap:` directive is hard-coded to the production URL; if the production URL ever changes, update this file manually (one-line edit).

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

The placeholder `REPLACE-WITH-PROD-URL` will be edited to the real production hostname at deploy time (documented in the deployment checklist below).

### 6. Structured data (JSON-LD)

`app/Support/Seo/StructuredData.php` exposes static methods that each return an array ready for `json_encode`.

**`<x-seo.json-ld :data="$data">`** component: accepts either an array (one block) or an array of arrays (multiple blocks), renders `<script type="application/ld+json">...</script>`. JSON is encoded with `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`, then `<` is escaped to `\u003c` to prevent HTML-in-JSON XSS. Null values are stripped recursively so clean output has no empty keys.

**Blocks per page:**

| Page | Blocks |
|---|---|
| `/` | `Organization` + `WebSite` (two separate blocks, not merged into `@graph`) |
| `/blog/{slug}` | `Article` + `BreadcrumbList` |
| `/profils/{verified}` | `Person` + `BreadcrumbList` |
| `/a-propos-de-bassila` | `BreadcrumbList` |
| `/qui-sommes-nous` | `BreadcrumbList` |

Blocks are injected via `@push('head') <x-seo.json-ld :data="..." /> @endpush`, using the existing `@stack('head')` in `app.blade.php`.

**`StructuredData::organization()`**: `@type: Organization`, `name` and `url` from config, `logo` absolute URL, `sameAs: []` from `config('seo.socials')` (empty by default).

**`StructuredData::website()`**: `@type: WebSite`, with `potentialAction: { @type: SearchAction, target: "{APP_URL}/annuaire?q={search_term_string}", query-input: "required name=search_term_string" }`. This activates Google's Sitelinks Search Box.

**`StructuredData::article(BlogPost $post)`**: `headline` (≤ 110 chars), `description`, `image` (featured_image_url or default), `datePublished` / `dateModified` in ISO 8601, `author: { @type: Person, name, url }` linking to the author's profile page, `publisher: { @type: Organization, name, logo }`, `mainEntityOfPage: {canonical URL}`, `articleSection` (category name), `keywords` (tags if present).

**`StructuredData::person(Profile $profile)`**: only callable for verified profiles — throws `InvalidArgumentException` otherwise. Fields: `name`, `jobTitle` (if set), `image` (avatar_url if set), `url` (canonical profile URL), `address: { @type: PostalAddress, addressLocality: city, addressCountry: country }` (omitted if neither city nor country is set). `sameAs` defaults to an empty array; it becomes populated later if/when the Profile model exposes public links (out of scope for this spec). Any null field is omitted from the output array entirely.

**`StructuredData::breadcrumb(array $items)`**: `itemListElement` numbered from `position: 1`, with `name` + `item` (absolute URL) per entry.

### 7. Breadcrumbs component

`resources/views/components/breadcrumbs.blade.php`:

```blade
@props(['items' => [], 'withJsonLd' => false])

<nav aria-label="Fil d'ariane" class="mb-6 text-sm text-gray-400 flex items-center gap-2 flex-wrap">
    @foreach ($items as $item)
        @if ($item['url'] ?? null)
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
```

The `@once` guard guarantees the JSON-LD block is emitted at most once per page even if the component is accidentally instantiated twice.

**Pages with breadcrumbs:**

| Page | Items |
|---|---|
| `/blog/{slug}` | Accueil › Blog › *post title* |
| `/profils/{id}` | Accueil › Annuaire › *full name* |
| `/a-propos-de-bassila` | Accueil › À propos de Bassila |
| `/qui-sommes-nous` | Accueil › Qui sommes-nous |

Section roots (`/blog`, `/annuaire`) and the home do not render breadcrumbs.

The existing manual breadcrumb in `blog/show.blade.php:22-26` is replaced by `<x-breadcrumbs :items="..." :with-json-ld="true" />`.

### 8. New static pages

Two plain views routed with `Route::view()` — no controllers needed.

```php
Route::view('/a-propos-de-bassila', 'pages.a-propos-de-bassila')->name('pages.about-bassila');
Route::view('/qui-sommes-nous',     'pages.qui-sommes-nous')->name('pages.about-us');
```

**`/a-propos-de-bassila`** (~1200 words final) — SEO targets: "Bassila", "commune de Bassila", "Bassila Bénin", "Donga Bassila".

Structure:
1. Hero — H1 *"Bassila — commune du nord-ouest du Bénin"*, subtitle, hero image
2. H2 "Géographie" — location, Donga department, population, an OpenStreetMap static embed
3. H2 "Histoire" — origins, settlement, key dates *(editorial TODO)*
4. H2 "Culture & traditions" — languages (Anii, Nagot…), festivals, craft
5. H2 "Économie locale" — agriculture, sectors, link to `/annuaire` filtered by relevant sectors
6. H2 "La diaspora Bassilaise" — transition to the platform, CTA to register + explore directory
7. Final CTA — register + newsletter

Placeholder copy is neutral and factual — no invented dates, names, or statistics. Editorial content is marked with `{{-- TODO éditorial --}}`.

SEO:
- title: "À propos de Bassila — Commune du Donga, Bénin"
- description: "Découvrez Bassila, commune du département de la Donga au Bénin : géographie, histoire, culture et diaspora. La plateforme du réseau des Bassilais."
- ogType: website, ogImage: hero section image
- Breadcrumb with JSON-LD

**`/qui-sommes-nous`** (~600 words final) — SEO targets: "Bassila Émergence", "association Bassilais", "plateforme diaspora Bassila".

Structure:
1. Hero — H1 *"Qui sommes-nous — Bassila Émergence"*
2. H2 "Notre mission" — long-form version of the text currently in `welcome.blade.php:100-135`
3. H2 "Nos valeurs" — 3–4 values: entraide, transparence, fierté communautaire, ouverture
4. H2 "Comment ça marche" — link to existing register flow
5. H2 "Gouvernance" — *optional placeholder, omitted entirely if empty*
6. H2 "Contact" — contact email via `setting('contact.email')` if present, otherwise a config constant
7. CTA — newsletter + register

SEO:
- title: "Qui sommes-nous — Bassila Émergence"
- description: "Bassila Émergence est la plateforme qui réunit les Bassilais du Bénin et de la diaspora. Découvrez notre mission, nos valeurs et notre équipe."
- Breadcrumb with JSON-LD

**Navigation integration:**
- **Main nav** (`partials/nav.blade.php`): add a single "À propos" link pointing to `/qui-sommes-nous`.
- **Footer** (`partials/footer.blade.php`): add a new "À propos" column containing both pages + link to "/" + "/annuaire" + "/blog".

### 9. Performance — Core Web Vitals

**Budget:**
- LCP < 2.5s on simulated 4G mobile
- CLS < 0.05
- INP < 200ms
- Total page weight for home < 500 KB (excluding fonts, which are CDN-cached)

**Actions:**

**Images.**
- Download the two Unsplash images used in `welcome.blade.php:16` and `:126` and serve them locally from `public/images/home/` in WebP (primary) + JPEG (fallback) at two sizes (1600w, 900w). These are placeholders until a real community photo is provided.
- Add explicit `width=` and `height=` attributes on every `<img>` rendered by public pages (hero, welcome, blog show, profile show, blog cards, avatars). Systematic pass — approximately 20 `<img>` tags.
- Hero image on `/`: `loading="eager"` + `fetchpriority="high"` (it is the LCP element).
- All other images: `loading="lazy"` + `decoding="async"`.

**Fonts.**
- Add `<link rel="preload" as="style" href="...bunny fonts URL...">` before the existing stylesheet link (`app.blade.php:30`).
- Verify Bunny Fonts serves with `font-display: swap`. If not, ship a local `@font-face` override with `font-display: swap`.

**HTTP caching.**
- `/build/*` (Vite hashed assets): `Cache-Control: public, max-age=31536000, immutable`. Configure in `nginx_app.conf` if not already.
- `/images/*`: `Cache-Control: public, max-age=2592000` (30 days).
- `/sitemap.xml`: `Cache-Control: public, max-age=3600` (aligned with the application-level cache).

**Not in scope:**
- Critical CSS inlining.
- Automated image optimization pipeline (Intervention → WebP on upload).
- JS bundle splitting.

### 10. Search Console setup (deployment checklist)

Procedural only — documented here so it is not lost at deploy time:

1. Go to https://search.google.com/search-console and add a URL-prefix property with the production URL.
2. Choose verification method **"HTML tag"**. Google provides a token.
3. Set `GOOGLE_SITE_VERIFICATION=<token>` in the production `.env`.
4. Redeploy. The meta tag appears in `<head>` via `<x-seo.meta-tags>`.
5. Back in Search Console, click **"Verify"**. Expect "Property verified".
6. Before submitting the sitemap: edit `public/robots.txt` to replace `REPLACE-WITH-PROD-URL` with the real production hostname, then redeploy.
7. In Search Console → **Sitemaps** → add `sitemap.xml` → submit.
8. One week post-launch: check **Performance** and **Core Web Vitals** panels.

## Config file

`config/seo.php` (new):

```php
return [
    'site_name'           => 'Bassila Émergence',
    'default_title'       => 'Le réseau des Bassilais à travers le monde',
    'default_description' => 'La plateforme de networking des Bassilais à travers le monde. Retrouvez d\'anciens camarades, développez votre réseau professionnel et contribuez à l\'histoire de votre communauté d\'origine.',
    'default_og_image'    => '/images/og-default.png',
    'locale'              => 'fr_FR',
    'google_verification' => env('GOOGLE_SITE_VERIFICATION'),
    'socials'             => [
        // 'https://www.facebook.com/...',
        // 'https://www.linkedin.com/company/...',
    ],
    'static_pages_lastmod' => '2026-04-11', // bumped on meaningful edits
];
```

## Testing strategy

Tests live in `tests/Unit/Seo/` and `tests/Feature/Seo/`, executable in isolation via `php artisan test --filter=Seo`.

**Unit (`tests/Unit/Seo/`):**

1. **`SeoDataTest`** — `::default()` reads config; every `withXxx()` returns a new instance; `withCanonical()` converts relative to absolute.
2. **`StructuredDataTest`** — `organization()` has `@context` + `@type` + absolute logo; `website()` has `potentialAction.target` = `/annuaire?q={search_term_string}`; `article($post)` has ISO 8601 dates and `author.url` = profile URL; `person($verified)` has address, skips null fields; `person($unverified)` throws `InvalidArgumentException`; `breadcrumb([...])` numbers from position 1.

**Feature (`tests/Feature/Seo/`):**

3. **`SitemapTest`** — `GET /sitemap.xml` → 200, `application/xml`; contains home, blog index, annuaire; contains published article but not draft; contains verified profile but not unverified; contains no `/admin`, `/profil/creer`, `/mes-articles` patterns; `lastmod` on article matches `updated_at`; second hit served from cache (DB not re-queried — verified via `DB::enableQueryLog()`).
4. **`RobotsTxtTest`** — `GET /robots.txt` → 200, `text/plain`; contains `Disallow: /admin`; contains `Sitemap:` directive.
5. **`MetaTagsTest`** — home has non-empty title, description, canonical, og:image, google-site-verification meta (when env set); `/blog/{slug}` has `og:type=article` and `article:published_time`; `/profils/{verified}` has `og:type=profile`; `/inscription` has `<meta name="robots" content="noindex,nofollow">`; `/a-propos-de-bassila` returns 200 with a single H1 and correct canonical.
6. **`JsonLdTest`** — `/` contains Organization + WebSite blocks; `/blog/{slug}` contains one Article block; `/profils/{verified}` contains one Person block; all blocks are valid JSON (parse + assert keys).
7. **`BreadcrumbsTest`** — 3-item component → 2 `<a>` + 1 `<span>`; `with-json-ld=true` pushes one block to `head`; `@once` prevents duplication when component is instantiated twice.
8. **`PerformanceHintsTest`** — `/` has at least one `<img>` with `fetchpriority="high"`; `/` has at least one `<link rel="preload" as="style">` for the font; no `<img>` on `/` lacks a `width` attribute (parsed via `Symfony\DomCrawler`).

**Not tested automatically** (manual post-deploy checklist, in spec only):
- Google Rich Results Test validation
- Lighthouse score
- Twitter Card Validator and Facebook Sharing Debugger
- PageSpeed Insights mobile score

## Open questions

None. All branching decisions are resolved:

- Tracking: **Google Search Console only** (no GA, no Plausible, no Matomo).
- Target audiences: **diaspora + natives P1**, **informational P2**.
- New content pages: **`/a-propos-de-bassila` + `/qui-sommes-nous`**, no `/contact` (contact email goes on `/qui-sommes-nous`).
- Production domain: **`config('app.url')` via env** — no hard-coded domain in code; only in `public/robots.txt` where it is edited manually at deploy time.
- Scope: **Approach 2 (Standard complet)** — approved. Sector/country landing pages and automated image pipeline deferred.
- `robots.txt`: **static file**, not Laravel-routed.
- Search Console verification: **HTML tag method**, not DNS.
- Meta migration: **soft backward compatibility** during migration, then cleanup.
- Breadcrumb JSON-LD: **opt-in via `with-json-ld` prop**, default off.

## Implementation order (rough)

1. Foundation: `config/seo.php`, `SeoData` DTO, `StructuredData` factory, unit tests.
2. Components: `<x-seo.meta-tags>`, `<x-seo.json-ld>`, `<x-breadcrumbs>`, component tests.
3. Layout integration: refactor `app.blade.php`, add `$seo` fallback from sections.
4. Views migration: one by one (public pages first, then auth pages with noindex).
5. Sitemap: `SitemapController`, route, cache, tests.
6. `robots.txt` rewrite.
7. Default OG image + brand-coloured placeholder at `public/images/og-default.png`.
8. New static pages: `/a-propos-de-bassila`, `/qui-sommes-nous`, nav + footer links.
9. Performance pass: download Unsplash assets, add `width`/`height` systematically, font preload, nginx cache headers.
10. Manual verification: Rich Results Test, Lighthouse, PageSpeed Insights, Twitter/Facebook debuggers.
11. Deployment checklist: Search Console verification, `robots.txt` URL replacement, sitemap submission.
