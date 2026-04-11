<?php

use App\Services\BlogImageUploader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->uploader = new BlogImageUploader;
});

it('cover resizes to 1600x900 jpeg', function () {
    $file = UploadedFile::fake()->image('huge.png', 3000, 2000);

    $url = $this->uploader->uploadCover($file, 'my-awesome-post');

    // File present in public disk
    expect($url)->toContain('blog/covers/');
    expect($url)->toEndWith('.jpg');

    // Extract stored path from the URL so we can verify dimensions
    $path = preg_replace('#^.*?(blog/covers/.+)$#', '$1', parse_url($url, PHP_URL_PATH) ?: $url);
    Storage::disk('public')->assertExists($path);

    $binary = Storage::disk('public')->get($path);
    $info = getimagesizefromstring($binary);
    expect($info)->not->toBeFalse();
    expect($info[0])->toBe(BlogImageUploader::MAX_COVER_WIDTH);
    expect($info[1])->toBe(BlogImageUploader::MAX_COVER_HEIGHT);
});

it('inline scales down wide images to max width', function () {
    $file = UploadedFile::fake()->image('wide.jpg', 2400, 1000);

    $url = $this->uploader->uploadInline($file);

    $path = preg_replace('#^.*?(blog/inline/.+)$#', '$1', parse_url($url, PHP_URL_PATH) ?: $url);
    Storage::disk('public')->assertExists($path);

    $binary = Storage::disk('public')->get($path);
    $info = getimagesizefromstring($binary);
    expect($info)->not->toBeFalse();
    expect($info[0])->toBe(BlogImageUploader::MAX_INLINE_WIDTH);
});

it('inline leaves small images unscaled', function () {
    $file = UploadedFile::fake()->image('small.jpg', 600, 400);

    $url = $this->uploader->uploadInline($file);

    $path = preg_replace('#^.*?(blog/inline/.+)$#', '$1', parse_url($url, PHP_URL_PATH) ?: $url);
    $binary = Storage::disk('public')->get($path);
    $info = getimagesizefromstring($binary);
    expect($info[0])->toBe(600);
});
