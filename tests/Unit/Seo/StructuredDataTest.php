<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Profile;
use App\Models\User;
use App\Support\Seo\StructuredData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('app.url', 'https://example.test');
    config()->set('seo.site_name', 'Bassila Émergence');
    config()->set('seo.default_og_image', '/images/og-default.png');
});

it('organization() returns a valid Organization payload', function () {
    config()->set('seo.socials', ['https://facebook.com/example']);

    $data = StructuredData::organization();

    expect($data['@context'])->toBe('https://schema.org')
        ->and($data['@type'])->toBe('Organization')
        ->and($data['name'])->toBe('Bassila Émergence')
        ->and($data['url'])->toBe('https://example.test')
        ->and($data['logo'])->toBe('https://example.test/images/logo.png')
        ->and($data['sameAs'])->toBe(['https://facebook.com/example']);
});

it('website() exposes a SearchAction pointing to /annuaire', function () {
    $data = StructuredData::website();

    expect($data['@type'])->toBe('WebSite')
        ->and($data['potentialAction']['@type'])->toBe('SearchAction')
        ->and($data['potentialAction']['target'])
        ->toBe('https://example.test/annuaire?q={search_term_string}')
        ->and($data['potentialAction']['query-input'])->toBe('required name=search_term_string');
});

it('article() returns an Article payload with ISO 8601 dates', function () {
    $user = User::factory()->create();
    Profile::factory()->verified()->create([
        'user_id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);
    $category = BlogCategory::factory()->create(['name' => 'Actualités']);
    $post = BlogPost::factory()->published()->create([
        'user_id' => $user->id,
        'title' => 'My post',
        'slug' => 'my-post',
        'category_id' => $category->id,
        'featured_image_url' => 'https://example.test/img/hero.jpg',
    ]);

    $data = StructuredData::article($post);

    expect($data['@type'])->toBe('Article')
        ->and($data['headline'])->toBe('My post')
        ->and($data['image'])->toBe('https://example.test/img/hero.jpg')
        ->and($data['datePublished'])->toMatch('/^\d{4}-\d{2}-\d{2}T/')
        ->and($data['dateModified'])->toMatch('/^\d{4}-\d{2}-\d{2}T/')
        ->and($data['author']['@type'])->toBe('Person')
        ->and($data['author']['name'])->toBe('Jane Doe')
        ->and($data['publisher']['@type'])->toBe('Organization')
        ->and($data['publisher']['name'])->toBe('Bassila Émergence')
        ->and($data['articleSection'])->toBe('Actualités')
        ->and($data['mainEntityOfPage'])->toBe('https://example.test/blog/my-post');
});

it('article() falls back to default OG image when no featured image', function () {
    $post = BlogPost::factory()->published()->create([
        'featured_image_url' => null,
    ]);

    $data = StructuredData::article($post);

    expect($data['image'])->toBe('https://example.test/images/og-default.png');
});

it('person() returns a Person payload for a verified profile', function () {
    $profile = Profile::factory()->verified()->create([
        'first_name' => 'Amina',
        'last_name'  => 'Traoré',
        'job_title'  => 'Médecin',
        'city'       => 'Cotonou',
        'country'    => 'Bénin',
        'avatar_url' => 'https://example.test/avatar.jpg',
    ]);

    $data = StructuredData::person($profile);

    expect($data['@type'])->toBe('Person')
        ->and($data['name'])->toBe('Amina Traoré')
        ->and($data['jobTitle'])->toBe('Médecin')
        ->and($data['image'])->toBe('https://example.test/avatar.jpg')
        ->and($data['url'])->toBe("https://example.test/profils/{$profile->id}")
        ->and($data['address']['@type'])->toBe('PostalAddress')
        ->and($data['address']['addressLocality'])->toBe('Cotonou')
        ->and($data['address']['addressCountry'])->toBe('Bénin');
});

it('person() omits empty fields', function () {
    // Use empty strings (DB has NOT NULL on job_title); empty values treated as absent.
    $profile = Profile::factory()->verified()->create([
        'job_title'  => '',
        'avatar_url' => '',
        'city'       => '',
        'country'    => '',
    ]);

    $data = StructuredData::person($profile);

    expect($data)->not->toHaveKey('jobTitle')
        ->and($data)->not->toHaveKey('image')
        ->and($data)->not->toHaveKey('address');
});

it('person() throws for an unverified profile', function () {
    $profile = Profile::factory()->create(['is_verified' => false]);

    StructuredData::person($profile);
})->throws(InvalidArgumentException::class);

it('breadcrumb() numbers items starting at position 1', function () {
    $data = StructuredData::breadcrumb([
        ['name' => 'Accueil', 'url' => 'https://example.test/'],
        ['name' => 'Blog',    'url' => 'https://example.test/blog'],
        ['name' => 'Mon article', 'url' => null],
    ]);

    expect($data['@type'])->toBe('BreadcrumbList')
        ->and($data['itemListElement'])->toHaveCount(3)
        ->and($data['itemListElement'][0]['position'])->toBe(1)
        ->and($data['itemListElement'][0]['name'])->toBe('Accueil')
        ->and($data['itemListElement'][0]['item'])->toBe('https://example.test/')
        ->and($data['itemListElement'][2]['position'])->toBe(3)
        ->and($data['itemListElement'][2])->not->toHaveKey('item');
});
