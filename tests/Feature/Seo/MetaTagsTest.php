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
    $post = BlogPost::factory()->published()->create(['slug' => 'meta-test-slug']);

    $html = get('/blog/meta-test-slug')->getContent();

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
