<?php

use App\Livewire\Admin\ManageVillages;
use App\Models\Profile;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\VillageSeeder;
use Livewire\Livewire;

it('village seeder creates bassila villages', function () {
    $this->seed(VillageSeeder::class);

    expect(Village::count())->toBeGreaterThan(15);
    expect(Village::where('name', 'Bassila')->exists())->toBeTrue();
    expect(Village::where('arrondissement', 'Manigri')->count())->toBeGreaterThan(3);
});

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->member->assignRole('member');
});

it('admin can see the manage villages page', function () {
    $this->actingAs($this->admin)
        ->get('/admin/villages')
        ->assertSuccessful()
        ->assertSee('Villages')
        ->assertSee('Nouveau village');
});

it('forbids non-admin users from the page', function () {
    $this->actingAs($this->member)
        ->get('/admin/villages')
        ->assertForbidden();
});

it('admin can create a village with unique name and arrondissement', function () {
    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->set('newName', 'Koutché')
        ->set('newArrondissement', 'Bassila')
        ->set('newSortOrder', 9)
        ->set('newIsActive', true)
        ->call('create')
        ->assertHasNoErrors();

    $v = Village::where('name', 'Koutché')->where('arrondissement', 'Bassila')->sole();
    expect($v->sort_order)->toBe(9)
        ->and($v->is_active)->toBeTrue();
});

it('rejects duplicate village name in the same arrondissement', function () {
    Village::create([
        'name' => 'Doublon', 'arrondissement' => 'Bassila', 'is_active' => true, 'sort_order' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->set('newName', 'Doublon')
        ->set('newArrondissement', 'Bassila')
        ->call('create')
        ->assertHasErrors(['newName' => 'unique']);

    expect(Village::where('name', 'Doublon')->count())->toBe(1);
});

it('allows the same village name in a different arrondissement', function () {
    Village::create([
        'name' => 'Partagé', 'arrondissement' => 'Bassila', 'is_active' => true, 'sort_order' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->set('newName', 'Partagé')
        ->set('newArrondissement', 'Manigri')
        ->call('create')
        ->assertHasNoErrors();

    expect(Village::where('name', 'Partagé')->count())->toBe(2);
});

it('admin can update a village via inline edit', function () {
    $v = Village::create([
        'name' => 'Ancien', 'arrondissement' => 'Wawa', 'is_active' => true, 'sort_order' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->call('startEdit', $v->id)
        ->set('editingName', 'Nouveau')
        ->set('editingArrondissement', 'Wawa')
        ->set('editingSortOrder', 5)
        ->set('editingIsActive', false)
        ->call('saveEdit')
        ->assertHasNoErrors();

    $fresh = $v->fresh();
    expect($fresh->name)->toBe('Nouveau')
        ->and($fresh->sort_order)->toBe(5)
        ->and($fresh->is_active)->toBeFalse();
});

it('deleting a village nulls profiles village_id', function () {
    $v = Village::create([
        'name' => 'Lié', 'arrondissement' => 'Bassila', 'is_active' => true, 'sort_order' => 1,
    ]);

    $profile = Profile::factory()->for($this->admin)->create(['village_id' => $v->id]);

    Livewire::actingAs($this->admin)
        ->test(ManageVillages::class)
        ->call('delete', $v->id);

    expect(Village::find($v->id))->toBeNull()
        ->and($profile->fresh()->village_id)->toBeNull();
});

it('admin.villages route exists and resolves to the component', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.villages'))
        ->assertSuccessful()
        ->assertSeeLivewire(ManageVillages::class);
});
