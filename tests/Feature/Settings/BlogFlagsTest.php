<?php

use App\Livewire\Blog\CreatePost;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    Setting::create([
        'key' => 'blog.public_creation', 'value' => '0', 'type' => 'bool',
        'group' => 'blog', 'sort_order' => 1,
        'label' => 'Création publique', 'description' => null,
    ]);
    Setting::create([
        'key' => 'blog.require_moderation', 'value' => '1', 'type' => 'bool',
        'group' => 'blog', 'sort_order' => 2,
        'label' => 'Modération', 'description' => null,
    ]);
});

it('blocks a verified member from /blog/rediger when public_creation is disabled', function () {
    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');

    $this->actingAs($member)
        ->get(route('blog.create'))
        ->assertForbidden();
});

it('allows an admin through /blog/rediger even when public_creation is disabled', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('blog.create'))
        ->assertSuccessful();
});

it('allows a verified member through /blog/rediger when public_creation is enabled', function () {
    Setting::where('key', 'blog.public_creation')->update(['value' => '1']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');

    $this->actingAs($member)
        ->get(route('blog.create'))
        ->assertSuccessful();
});

it('forces status=draft for non-admin publications when require_moderation is enabled', function () {
    Setting::where('key', 'blog.public_creation')->update(['value' => '1']);

    $member = User::factory()->create(['email_verified_at' => now()]);
    $member->assignRole('user');
    $category = BlogCategory::factory()->create();

    Livewire::actingAs($member)
        ->test(CreatePost::class)
        ->set('title', 'My new post')
        ->set('slug', 'my-new-post')
        ->set('content', 'Lorem ipsum.')
        ->set('category_id', $category->id)
        ->set('status', 'published')
        ->call('save');

    $post = BlogPost::where('slug', 'my-new-post')->first();
    expect($post)->not->toBeNull()
        ->and($post->status)->toBe('draft')
        ->and($post->published_at)->toBeNull();
});

it('lets an admin publish directly even when require_moderation is enabled', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole('admin');
    $category = BlogCategory::factory()->create();

    Livewire::actingAs($admin)
        ->test(CreatePost::class)
        ->set('title', 'Admin post')
        ->set('slug', 'admin-post')
        ->set('content', 'Body.')
        ->set('category_id', $category->id)
        ->set('status', 'published')
        ->call('save');

    $post = BlogPost::where('slug', 'admin-post')->first();
    expect($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull();
});
