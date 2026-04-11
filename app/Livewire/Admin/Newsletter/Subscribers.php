<?php

namespace App\Livewire\Admin\Newsletter;

use App\Models\NewsletterSubscriber;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Subscribers extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all'; // all | active | pending | unsubscribed

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function unsubscribe(int $id): void
    {
        $sub = NewsletterSubscriber::findOrFail($id);
        $sub->update(['unsubscribed_at' => now()]);
    }

    public function reSubscribe(int $id): void
    {
        $sub = NewsletterSubscriber::findOrFail($id);
        $sub->update(['unsubscribed_at' => null, 'confirmed_at' => now()]);
    }

    public function delete(int $id): void
    {
        NewsletterSubscriber::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = NewsletterSubscriber::query();

        if ($this->search) {
            $query->where('email', 'ilike', '%' . $this->search . '%');
        }

        $query = match ($this->filter) {
            'active'       => $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at'),
            'pending'      => $query->whereNull('confirmed_at')->whereNull('unsubscribed_at'),
            'unsubscribed' => $query->whereNotNull('unsubscribed_at'),
            default        => $query,
        };

        $counts = [
            'all'          => NewsletterSubscriber::count(),
            'active'       => NewsletterSubscriber::active()->count(),
            'pending'      => NewsletterSubscriber::whereNull('confirmed_at')->whereNull('unsubscribed_at')->count(),
            'unsubscribed' => NewsletterSubscriber::whereNotNull('unsubscribed_at')->count(),
        ];

        $subscribers = $query->latest()->paginate(30);

        return view('livewire.admin.newsletter.subscribers', compact('subscribers', 'counts'));
    }
}
