<?php

use App\Livewire\Profile\ProfileCard;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('shows email with icon when show_email_contact is true', function () {
    $user = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'email_contact' => 'pro@example.com',
        'show_email_contact' => true,
        'is_verified' => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('pro@example.com');
});

it('hides email when show_email_contact is false', function () {
    $user = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'email_contact' => 'pro@example.com',
        'show_email_contact' => false,
        'is_verified' => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertDontSee('pro@example.com');
});

it('shows phone with icon when show_phone is true', function () {
    $user = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'phone' => '+22960000000',
        'show_phone' => true,
        'is_verified' => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('+22960000000');
});

it('hides phone when show_phone is false', function () {
    $user = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'phone' => '+22960000000',
        'show_phone' => false,
        'is_verified' => true,
    ]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertDontSee('+22960000000');
});

it('authenticated user sees Contacter button linking to contact form', function () {
    $viewer = User::factory()->create(['is_active' => true]);
    $owner = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($viewer)
        ->test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('Contacter')
        ->assertSee(route('profile.show', $profile).'#contact-form');
});

it('guest sees Contacter button linking to login', function () {
    $owner = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    Livewire::test(ProfileCard::class, ['profile' => $profile])
        ->assertSee('Contacter')
        ->assertSee(route('login'));
});

it('profile owner does not see Contacter button', function () {
    $owner = User::factory()->create(['is_active' => true]);
    $profile = Profile::factory()->create(['user_id' => $owner->id]);

    Livewire::actingAs($owner)
        ->test(ProfileCard::class, ['profile' => $profile])
        ->assertDontSee('Contacter');
});
