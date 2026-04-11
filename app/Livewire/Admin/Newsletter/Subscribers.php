<?php

namespace App\Livewire\Admin\Newsletter;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Subscribers extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    public function render()
    {
        return view('livewire.admin.newsletter.subscribers');
    }
}
