<?php

use App\Models\User;
use function Pest\Laravel\get;

it('home has multiple scroll-reveal sections', function () {
    $html = get('/')->getContent();
    expect(substr_count($html, 'anim-reveal'))->toBeGreaterThan(5);
});

it('home hero has entrance animation classes', function () {
    $html = get('/')->getContent();
    expect($html)->toContain('anim-hero-subtitle')
        ->and($html)->toContain('anim-hero-cta');
});

it('home stats bar has count-up class when platform has data', function () {
    User::factory()->count(5)->create();
    $html = get('/')->getContent();
    expect($html)->toContain('anim-count');
});

it('count-up elements render final numeric value in HTML (SEO-safe)', function () {
    User::factory()->count(5)->create();
    $html = get('/')->getContent();
    // The <div class="... anim-count">N</div> must contain a digit, not "0".
    expect($html)->toMatch('/<div[^>]*anim-count[^>]*>\s*\d+\s*<\/div>/');
});

it('qui-sommes-nous has scroll-reveal', function () {
    $html = get('/qui-sommes-nous')->getContent();
    expect($html)->toContain('anim-reveal');
});

it('a-propos-de-bassila has scroll-reveal', function () {
    $html = get('/a-propos-de-bassila')->getContent();
    expect($html)->toContain('anim-reveal');
});
