<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class BlogImageUploader
{
    public const MAX_COVER_WIDTH  = 1600;
    public const MAX_COVER_HEIGHT = 900;
    public const MAX_INLINE_WIDTH = 1400;
    public const JPEG_QUALITY     = 85;

    public function uploadCover(UploadedFile $file, string $slug): string
    {
        $image = (new ImageManager(new GdDriver))->decodePath($file->getRealPath());
        $image->cover(self::MAX_COVER_WIDTH, self::MAX_COVER_HEIGHT);

        $filename = sprintf(
            'blog/covers/%s/%s-%s.jpg',
            now()->format('Y/m'),
            Str::slug($slug) ?: 'post',
            Str::random(8),
        );

        Storage::disk('public')->put(
            $filename,
            (string) $image->encode(new JpegEncoder(quality: self::JPEG_QUALITY)),
        );

        return Storage::disk('public')->url($filename);
    }

    public function uploadInline(UploadedFile $file): string
    {
        $image = (new ImageManager(new GdDriver))->decodePath($file->getRealPath());

        if ($image->width() > self::MAX_INLINE_WIDTH) {
            $image->scale(width: self::MAX_INLINE_WIDTH);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

        [$binary, $safeExt] = match ($ext) {
            'png'  => [(string) $image->encode(new PngEncoder),                             'png'],
            'webp' => [(string) $image->encode(new WebpEncoder(quality: self::JPEG_QUALITY)), 'webp'],
            'gif'  => [(string) $image->encode(new GifEncoder),                             'gif'],
            default => [(string) $image->encode(new JpegEncoder(quality: self::JPEG_QUALITY)), 'jpg'],
        };

        $filename = sprintf(
            'blog/inline/%s/%s.%s',
            now()->format('Y/m'),
            Str::random(40),
            $safeExt,
        );

        Storage::disk('public')->put($filename, $binary);

        return Storage::disk('public')->url($filename);
    }
}
