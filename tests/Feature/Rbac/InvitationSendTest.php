<?php

use App\Livewire\Admin\InviteUser;
use App\Mail\InvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('admin can invite a new email', function () {
    Mail::fake();

    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(InviteUser::class)
        ->set('email', 'newperson@example.com')
        ->set('first_name', 'New')
        ->set('last_name', 'Person')
        ->set('role', 'member')
        ->set('message', 'Bienvenue !')
        ->call('send')
        ->assertHasNoErrors()
        ->assertRedirect();

    $invitation = UserInvitation::where('email', 'newperson@example.com')->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->role)->toBe('member')
        ->and($invitation->first_name)->toBe('New')
        ->and($invitation->last_name)->toBe('Person')
        ->and($invitation->message)->toBe('Bienvenue !')
        ->and($invitation->invited_by)->toBe($admin->id)
        ->and($invitation->token)->not->toBeEmpty()
        ->and($invitation->expires_at->isAfter(now()->addDays(6)))->toBeTrue();

    Mail::assertQueued(InvitationMail::class);
});

it('invite fails for an email that is already a user', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    User::factory()->create(['email' => 'existing@example.com']);

    Livewire::actingAs($admin)
        ->test(InviteUser::class)
        ->set('email', 'existing@example.com')
        ->set('role', 'member')
        ->call('send')
        ->assertHasErrors('email');

    expect(UserInvitation::where('email', 'existing@example.com')->exists())->toBeFalse();
});

it('invite fails when a pending invitation already exists for the email', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    UserInvitation::create([
        'email'      => 'pending@example.com',
        'role'       => 'member',
        'token'      => Str::random(64),
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(7),
    ]);

    Livewire::actingAs($admin)
        ->test(InviteUser::class)
        ->set('email', 'pending@example.com')
        ->set('role', 'member')
        ->call('send')
        ->assertHasErrors('email');

    expect(UserInvitation::where('email', 'pending@example.com')->count())->toBe(1);
});

it('forbids a non-admin user from accessing the invite form', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get('/admin/utilisateurs/inviter')
        ->assertForbidden();
});
