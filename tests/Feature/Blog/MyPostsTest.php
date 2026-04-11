<?php

use App\Livewire\Blog\MyPosts;
use App\Models\BlogPost;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);

    $this->author = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->author->assignRole('member');

    $this->other = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->other->assignRole('member');
});

it('lists only own posts', function () {
    BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'Mine one',
        'slug'    => 'mine-one',
        'content' => '<p>c1</p>',
        'status'  => 'draft',
    ]);
    BlogPost::create([
        'user_id' => $this->other->id,
        'title'   => 'Theirs',
        'slug'    => 'theirs',
        'content' => '<p>c2</p>',
        'status'  => 'published',
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->author)
        ->test(MyPosts::class)
        ->assertSee('Mine one')
        ->assertDontSee('Theirs');
});

it('filters by status', function () {
    BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'Brouillon',
        'slug'    => 'brouillon',
        'content' => '<p>d</p>',
        'status'  => 'draft',
    ]);
    BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'Publie',
        'slug'    => 'publie',
        'content' => '<p>p</p>',
        'status'  => 'published',
        'published_at' => now(),
    ]);

    Livewire::actingAs($this->author)
        ->test(MyPosts::class)
        ->set('filter', 'draft')
        ->assertSee('Brouillon')
        ->assertDontSee('Publie');
});

it('allows author to delete own post', function () {
    $post = BlogPost::create([
        'user_id' => $this->author->id,
        'title'   => 'To delete',
        'slug'    => 'to-delete',
        'content' => '<p>x</p>',
        'status'  => 'draft',
    ]);

    Livewire::actingAs($this->author)
        ->test(MyPosts::class)
        ->call('delete', $post->id);

    expect(BlogPost::find($post->id))->toBeNull();
});
