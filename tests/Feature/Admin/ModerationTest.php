<?php

use App\Livewire\Admin\ModerateProfiles;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('admin can access the admin dashboard', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful();
});

it('non-admin cannot access the admin panel', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole('member');

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('unauthenticated user is redirected from admin panel', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('admin can access every admin sub-page', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin);

    foreach (['admin.dashboard', 'admin.profiles', 'admin.posts', 'admin.comments'] as $route) {
        $this->get(route($route))->assertSuccessful();
    }
});

it('admin can approve a profile via the livewire component', function () {
    Mail::fake();

    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $profile = Profile::factory()->create(['is_verified' => false]);

    Livewire::actingAs($admin)
        ->test(ModerateProfiles::class)
        ->call('approve', $profile->id);

    $this->assertDatabaseHas('profiles', [
        'id'          => $profile->id,
        'is_verified' => true,
    ]);

    $this->assertDatabaseHas('moderation_logs', [
        'admin_user_id' => $admin->id,
        'action'        => 'profile_approved',
        'subject_id'    => $profile->id,
    ]);
});

it('admin can reject a profile with a reason', function () {
    Mail::fake();

    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $profile = Profile::factory()->create(['is_verified' => true, 'verified_at' => now()]);

    Livewire::actingAs($admin)
        ->test(ModerateProfiles::class)
        ->call('openRejectModal', $profile->id)
        ->set('rejectionReason', 'Informations incomplètes')
        ->call('confirmReject');

    $this->assertDatabaseHas('profiles', [
        'id'          => $profile->id,
        'is_verified' => false,
    ]);

    $this->assertDatabaseHas('moderation_logs', [
        'admin_user_id' => $admin->id,
        'action'        => 'profile_rejected',
        'subject_id'    => $profile->id,
        'notes'         => 'Informations incomplètes',
    ]);
});
