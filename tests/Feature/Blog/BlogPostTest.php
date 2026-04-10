<?php

use App\Livewire\Blog\CreatePost;
use App\Models\BlogPost;
use App\Models\User;
use Livewire\Livewire;

it('renders the blog index page', function () {
    $this->get(route('blog.index'))->assertSuccessful();
});

it('shows published blog posts', function () {
    $post = BlogPost::factory()->create([
        'status'       => 'published',
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('blog.index'))->assertSee($post->title);
});

it('does not show draft posts on public listing', function () {
    $draft = BlogPost::factory()->create(['status' => 'draft']);

    $this->get(route('blog.index'))->assertDontSee($draft->title);
});

it('shows a published blog post', function () {
    $post = BlogPost::factory()->create([
        'status'       => 'published',
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('blog.show', $post->slug))->assertSuccessful()->assertSee($post->title);
});

it('returns 404 for draft post accessed directly', function () {
    $draft = BlogPost::factory()->create(['status' => 'draft']);

    $this->get(route('blog.show', $draft->slug))->assertNotFound();
});

it('authenticated user can access create post form', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    $this->actingAs($user)->get(route('blog.create'))->assertSuccessful();
});

it('can create a blog post as draft', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    Livewire::actingAs($user)
        ->test(CreatePost::class)
        ->set('title', 'Mon premier article')
        ->set('slug', 'mon-premier-article')
        ->set('content', 'Contenu de l\'article de test pour vérification.')
        ->set('status', 'draft')
        ->call('save');

    $this->assertDatabaseHas('blog_posts', [
        'title'   => 'Mon premier article',
        'status'  => 'draft',
        'user_id' => $user->id,
    ]);
});

it('validates required fields for post creation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('user');

    Livewire::actingAs($user)
        ->test(CreatePost::class)
        ->call('save')
        ->assertHasErrors(['title', 'slug', 'content']);
});
