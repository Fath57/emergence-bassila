<?php

namespace App\Livewire\Admin\Newsletter;

use App\Models\NewsletterCampaign;
use App\Services\BlogContentSanitizer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CreateCampaign extends Component
{
    public string $subject     = '';
    public string $previewText = '';
    public string $content     = '';

    protected function rules(): array
    {
        return [
            'subject'     => ['required', 'string', 'max:255'],
            'previewText' => ['nullable', 'string', 'max:150'],
            'content'     => ['required', 'string'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    public function save(): void
    {
        $this->validate();

        $sanitizer = app(BlogContentSanitizer::class);

        $campaign = NewsletterCampaign::create([
            'subject'      => $this->subject,
            'preview_text' => $this->previewText ?: null,
            'content'      => $sanitizer->clean($this->content),
            'status'       => 'draft',
            'created_by'   => Auth::id(),
        ]);

        $this->redirect(route('admin.newsletter.edit', $campaign), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.newsletter.create-campaign');
    }
}
