<?php

use App\Livewire\Blog\CreatePost;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->member = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $this->member->assignRole('member');
});

it('autosave does nothing when title is empty', function () {
    Livewire::actingAs($this->member)
        ->test(CreatePost::class)
        ->set('title', '')
        ->set('content', '<p>Some draft content</p>')
        ->call('autoSave');

    expect(BlogPost::count())->toBe(0);
});

it('autosave creates draft when title is non empty', function () {
    Livewire::actingAs($this->member)
        ->test(CreatePost::class)
        ->set('title', 'Mon brouillon')
        ->set('content', '<p>Brouillon.</p>')
        ->call('autoSave');

    $post = BlogPost::sole();
    expect($post->user_id)->toBe($this->member->id)
        ->and($post->status)->toBe('draft')
        ->and($post->content)->toContain('<p>Brouillon.</p>');
});

it('autosave updates existing draft on subsequent calls', function () {
    $cmp = Livewire::actingAs($this->member)
        ->test(CreatePost::class)
        ->set('title', 'V1')
        ->call('autoSave');

    $cmp->set('title', 'V2')
        ->set('content', '<p>Updated.</p>')
        ->call('autoSave');

    expect(BlogPost::count())->toBe(1);
    $post = BlogPost::sole();
    expect($post->title)->toBe('V2')
        ->and($post->content)->toContain('Updated');
});

it('save persists full content and meta fields', function () {
    $cmp = Livewire::actingAs($this->member)
        ->test(CreatePost::class)
        ->set('title', 'Titre complet')
        ->set('content', '<p>Contenu riche.</p>')
        ->set('excerpt', 'Résumé')
        ->set('metaTitle', 'SEO title')
        ->set('metaDescription', 'SEO description')
        ->set('status', 'draft')
        ->call('save');

    $post = BlogPost::sole();
    expect($post->title)->toBe('Titre complet')
        ->and($post->excerpt)->toBe('Résumé')
        ->and($post->meta_title)->toBe('SEO title')
        ->and($post->meta_description)->toBe('SEO description');
});

it('save rejects malicious script in content', function () {
    Livewire::actingAs($this->member)
        ->test(CreatePost::class)
        ->set('title', 'Evil')
        ->set('content', '<p>Hello</p><script>alert(1)</script>')
        ->call('save');

    $post = BlogPost::sole();
    expect($post->content)
        ->toContain('<p>Hello</p>')
        ->not->toContain('<script')
        ->not->toContain('alert(1)');
});

it('uploads featured image to public disk on save', function () {
    $image = UploadedFile::fake()->image('cover.jpg', 2000, 1200);

    Livewire::actingAs($this->member)
        ->test(CreatePost::class)
        ->set('title', 'With cover')
        ->set('content', '<p>ok</p>')
        ->set('featuredImage', $image)
        ->call('save');

    $post = BlogPost::sole();
    expect($post->featured_image_url)->toContain('blog/covers/');
});
