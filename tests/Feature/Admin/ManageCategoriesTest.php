<?php

use App\Livewire\Admin\ManageCategories;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->member->assignRole('member');
});

it('admin can see the manage categories page', function () {
    $this->actingAs($this->admin)
        ->get('/admin/categories')
        ->assertSuccessful()
        ->assertSee('Catégories')
        ->assertSee('Nouvelle catégorie');
});

it('forbids non-admin users from the page', function () {
    $this->actingAs($this->member)
        ->get('/admin/categories')
        ->assertForbidden();
});

it('admin can create a category with unique name', function () {
    Livewire::actingAs($this->admin)
        ->test(ManageCategories::class)
        ->set('newName', 'Environnement')
        ->call('create')
        ->assertHasNoErrors();

    $cat = BlogCategory::where('name', 'Environnement')->sole();
    expect($cat->slug)->toBe('environnement');
});

it('rejects duplicate category name on create', function () {
    BlogCategory::create(['name' => 'Actualités', 'slug' => 'actualites']);

    Livewire::actingAs($this->admin)
        ->test(ManageCategories::class)
        ->set('newName', 'Actualités')
        ->call('create')
        ->assertHasErrors(['newName' => 'unique']);

    expect(BlogCategory::where('name', 'Actualités')->count())->toBe(1);
});

it('admin can rename a category via inline edit', function () {
    $cat = BlogCategory::create(['name' => 'Old name', 'slug' => 'old-name']);

    Livewire::actingAs($this->admin)
        ->test(ManageCategories::class)
        ->call('startEdit', $cat->id)
        ->set('editingName', 'New name')
        ->call('saveEdit')
        ->assertHasNoErrors();

    $fresh = $cat->fresh();
    expect($fresh->name)->toBe('New name')
        ->and($fresh->slug)->toBe('new-name');
});

it('rename allows keeping the same name on the same row', function () {
    $cat = BlogCategory::create(['name' => 'Culture', 'slug' => 'culture']);

    Livewire::actingAs($this->admin)
        ->test(ManageCategories::class)
        ->call('startEdit', $cat->id)
        ->set('editingName', 'Culture')
        ->call('saveEdit')
        ->assertHasNoErrors();

    expect($cat->fresh()->name)->toBe('Culture');
});

it('rename rejects a name already used by another row', function () {
    BlogCategory::create(['name' => 'Culture', 'slug' => 'culture']);
    $other = BlogCategory::create(['name' => 'Autre', 'slug' => 'autre']);

    Livewire::actingAs($this->admin)
        ->test(ManageCategories::class)
        ->call('startEdit', $other->id)
        ->set('editingName', 'Culture')
        ->call('saveEdit')
        ->assertHasErrors(['editingName' => 'unique']);
});

it('deleting a category detaches posts via FK set null', function () {
    $cat = BlogCategory::create(['name' => 'Archivée', 'slug' => 'archivee']);

    $post = BlogPost::create([
        'user_id'     => $this->admin->id,
        'title'       => 'Post rattaché',
        'slug'        => 'post-rattache',
        'content'     => '<p>hi</p>',
        'status'      => 'draft',
        'category_id' => $cat->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ManageCategories::class)
        ->call('delete', $cat->id);

    expect(BlogCategory::find($cat->id))->toBeNull()
        ->and($post->fresh()->category_id)->toBeNull();
});

it('admin.categories route exists and resolves to the component', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.categories'))
        ->assertSuccessful()
        ->assertSeeLivewire(ManageCategories::class);
});
