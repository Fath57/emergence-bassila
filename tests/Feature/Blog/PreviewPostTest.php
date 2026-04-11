<?php

use App\Models\BlogPost;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);

    $this->author = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->author->assignRole('member');

    $this->other = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->other->assignRole('member');

    $this->admin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->admin->assignRole('admin');

    $this->post = BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'Brouillon secret',
        'slug'    => 'brouillon-secret',
        'content' => '<p>contenu</p>',
        'status'  => 'draft',
    ]);
});

it('author can preview own draft', function () {
    $this->actingAs($this->author)
        ->get(route('blog.preview', $this->post))
        ->assertOk()
        ->assertSee('Brouillon secret')
        ->assertSee("Aperçu", false);
});

it('non author cannot preview draft', function () {
    $this->actingAs($this->other)
        ->get(route('blog.preview', $this->post))
        ->assertForbidden();
});

it('admin can preview any draft', function () {
    $this->actingAs($this->admin)
        ->get(route('blog.preview', $this->post))
        ->assertOk()
        ->assertSee('Brouillon secret');
});
