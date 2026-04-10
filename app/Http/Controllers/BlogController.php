<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with(['user.profile', 'category'])
            ->published()
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get()
            ->filter(fn ($cat) => $cat->posts_count > 0);

        $featured = BlogPost::with(['user.profile', 'category'])
            ->published()
            ->latest('published_at')
            ->first();

        return view('blog.index', compact('posts', 'categories', 'featured'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::with(['user.profile', 'category', 'comments' => fn ($q) => $q->approved()->with('user.profile')->latest()])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', compact('post'));
    }
}
