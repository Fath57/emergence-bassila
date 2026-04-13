<?php

use App\Livewire\Auth\Register;
use Livewire\Livewire;

it('rejects registration when CGU checkbox is not checked', function () {
    Livewire::test(Register::class)
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('accepts_terms', false)
        ->call('register')
        ->assertHasErrors(['accepts_terms']);
});

it('accepts registration when CGU checkbox is checked', function () {
    Livewire::test(Register::class)
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->set('accepts_terms', true)
        ->call('register')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});
