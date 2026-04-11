<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePosts extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    /** @var 'all'|'draft'|'published'|'archived' */
    #[Url]
    public string $status = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function publish(int $postId): void
    {
        $this->authorize('posts.edit.any');

        $post = BlogPost::findOrFail($postId);
        $post->update(['status' => 'published', 'published_at' => now()]);

        session()->flash('success', "Article « {$post->title} » publié.");
    }

    public function unpublish(int $postId): void
    {
        $this->authorize('posts.edit.any');

        $post = BlogPost::findOrFail($postId);
        $post->update(['status' => 'draft']);

        session()->flash('success', "Article « {$post->title} » dépublié.");
    }

    public function delete(int $postId): void
    {
        $this->authorize('posts.edit.any');

        $post = BlogPost::findOrFail($postId);
        $title = $post->title;
        $post->delete();

        session()->flash('success', "Article « {$title} » supprimé.");
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $posts = BlogPost::query()
            ->with(['user', 'category'])
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q) {
                $term = '%' . mb_strtolower($this->search) . '%';
                $q->whereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.manage-posts', compact('posts'));
    }
}
