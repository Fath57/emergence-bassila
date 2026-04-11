<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PreviewPost extends Component
{
    public BlogPost $post;

    public function mount(BlogPost $post): void
    {
        if ($post->user_id !== Auth::id() && ! Auth::user()->can('posts.edit.any')) {
            abort(403);
        }

        $this->post = $post->load(['user', 'category']);
    }

    public function render()
    {
        return view('livewire.blog.preview-post', [
            'post' => $this->post,
        ]);
    }
}
