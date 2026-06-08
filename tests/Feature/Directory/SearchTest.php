<?php

use App\Livewire\Directory\SearchDirectory;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
use Livewire\Livewire;

it('renders the directory page', function () {
    $this->get(route('directory.index'))->assertSuccessful();
});

it('can search profiles by name', function () {
    Profile::factory()->create(['first_name' => 'Amadou', 'last_name' => 'Kouyaté', 'job_title' => 'Développeur']);
    Profile::factory()->create(['first_name' => 'Fatoumata', 'last_name' => 'Bah', 'job_title' => 'Médecin']);

    $component = Livewire::test(SearchDirectory::class)
        ->set('query', 'Amadou');

    $paginator = $component->instance()->results();

    expect($paginator->total())->toBeGreaterThan(0);
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

it('can filter profiles by education level', function () {
    Profile::factory()->create(['education_level' => 'Master (BAC+5)']);
    Profile::factory()->create(['education_level' => 'BAC']);

    $paginator = Livewire::test(SearchDirectory::class)
        ->set('educationLevel', 'Master (BAC+5)')
        ->instance()->results();

    expect($paginator->total())->toBe(1)
        ->and($paginator->first()->education_level)->toBe('Master (BAC+5)');
});

it('exposes métiers grouped by category, filtered by search', function () {
    Skill::create(['name' => 'Vulcanisateur test', 'category' => 'Métiers & Services', 'sort_order' => 0]);
    Skill::create(['name' => 'Cardiologie test', 'category' => 'Santé & Médecine', 'sort_order' => 0]);

    $groups = Livewire::test(SearchDirectory::class)
        ->set('skillSearch', 'vulcanisateur')
        ->instance()->skillGroups();

    expect($groups->has('Métiers & Services'))->toBeTrue()
        ->and($groups->has('Santé & Médecine'))->toBeFalse();
});

it('toggles a métier selection on and off', function () {
    $skill = Skill::create(['name' => 'Maçonnerie test', 'category' => 'Métiers & Services', 'sort_order' => 0]);

    $component = Livewire::test(SearchDirectory::class)
        ->call('toggleSkill', $skill->id);

    expect($component->get('skills'))->toContain($skill->id);

    $component->call('toggleSkill', $skill->id);

    expect($component->get('skills'))->not->toContain($skill->id);
});

it('toggles a category open and closed', function () {
    $component = Livewire::test(SearchDirectory::class)
        ->call('toggleCategory', 'Santé & Médecine');

    expect($component->get('expandedCategories'))->toContain('Santé & Médecine');

    $component->call('toggleCategory', 'Santé & Médecine');

    expect($component->get('expandedCategories'))->not->toContain('Santé & Médecine');
});

it('resets page when query changes', function () {
    $component = Livewire::test(SearchDirectory::class);

    $component->call('setPage', 2);
    $component->set('query', 'test');

    expect($component->get('paginators')['page'] ?? 1)->toBe(1);
});
