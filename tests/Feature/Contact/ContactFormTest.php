<?php

use App\Livewire\Contact\ContactForm;
use App\Mail\ContactMessageReceived;
use App\Mail\ContactMessageSent;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('can send a contact message', function () {
    Mail::fake();

    $sender = User::factory()->create(['email_verified_at' => now()]);
    $sender->assignRole('user');

    $receiver = User::factory()->create(['email_verified_at' => now()]);
    $receiver->assignRole('user');
    $profile = Profile::factory()->create(['user_id' => $receiver->id]);

    Livewire::actingAs($sender)
        ->test(ContactForm::class, ['profile' => $profile])
        ->set('subject', 'Bonjour!')
        ->set('message', 'Je vous contacte pour discuter d\'une opportunité.')
        ->call('send')
        ->assertSet('sent', true);

    $this->assertDatabaseHas('contact_messages', [
        'from_user_id' => $sender->id,
        'to_user_id'   => $receiver->id,
        'subject'      => 'Bonjour!',
    ]);

    Mail::assertQueued(ContactMessageReceived::class);
    Mail::assertQueued(ContactMessageSent::class);
});

it('validates required fields in contact form', function () {
    Mail::fake();

    $sender = User::factory()->create(['email_verified_at' => now()]);
    $sender->assignRole('user');

    $receiver = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $receiver->id]);

    Livewire::actingAs($sender)
        ->test(ContactForm::class, ['profile' => $profile])
        ->call('send')
        ->assertHasErrors(['subject', 'message']);

    Mail::assertNothingQueued();
});

it('requires authentication to send contact message', function () {
    $profile = Profile::factory()->create();

    $this->get(route('profile.show', $profile))
        ->assertSee('Se connecter');
});
