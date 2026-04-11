<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

it('renders the registration form', function () {
    $this->get(route('register'))->assertSuccessful();
});

it('can register a new user', function () {
    Livewire::test(Register::class)
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register');

    $this->assertDatabaseHas('users', [
        'email'      => 'test@example.com',
        'first_name' => 'Test',
        'last_name'  => 'User',
        'name'       => 'Test User', // generated column
    ]);
});

it('validates required fields on registration', function () {
    Livewire::test(Register::class)
        ->call('register')
        ->assertHasErrors(['first_name', 'last_name', 'email', 'password']);
});

it('rejects duplicate email on registration', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    Livewire::test(Register::class)
        ->set('first_name', 'Another')
        ->set('last_name', 'User')
        ->set('email', 'existing@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['email']);
});

it('rejects mismatched passwords', function () {
    Livewire::test(Register::class)
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('email', 'test@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'different')
        ->call('register')
        ->assertHasErrors(['password']);
});
