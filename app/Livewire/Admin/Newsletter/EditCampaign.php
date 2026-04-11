<?php

namespace App\Livewire\Admin\Newsletter;

use App\Actions\Newsletter\LaunchCampaignSend;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Services\BlogContentSanitizer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class EditCampaign extends Component
{
    public NewsletterCampaign $campaign;

    public string $subject     = '';
    public string $previewText = '';
    public string $content     = '';

    public bool $showLaunchModal = false;

    protected function rules(): array
    {
        return [
            'subject'     => ['required', 'string', 'max:255'],
            'previewText' => ['nullable', 'string', 'max:150'],
            'content'     => ['required', 'string'],
        ];
    }

    public function mount(NewsletterCampaign $campaign): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);

        $this->campaign    = $campaign;
        $this->subject     = $campaign->subject;
        $this->previewText = $campaign->preview_text ?? '';
        $this->content     = $campaign->content;
    }

    public function save(): void
    {
        if (! $this->campaign->isEditable()) {
            session()->flash('error', 'Cette campagne ne peut plus être modifiée.');
            return;
        }

        $this->validate();

        $sanitizer = app(BlogContentSanitizer::class);

        $this->campaign->update([
            'subject'      => $this->subject,
            'preview_text' => $this->previewText ?: null,
            'content'      => $sanitizer->clean($this->content),
        ]);

        session()->flash('success', 'Campagne enregistrée.');
    }

    public function launch(): void
    {
        if (! $this->campaign->isEditable()) {
            session()->flash('error', 'Cette campagne ne peut pas être lancée.');
            $this->showLaunchModal = false;
            return;
        }

        // Save current state first
        $this->save();

        (new LaunchCampaignSend)->run($this->campaign->fresh());

        $this->showLaunchModal = false;
        $this->redirect(route('admin.newsletter'), navigate: true);
        session()->flash('success', "Campagne « {$this->campaign->subject} » en cours d'envoi.");
    }

    public function render()
    {
        $activeCount = NewsletterSubscriber::active()->count();

        return view('livewire.admin.newsletter.edit-campaign', compact('activeCount'));
    }
}
