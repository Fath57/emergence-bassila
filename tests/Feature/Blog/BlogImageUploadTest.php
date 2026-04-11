<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->member->assignRole('member');
});

it('authenticated member can upload jpeg under 10mb', function () {
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $this->actingAs($this->member)
        ->postJson(route('blog.upload-image'), ['image' => $file])
        ->assertOk()
        ->assertJsonStructure(['url']);
});

it('rejects svg upload', function () {
    $svg = UploadedFile::fake()->createWithContent(
        'logo.svg',
        '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40"/></svg>',
    );

    $this->actingAs($this->member)
        ->postJson(route('blog.upload-image'), ['image' => $svg])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
});

it('rejects files over 10mb', function () {
    $big = UploadedFile::fake()->image('big.jpg')->size(11 * 1024);

    $this->actingAs($this->member)
        ->postJson(route('blog.upload-image'), ['image' => $big])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('image');
});

it('forbids upload for guests', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->postJson(route('blog.upload-image'), ['image' => $file])
        ->assertUnauthorized();
});
