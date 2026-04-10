<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarGenerator
{
    /**
     * Generate a PNG avatar from the given name's initials,
     * upload it to S3, and return the public URL.
     */
    public function generate(string $name): string
    {
        $initials = $this->extractInitials($name);

        $size = 200;

        $image = imagecreate($size, $size);

        // Background: #0066CC
        $bg = imagecolorallocate($image, 0, 102, 204);
        imagefill($image, 0, 0, $bg);

        // Text: white
        $textColor = imagecolorallocate($image, 255, 255, 255);

        // Use built-in font 5 (largest GD built-in font, ~9x15 px per char)
        $font       = 5;
        $charWidth  = imagefontwidth($font);
        $charHeight = imagefontheight($font);

        $textWidth  = strlen($initials) * $charWidth;
        $textHeight = $charHeight;

        $x = (int) (($size - $textWidth) / 2);
        $y = (int) (($size - $textHeight) / 2);

        imagestring($image, $font, $x, $y, $initials, $textColor);

        // Capture PNG to a temporary buffer
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        imagedestroy($image);

        // Store on S3
        $filename = 'avatars/generated/' . Str::uuid() . '.png';
        Storage::disk('s3')->put($filename, $imageData, 'public');

        return Storage::disk('s3')->url($filename);
    }

    /**
     * Extract up to 2 uppercase initials from a name string.
     */
    private function extractInitials(string $name): string
    {
        $words    = preg_split('/\s+/', trim($name));
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            if (! empty($word)) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            }
        }

        return $initials ?: 'U';
    }
}
