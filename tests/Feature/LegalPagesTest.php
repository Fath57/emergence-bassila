<?php

it('serves the CGU page publicly', function () {
    $this->get('/cgu')
        ->assertOk()
        ->assertSee('Conditions Générales d\'Utilisation', false)
        ->assertSee('Bassila Emergence')
        ->assertSee('Dernière mise à jour');
});

it('serves the privacy policy publicly', function () {
    $this->get('/politique-de-confidentialite')
        ->assertOk()
        ->assertSee('Politique de confidentialité')
        ->assertSee('contact@bassila-emergence.org')
        ->assertSee('APDP')
        ->assertSee('RGPD');
});

it('serves the legal mentions publicly', function () {
    $this->get('/mentions-legales')
        ->assertOk()
        ->assertSee('Mentions légales')
        ->assertSee('Bassila Emergence');
});

it('links the legal pages from the footer', function () {
    $html = $this->get('/')->getContent();

    expect($html)->toContain(route('pages.cgu'))
        ->and($html)->toContain(route('pages.privacy'))
        ->and($html)->toContain(route('pages.legal'));
});
