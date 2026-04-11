<?php

namespace App\Livewire\Blog;

use App\Models\BlogPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MyPosts extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    #[Url]
    public string $filter = 'all'; // 'all' | 'draft' | 'published' | 'archived'

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $postId): void
    {
        $post = BlogPost::findOrFail($postId);
        $this->authorize('delete', $post);

        $title = $post->title;
        $post->delete();

        session()->flash('success', "Article « {$title} » supprimé.");
    }

    public function render()
    {
        $userId = Auth::id();

        $counts = [
            'all'       => BlogPost::where('user_id', $userId)->count(),
            'draft'     => BlogPost::where('user_id', $userId)->where('status', 'draft')->count(),
            'published' => BlogPost::where('user_id', $userId)->where('status', 'published')->count(),
            'archived'  => BlogPost::where('user_id', $userId)->where('status', 'archived')->count(),
        ];

        $posts = BlogPost::query()
            ->where('user_id', $userId)
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->orderByRaw("
                CASE status
                    WHEN 'draft' THEN 1
                    WHEN 'published' THEN 2
                    ELSE 3
                END
            ")
            ->latest('updated_at')
            ->paginate(15);

        return view('livewire.blog.my-posts', compact('posts', 'counts'));
    }
}
