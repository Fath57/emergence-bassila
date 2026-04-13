<?php

use App\Models\Profile;
use App\Models\User;

it('shows phone on profile page when show_phone is true', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'phone' => '+229 01 23 45 67',
        'show_phone' => true,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertSee('+229 01 23 45 67');
});

it('hides phone on profile page when show_phone is false', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'phone' => '+229 01 23 45 67',
        'show_phone' => false,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertDontSee('+229 01 23 45 67');
});

it('shows email_contact on profile page when show_email_contact is true', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'email_contact' => 'pro@example.com',
        'show_email_contact' => true,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertSee('pro@example.com');
});

it('hides email_contact on profile page when show_email_contact is false', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'email_contact' => 'pro@example.com',
        'show_email_contact' => false,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertDontSee('pro@example.com');
});

it('shows whatsapp on profile page when show_whatsapp is true', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'whatsapp' => '+229 97 00 00 01',
        'show_whatsapp' => true,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertSee('wa.me/22997000001')
        ->assertSee('+229 97 00 00 01');
});

it('hides whatsapp on profile page when show_whatsapp is false', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'whatsapp' => '+229 97 00 00 01',
        'show_whatsapp' => false,
        'is_verified' => true,
    ]);

    $this->get(route('profile.show', $profile))
        ->assertDontSee('wa.me/22997000001')
        ->assertDontSee('+229 97 00 00 01');
});
