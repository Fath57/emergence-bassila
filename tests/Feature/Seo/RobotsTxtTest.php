<?php

/**
 * robots.txt is a static file served directly by nginx in production.
 * We assert on the file contents rather than via HTTP since Laravel's
 * test runner does not serve public/ files through the framework.
 */

beforeEach(function () {
    $this->robots = file_get_contents(public_path('robots.txt'));
});

it('robots.txt file exists in public directory', function () {
    expect(file_exists(public_path('robots.txt')))->toBeTrue();
});

it('disallows admin and private paths', function () {
    expect($this->robots)->toContain('Disallow: /admin')
        ->and($this->robots)->toContain('Disallow: /profil/creer')
        ->and($this->robots)->toContain('Disallow: /inscription')
        ->and($this->robots)->toContain('Disallow: /mes-articles')
        ->and($this->robots)->toContain('Disallow: /newsletter/');
});

it('allows crawling the root', function () {
    expect($this->robots)->toContain('User-agent: *')
        ->and($this->robots)->toContain('Allow: /');
});

it('includes Sitemap directive', function () {
    expect($this->robots)->toContain('Sitemap: ')
        ->and($this->robots)->toContain('/sitemap.xml');
});
