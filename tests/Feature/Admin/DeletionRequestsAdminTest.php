<?php

use App\Livewire\Admin\DeletionRequests;
use App\Mail\AdminDeletionPendingWithContent;
use App\Models\AccountDeletionRequest;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    \Spatie\Permission\Models\Role::findOrCreate('admin');
    \Spatie\Permission\Models\Role::findOrCreate('member');
});

it('blocks non-admins', function () {
    $user = User::factory()->create();
    $user->assignRole('member');
    $this->actingAs($user);

    $this->get('/admin/suppressions')->assertForbidden();
});

it('lists all deletion requests for an admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $u = User::factory()->create(['first_name' => 'Alice']);
    $u->assignRole('member');
    AccountDeletionRequest::factory()->for($u)->confirmed()->create();

    $this->actingAs($admin);

    Livewire::test(DeletionRequests::class)
        ->assertSee('Alice');
});

it('lets an admin cancel a pending request', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $u = User::factory()->create();
    $u->assignRole('member');
    $req = AccountDeletionRequest::factory()->for($u)->confirmed()->create();

    $this->actingAs($admin);

    Livewire::test(DeletionRequests::class)
        ->set('cancelReason.'.$req->id, 'abuse report to review first')
        ->call('cancel', $req->id)
        ->assertHasNoErrors();

    $req->refresh();
    expect($req->status)->toBe('cancelled')
        ->and($req->cancelled_by)->toBe($admin->id)
        ->and($req->cancel_reason)->toBe('abuse report to review first');
});

it('lets an admin force-purge a due request', function () {
    Mail::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $u = User::factory()->create();
    $u->assignRole('member');
    $req = AccountDeletionRequest::factory()->for($u)->confirmed()->create([
        'scheduled_purge_at' => now()->addDays(10),
    ]);

    $this->actingAs($admin);

    Livewire::test(DeletionRequests::class)
        ->call('forcePurge', $req->id)
        ->assertHasNoErrors();

    $req->refresh();
    expect($req->status)->toBe('purged')
        ->and(User::find($u->id))->toBeNull();
});

it('notifies the admin team when a confirmed deletion concerns a user with published posts', function () {
    Mail::fake();

    $user = User::factory()->create();
    $user->assignRole('member');
    BlogPost::factory()->for($user)->create(['status' => 'published']);

    $req = AccountDeletionRequest::startFor($user);

    $this->get(route('account.deletion.confirm', ['token' => $req->confirmation_token]))
        ->assertOk();

    Mail::assertQueued(AdminDeletionPendingWithContent::class, fn ($m) => $m->hasTo('contact@bassila-emergence.org'));
});
