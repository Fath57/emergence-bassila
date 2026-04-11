<?php

namespace App\Http\Controllers;

use App\Services\BlogImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogImageUploadController extends Controller
{
    public function __construct(private BlogImageUploader $uploader) {}

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240', 'mimes:jpg,jpeg,png,webp,gif'],
        ]);

        $url = $this->uploader->uploadInline($request->file('image'));

        return response()->json(['url' => $url]);
    }
}
