<?php

use App\Livewire\Blog\CommentForm;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\User;
use Livewire\Livewire;

it('can submit a comment on a published post', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $post = BlogPost::factory()->create([
        'status'       => 'published',
        'published_at' => now()->subHour(),
    ]);

    Livewire::actingAs($user)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'Super article, merci pour le partage !')
        ->call('submit')
        ->assertSet('submitted', true);

    $this->assertDatabaseHas('blog_comments', [
        'blog_post_id' => $post->id,
        'user_id'      => $user->id,
        'moderated_at' => null,
    ]);
});

it('comment is not shown before moderation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = BlogPost::factory()->create([
        'status'       => 'published',
        'published_at' => now()->subHour(),
    ]);

    $comment = BlogComment::factory()->create([
        'blog_post_id' => $post->id,
        'user_id'      => $user->id,
        'content'      => 'Commentaire en attente',
        'moderated_at' => null,
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertDontSee('Commentaire en attente');
});

it('comment is shown after moderation', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = BlogPost::factory()->create([
        'status'       => 'published',
        'published_at' => now()->subHour(),
    ]);

    $comment = BlogComment::factory()->create([
        'blog_post_id' => $post->id,
        'user_id'      => $user->id,
        'content'      => 'Commentaire approuvé',
        'moderated_at' => now(),
    ]);

    $this->get(route('blog.show', $post->slug))
        ->assertSee('Commentaire approuvé');
});

it('validates minimum content length for comment', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($user)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'Hi')
        ->call('submit')
        ->assertHasErrors(['content']);
});
