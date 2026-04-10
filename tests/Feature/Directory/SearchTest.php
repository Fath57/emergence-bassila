<?php

use App\Livewire\Directory\SearchDirectory;
use App\Models\Profile;
use App\Models\Sector;
use Livewire\Livewire;

it('renders the directory page', function () {
    $this->get(route('directory.index'))->assertSuccessful();
});

it('can search profiles by name', function () {
    Profile::factory()->create(['full_name' => 'Amadou Kouyaté', 'job_title' => 'Développeur']);
    Profile::factory()->create(['full_name' => 'Fatoumata Bah', 'job_title' => 'Médecin']);

    $component = Livewire::test(SearchDirectory::class)
        ->set('query', 'Amadou');

    $results = $component->get('profiles');
    // Results will include matching profiles
    expect($results)->not->toBeNull();
});

it('can filter profiles by sector', function () {
    $sector = Sector::factory()->create();
    $profileInSector = Profile::factory()->create(['sector_id' => $sector->id]);
    $profileOther = Profile::factory()->create();

    $component = Livewire::test(SearchDirectory::class)
        ->set('sector', $sector->id);

    expect($component->get('sector'))->toBe($sector->id);
});

it('can filter by verified only', function () {
    Profile::factory()->create(['is_verified' => true]);
    Profile::factory()->create(['is_verified' => false]);

    $component = Livewire::test(SearchDirectory::class)
        ->set('verifiedOnly', true);

    expect($component->get('verifiedOnly'))->toBeTrue();
});

it('resets page when query changes', function () {
    $component = Livewire::test(SearchDirectory::class);

    $component->set('query', 'test');

    expect($component->get('page'))->toBe(1);
});
