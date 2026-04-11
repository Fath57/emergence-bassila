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

it('caches the sitemap output between hits', function () {
    BlogPost::factory()->published()->create(['slug' => 'cached-post']);

    $first = get('/sitemap.xml')->getContent();

    // Create a new post AFTER the first hit — the cache should still serve the old one.
    BlogPost::factory()->published()->create(['slug' => 'uncached-post']);
    $second = get('/sitemap.xml')->getContent();

    expect($first)->toBe($second)
        ->and($second)->not->toContain('uncached-post');
});
