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

    // Find all <img tags and ensure each has a width attribute
    preg_match_all('/<img\b[^>]*>/i', $html, $matches);
    $imgs = $matches[0] ?? [];

    $missing = array_filter($imgs, fn ($tag) => ! preg_match('/\bwidth\s*=/i', $tag));

    expect($missing)->toBe([]);
});

it('home still has no img without width when seeded with blog and profiles', function () {
    \App\Models\BlogPost::factory()->published()->count(3)->create([
        'featured_image_url' => 'https://example.test/img.jpg',
    ]);
    \App\Models\Profile::factory()->verified()->count(3)->create([
        'avatar_url' => 'https://example.test/avatar.jpg',
    ]);

    $html = get('/')->getContent();

    preg_match_all('/<img\b[^>]*>/i', $html, $matches);
    $imgs = $matches[0] ?? [];
    $missing = array_filter($imgs, fn ($tag) => ! preg_match('/\bwidth\s*=/i', $tag));

    expect($missing)->toBe([]);
});

it('home hero h1 has no animation class (LCP-protected)', function () {
    $html = get('/')->getContent();
    preg_match('/<h1\b[^>]*>/i', $html, $m);
    $h1Tag = $m[0] ?? '';
    expect($h1Tag)->not->toContain('anim-');
});

it('home hero img with fetchpriority=high has no animation class (LCP-protected)', function () {
    $html = get('/')->getContent();
    preg_match('/<img\b[^>]*fetchpriority="high"[^>]*>/i', $html, $m);
    $imgTag = $m[0] ?? '';
    expect($imgTag)->not->toBe('')
        ->and($imgTag)->not->toContain('anim-');
});
