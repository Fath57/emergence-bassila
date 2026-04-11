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

it('all JSON-LD blocks on blog show are valid JSON', function () {
    BlogPost::factory()->published()->create(['slug' => 'valid-json-post']);

    $html = get('/blog/valid-json-post')->getContent();

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
    $blocks = $matches[1] ?? [];

    expect($blocks)->not->toBeEmpty();

    foreach ($blocks as $json) {
        // Reverse the <-escape so json_decode can parse cleanly
        $clean = str_replace('\u003c', '<', $json);
        $decoded = json_decode($clean, true);
        expect(json_last_error())->toBe(JSON_ERROR_NONE)
            ->and($decoded)->toBeArray();
    }
});
