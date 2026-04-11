<?php

namespace App\Livewire\Admin\Newsletter;

use App\Actions\Newsletter\LaunchCampaignSend;
use App\Models\NewsletterCampaign;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Campaigns extends Component
{
    use WithPagination;

    public ?int $confirmLaunchId = null;
    public ?int $confirmDeleteId = null;
    public string $testEmail     = '';
    public ?int $testSendId      = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    public function launch(int $id): void
    {
        $campaign = NewsletterCampaign::findOrFail($id);

        (new LaunchCampaignSend)->run($campaign);

        $this->confirmLaunchId = null;
        session()->flash('success', "Campagne « {$campaign->subject} » en cours d'envoi.");
    }

    public function delete(int $id): void
    {
        $campaign = NewsletterCampaign::findOrFail($id);

        if (! $campaign->isCancellable()) {
            session()->flash('error', 'Seules les campagnes en brouillon ou échouées peuvent être supprimées.');
            $this->confirmDeleteId = null;
            return;
        }

        $campaign->delete();
        $this->confirmDeleteId = null;
        session()->flash('success', 'Campagne supprimée.');
    }

    public function render()
    {
        $campaigns = NewsletterCampaign::with('createdBy')
            ->latest()
            ->paginate(20);

        return view('livewire.admin.newsletter.campaigns', compact('campaigns'));
    }
}
