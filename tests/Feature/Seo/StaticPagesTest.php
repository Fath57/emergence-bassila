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

it('/qui-sommes-nous has exactly one h1', function () {
    $html = get('/qui-sommes-nous')->getContent();
    expect(substr_count($html, '<h1'))->toBe(1);
});

it('/a-propos-de-bassila has exactly one h1', function () {
    $html = get('/a-propos-de-bassila')->getContent();
    expect(substr_count($html, '<h1'))->toBe(1);
});
