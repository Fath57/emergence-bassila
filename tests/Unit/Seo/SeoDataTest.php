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
    config()->set('seo.default_og_image', '/images/og-default.png');

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
