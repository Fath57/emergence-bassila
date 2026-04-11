<?php

use Illuminate\Support\Facades\View;

function renderBreadcrumbFixture(array $items, bool $withJsonLd): string
{
    return View::make('tests.breadcrumb-fixture', [
        'items'      => $items,
        'withJsonLd' => $withJsonLd,
    ])->render();
}

it('renders 2 anchors and 1 span for a 3-item breadcrumb', function () {
    $items = [
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Blog',    'url' => 'https://example.test/blog'],
        ['name' => 'Mon article', 'url' => null],
    ];

    $html = renderBreadcrumbFixture($items, withJsonLd: false);

    expect(substr_count($html, '<a '))->toBe(2)
        ->and(substr_count($html, '<span class="text-gray-700'))->toBe(1)
        ->and($html)->toContain('Mon article');
});

it('does NOT emit JSON-LD when with-json-ld is false', function () {
    $items = [
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Blog',    'url' => null],
    ];

    $html = renderBreadcrumbFixture($items, withJsonLd: false);

    expect($html)->not->toContain('BreadcrumbList');
});

it('emits one BreadcrumbList JSON-LD block when with-json-ld is true', function () {
    $items = [
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Blog',    'url' => 'https://example.test/blog'],
        ['name' => 'Mon article', 'url' => null],
    ];

    $html = renderBreadcrumbFixture($items, withJsonLd: true);

    expect(substr_count($html, '"BreadcrumbList"'))->toBe(1);
});
