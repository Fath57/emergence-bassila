<?php

use App\Livewire\Blog\CommentForm;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    Setting::create([
        'key' => 'comments.enabled', 'value' => '1', 'type' => 'bool',
        'group' => 'comments', 'sort_order' => 1,
        'label' => 'Commentaires activés', 'description' => null,
    ]);
    Setting::create([
        'key' => 'comments.require_moderation', 'value' => '1', 'type' => 'bool',
        'group' => 'comments', 'sort_order' => 2,
        'label' => 'Modération', 'description' => null,
    ]);
});

it('refuses to save a comment when comments.enabled is false', function () {
    Setting::where('key', 'comments.enabled')->update(['value' => '0']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('member');
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($member)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'This should not be saved.')
        ->call('submit')
        ->assertHasErrors('content');

    expect($post->comments()->count())->toBe(0);
});

it('saves a comment with moderated_at=null when require_moderation is true', function () {
    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('member');
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($member)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'Pending approval please.')
        ->call('submit');

    $comment = $post->comments()->first();
    expect($comment)->not->toBeNull()
        ->and($comment->moderated_at)->toBeNull();
});

it('auto-approves a comment when require_moderation is false', function () {
    Setting::where('key', 'comments.require_moderation')->update(['value' => '0']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('member');
    $post = BlogPost::factory()->create(['status' => 'published', 'published_at' => now()]);

    Livewire::actingAs($member)
        ->test(CommentForm::class, ['post' => $post])
        ->set('content', 'Instant.')
        ->call('submit');

    $comment = $post->comments()->first();
    expect($comment->moderated_at)->not->toBeNull();
});
