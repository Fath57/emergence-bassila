<?php

namespace App\Livewire\Admin\Newsletter;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PreviewCampaign extends Component
{
    public NewsletterCampaign $campaign;

    public function mount(NewsletterCampaign $campaign): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
        $this->campaign = $campaign;
    }

    public function render()
    {
        return view('livewire.admin.newsletter.preview-campaign');
    }
}
