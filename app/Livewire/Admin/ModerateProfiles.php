<?php

namespace App\Livewire\Admin;

use App\Mail\ProfileVerificationApproved;
use App\Mail\ProfileVerificationRejected;
use App\Models\ModerationLog;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ModerateProfiles extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    /** @var 'all'|'pending'|'verified' */
    #[Url]
    public string $filter = 'pending';

    // Rejection modal state
    public ?int $rejectingProfileId = null;
    public string $rejectionReason = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $profileId): void
    {
        $this->authorize('profiles.moderate');

        $profile = Profile::findOrFail($profileId);

        $profile->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        ModerationLog::create([
            'admin_user_id' => Auth::id(),
            'action'        => 'profile_approved',
            'subject_type'  => Profile::class,
            'subject_id'    => $profile->id,
        ]);

        $profile->load('user');
        Mail::to($profile->user)->queue(new ProfileVerificationApproved($profile));

        session()->flash('success', "Profil « {$profile->full_name} » approuvé.");
    }

    public function openRejectModal(int $profileId): void
    {
        $this->rejectingProfileId = $profileId;
        $this->rejectionReason = '';
    }

    public function closeRejectModal(): void
    {
        $this->rejectingProfileId = null;
        $this->rejectionReason = '';
    }

    public function confirmReject(): void
    {
        $this->authorize('profiles.moderate');

        if (! $this->rejectingProfileId) {
            return;
        }

        $profile = Profile::findOrFail($this->rejectingProfileId);

        $profile->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        ModerationLog::create([
            'admin_user_id' => Auth::id(),
            'action'        => 'profile_rejected',
            'subject_type'  => Profile::class,
            'subject_id'    => $profile->id,
            'notes'         => $this->rejectionReason ?: null,
        ]);

        $profile->load('user');
        Mail::to($profile->user)->queue(new ProfileVerificationRejected($profile, $this->rejectionReason ?: null));

        session()->flash('success', "Profil « {$profile->full_name} » rejeté.");

        $this->closeRejectModal();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $profiles = Profile::query()
            ->with(['sector', 'user'])
            ->when($this->filter === 'pending', fn ($q) => $q->where('is_verified', false))
            ->when($this->filter === 'verified', fn ($q) => $q->where('is_verified', true))
            ->when($this->search !== '', function ($q) {
                $term = '%' . mb_strtolower($this->search) . '%';
                $q->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(full_name) LIKE ?', [$term])
                      ->orWhereRaw('LOWER(job_title) LIKE ?', [$term])
                      ->orWhereRaw('LOWER(company) LIKE ?', [$term]);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.moderate-profiles', [
            'profiles' => $profiles,
            'rejectingProfile' => $this->rejectingProfileId
                ? Profile::find($this->rejectingProfileId)
                : null,
        ]);
    }
}
