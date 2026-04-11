<?php

namespace App\Livewire\Admin;

use App\Models\BlogComment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ModerateComments extends Component
{
    use WithPagination;

    /** @var 'pending'|'approved'|'all' */
    #[Url]
    public string $filter = 'pending';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $commentId): void
    {
        $this->authorize('comments.moderate');

        $comment = BlogComment::findOrFail($commentId);
        $comment->update(['moderated_at' => now()]);

        session()->flash('success', 'Commentaire approuvé.');
    }

    public function delete(int $commentId): void
    {
        $this->authorize('comments.moderate');

        $comment = BlogComment::findOrFail($commentId);
        $comment->delete();

        session()->flash('success', 'Commentaire supprimé.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $comments = BlogComment::query()
            ->with(['user', 'post'])
            ->when($this->filter === 'pending', fn ($q) => $q->whereNull('moderated_at'))
            ->when($this->filter === 'approved', fn ($q) => $q->whereNotNull('moderated_at'))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.moderate-comments', compact('comments'));
    }
}
