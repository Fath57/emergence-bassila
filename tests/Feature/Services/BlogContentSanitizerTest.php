<?php

use App\Services\BlogContentSanitizer;

beforeEach(function () {
    $this->sanitizer = new BlogContentSanitizer;
});

it('strips script tags', function () {
    $dirty = '<p>Hello</p><script>alert("xss")</script>';

    $clean = $this->sanitizer->clean($dirty);

    expect($clean)
        ->toContain('<p>Hello</p>')
        ->not->toContain('<script')
        ->not->toContain('alert');
});

it('allows whitelisted youtube iframe', function () {
    $dirty = '<p>Watch this</p><iframe src="https://www.youtube.com/embed/XYZ123"></iframe>';

    $clean = $this->sanitizer->clean($dirty);

    expect($clean)
        ->toContain('<iframe')
        ->toContain('youtube.com/embed/XYZ123');
});

it('strips non whitelist iframe', function () {
    $dirty = '<p>Text</p><iframe src="https://evil.com/malicious"></iframe>';

    $clean = $this->sanitizer->clean($dirty);

    expect($clean)
        ->toContain('<p>Text</p>')
        ->not->toContain('evil.com');
});

it('preserves french accents', function () {
    $dirty = '<p>Émergence à Bassila — des idées.</p>';

    $clean = $this->sanitizer->clean($dirty);

    expect($clean)
        ->toContain('Émergence')
        ->toContain('à')
        ->toContain('idées');
});

it('rejects javascript uri scheme in links', function () {
    $dirty = '<p><a href="javascript:alert(1)">click</a></p>';

    $clean = $this->sanitizer->clean($dirty);

    expect($clean)->not->toContain('javascript:');
});
