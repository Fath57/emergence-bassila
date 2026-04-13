<?php

use App\Mail\AccountDeletionCompleted;
use App\Models\AccountDeletionRequest;
use App\Models\BlogPost;
use App\Models\BlogComment;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('member');
});

it('purges a confirmed request whose scheduled_purge_at has passed', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('member');
    Profile::factory()->for($user)->create();
    $post = BlogPost::factory()->for($user)->create(['status' => 'published']);
    $comment = BlogComment::factory()->for($user)->create();

    $req = AccountDeletionRequest::factory()->for($user)->confirmed()->create([
        'scheduled_purge_at' => now()->subDay(),
    ]);

    $this->artisan('accounts:purge-expired')->assertExitCode(0);

    expect(User::find($user->id))->toBeNull()
        ->and(Profile::where('user_id', $user->id)->exists())->toBeFalse();

    $post->refresh();
    $comment->refresh();

    expect($post->user_id)->toBeNull()
        ->and($post->author_display_name)->toBe('Ancien membre')
        ->and($comment->user_id)->toBeNull()
        ->and($comment->author_display_name)->toBe('Membre supprimé');

    $req->refresh();
    expect($req->status)->toBe('purged')->and($req->purged_at)->not->toBeNull();

    Mail::assertQueued(AccountDeletionCompleted::class);
});

it('does not purge a confirmed request whose scheduled_purge_at is in the future', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    AccountDeletionRequest::factory()->for($user)->confirmed()->create([
        'scheduled_purge_at' => now()->addDays(5),
    ]);

    $this->artisan('accounts:purge-expired')->assertExitCode(0);

    expect(User::find($user->id))->not->toBeNull();
});

it('does not purge a cancelled request even if scheduled_purge_at has passed', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    AccountDeletionRequest::factory()->for($user)->confirmed()->create([
        'scheduled_purge_at' => now()->subDay(),
        'cancelled_at'       => now()->subHour(),
        'status'             => 'cancelled',
    ]);

    $this->artisan('accounts:purge-expired')->assertExitCode(0);

    expect(User::find($user->id))->not->toBeNull();
});
