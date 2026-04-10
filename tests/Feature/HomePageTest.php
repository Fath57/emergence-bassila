<?php

use App\Models\Profile;
use App\Models\Sector;
use App\Models\User;

it('renders the home page successfully', function () {
    $this->get('/')->assertOk();
});

it('includes OG title meta tag', function () {
    $this->get('/')->assertSee('og:title', false);
});

it('includes OG description meta tag', function () {
    $this->get('/')->assertSee('og:description', false);
});

it('includes OG type meta tag', function () {
    $this->get('/')->assertSee('og:type', false);
});

it('includes twitter card meta tag', function () {
    $this->get('/')->assertSee('twitter:card', false);
});

it('passes sectors with verified profiles to the view', function () {
    $sector = Sector::factory()->create(['name' => 'Santé']);
    Profile::factory()->verified()->create(['sector_id' => $sector->id]);

    $this->get('/')->assertSee('Secteurs représentés')->assertSee('Santé');
});

it('does not show sectors section when no verified profiles exist', function () {
    $this->get('/')->assertDontSee('Secteurs représentés');
});

it('shows recently verified profiles on the home page', function () {
    $profile = Profile::factory()->verified()->create(['full_name' => 'Amina Traoré']);

    $this->get('/')->assertSee('Amina Traoré');
});

it('hides stats bar when platform is empty', function () {
    $this->get('/')->assertDontSee('Membres inscrits');
});

it('shows stats bar when platform has members', function () {
    User::factory()->count(3)->create();
    $this->get('/')->assertSee('Membres inscrits');
});

it('shows register CTA to guests', function () {
    $this->get('/')->assertSee('Créer mon profil');
});

it('shows directory and blog CTAs to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertSee('Explorer l\'annuaire')
        ->assertSee('Lire le blog');
});

it('shows the comment ca marche section', function () {
    $this->get('/')->assertSee('Comment ça marche');
});

it('shows three steps in comment ca marche', function () {
    $this->get('/')
        ->assertSee('Inscris-toi')
        ->assertSee('Crée ton profil')
        ->assertSee('Connecte-toi');
});

it('shows testimonials section', function () {
    $this->get('/')->assertSee('Ils parlent de leur communauté');
});

it('shows sector on featured profile cards', function () {
    $sector = Sector::factory()->create(['name' => 'Santé']);
    Profile::factory()->verified()->create([
        'full_name' => 'Fatou Diallo',
        'sector_id' => $sector->id,
    ]);

    $this->get('/')->assertSee('Fatou Diallo')->assertSee('Santé');
});

it('shows newsletter section', function () {
    $this->get('/')->assertSee('Pas encore prêt');
});

it('shows recently verified label on profiles section', function () {
    $this->get('/')->assertSee('Membres récemment vérifiés');
});
