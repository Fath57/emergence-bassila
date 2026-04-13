<?php

use App\Livewire\Profile\DeleteAccount;
use App\Mail\AccountDeletionRequested;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('admin');
    \Spatie\Permission\Models\Role::findOrCreate('member');
});

it('creates a deletion request and sends confirmation email', function () {
    Mail::fake();

    $user = User::factory()->create(['password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'secret1234')
        ->set('understood', true)
        ->call('submit')
        ->assertHasNoErrors();

    expect(AccountDeletionRequest::where('user_id', $user->id)->requested()->exists())->toBeTrue();
    Mail::assertQueued(AccountDeletionRequested::class, fn ($m) => $m->hasTo($user->email));
});

it('rejects when password is wrong', function () {
    $user = User::factory()->create(['password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'wrong')
        ->set('understood', true)
        ->call('submit')
        ->assertHasErrors(['password']);

    expect(AccountDeletionRequest::where('user_id', $user->id)->exists())->toBeFalse();
});

it('rejects when understood is not checked', function () {
    $user = User::factory()->create(['password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $this->actingAs($user);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'secret1234')
        ->set('understood', false)
        ->call('submit')
        ->assertHasErrors(['understood']);
});

it('rejects when the user is the last active admin', function () {
    $admin = User::factory()->create(['password' => Hash::make('secret1234')]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Livewire::test(DeleteAccount::class)
        ->set('password', 'secret1234')
        ->set('understood', true)
        ->call('submit')
        ->assertHasErrors(['lockout']);

    expect(AccountDeletionRequest::count())->toBe(0);
});

it('confirms a deletion when clicking a valid token URL, disables the account, and schedules the purge', function () {
    Mail::fake();

    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('member');
    $req = AccountDeletionRequest::startFor($user);
    $token = $req->confirmation_token;

    $this->get(route('account.deletion.confirm', ['token' => $token]))
        ->assertOk()
        ->assertSee('confirmée');

    $user->refresh();
    $req->refresh();

    expect($req->status)->toBe('confirmed')
        ->and($req->scheduled_purge_at)->not->toBeNull()
        ->and($user->is_active)->toBeFalse();

    Mail::assertQueued(\App\Mail\AccountDeletionConfirmed::class);
});

it('rejects an expired confirmation token (>24h)', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    $req = AccountDeletionRequest::startFor($user);
    $req->update(['requested_at' => now()->subHours(25)]);
    $token = $req->confirmation_token;

    $this->get(route('account.deletion.confirm', ['token' => $token]))
        ->assertOk()
        ->assertSee('expiré');

    $req->refresh();
    expect($req->status)->toBe('cancelled');
});

it('rejects an unknown token with 404', function () {
    $this->get(route('account.deletion.confirm', ['token' => str_repeat('x', 64)]))
        ->assertNotFound();
});

it('lets a user in grace period cancel their pending deletion', function () {
    Mail::fake();

    $user = User::factory()->create(['is_active' => false, 'password' => Hash::make('secret1234')]);
    $user->assignRole('member');
    $req = AccountDeletionRequest::factory()->for($user)->confirmed()->create();

    // Log in via the Livewire Login component. is_active=false would normally block,
    // but the pending deletion exception allows it through.
    Livewire::test(\App\Livewire\Auth\Login::class)
        ->set('email', $user->email)
        ->set('password', 'secret1234')
        ->call('login')
        ->assertHasNoErrors();

    $this->actingAs($user->fresh());

    // Any page other than cancel/confirm/logout should redirect to cancel.
    $this->get('/')->assertRedirect(route('account.deletion.cancel'));

    Livewire::test(\App\Livewire\Account\CancelDeletion::class)
        ->call('cancel')
        ->assertRedirect('/');

    $req->refresh();
    $user->refresh();

    expect($req->status)->toBe('cancelled')
        ->and($user->is_active)->toBeTrue();

    Mail::assertQueued(\App\Mail\AccountDeletionCancelled::class);
});
