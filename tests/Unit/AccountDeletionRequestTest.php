<?php

use App\Models\AccountDeletionRequest;
use App\Models\User;

it('is created in requested state with a token', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);

    expect($req->status)->toBe('requested')
        ->and($req->confirmation_token)->toHaveLength(64)
        ->and($req->requested_at)->not->toBeNull();
});

it('transitions from requested to confirmed, clears token, schedules purge at now+30d', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);

    $req->confirm();

    expect($req->status)->toBe('confirmed')
        ->and($req->confirmed_at)->not->toBeNull()
        ->and($req->confirmation_token)->toBeNull()
        ->and($req->scheduled_purge_at->isSameDay(now()->addDays(30)))->toBeTrue();
});

it('cannot confirm a cancelled request', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);
    $req->cancel($user, 'changed mind');

    expect(fn () => $req->confirm())
        ->toThrow(LogicException::class);
});

it('cannot confirm a purged request', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::factory()->for($user)->purged()->create();

    expect(fn () => $req->confirm())
        ->toThrow(LogicException::class);
});

it('can cancel from requested state', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);

    $req->cancel($user, 'changed mind');

    expect($req->status)->toBe('cancelled')
        ->and($req->cancelled_at)->not->toBeNull()
        ->and($req->cancelled_by)->toBe($user->id)
        ->and($req->cancel_reason)->toBe('changed mind');
});

it('can cancel from confirmed state', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);
    $req->confirm();

    $req->cancel($user);

    expect($req->status)->toBe('cancelled');
});

it('cannot cancel an already cancelled request', function () {
    $user = User::factory()->create();
    $req = AccountDeletionRequest::startFor($user);
    $req->cancel($user);

    expect(fn () => $req->cancel($user))->toThrow(LogicException::class);
});

it('dueForPurge scope returns only confirmed requests whose purge date has passed', function () {
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    $u3 = User::factory()->create();

    $due = AccountDeletionRequest::factory()->for($u1)->confirmed()->create([
        'scheduled_purge_at' => now()->subDay(),
    ]);
    AccountDeletionRequest::factory()->for($u2)->confirmed()->create([
        'scheduled_purge_at' => now()->addDays(10),
    ]);
    AccountDeletionRequest::factory()->for($u3)->create(['status' => 'requested']);

    $ids = AccountDeletionRequest::dueForPurge()->pluck('id');

    expect($ids)->toHaveCount(1)->and($ids->first())->toBe($due->id);
});
