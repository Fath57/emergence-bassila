<?php

use App\Livewire\Blog\EditPost;
use App\Models\BlogPost;
use App\Models\User;
use Livewire\Livewire;

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
        'title'   => 'Original',
        'slug'    => 'original',
        'content' => '<p>Original content</p>',
        'status'  => 'draft',
    ]);
});

it('member can edit own post', function () {
    Livewire::actingAs($this->author)
        ->test(EditPost::class, ['slug' => $this->post->slug])
        ->set('title', 'Updated')
        ->set('content', '<p>Updated content</p>')
        ->call('save');

    expect($this->post->fresh()->title)->toBe('Updated')
        ->and($this->post->fresh()->content)->toContain('<p>Updated content</p>');
});

it('member cannot edit others posts', function () {
    $this->actingAs($this->other)
        ->get(route('blog.edit', $this->post->slug))
        ->assertForbidden();
});

it('admin can edit any post', function () {
    Livewire::actingAs($this->admin)
        ->test(EditPost::class, ['slug' => $this->post->slug])
        ->set('title', 'Admin edit')
        ->set('content', '<p>By admin.</p>')
        ->call('save');

    expect($this->post->fresh()->title)->toBe('Admin edit');
});

it('edit page autosave persists sanitized content', function () {
    Livewire::actingAs($this->author)
        ->test(EditPost::class, ['slug' => $this->post->slug])
        ->set('title', 'Autosaved')
        ->set('content', '<p>Fine</p><script>alert(1)</script>')
        ->call('autoSave');

    $fresh = $this->post->fresh();
    expect($fresh->title)->toBe('Autosaved')
        ->and($fresh->content)->toContain('<p>Fine</p>')
        ->and($fresh->content)->not->toContain('<script');
});
