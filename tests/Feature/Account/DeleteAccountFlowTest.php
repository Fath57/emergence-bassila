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
