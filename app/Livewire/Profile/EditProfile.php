<?php

namespace App\Livewire\Profile;

use App\Models\Profile;
use App\Services\AvatarGenerator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProfile extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?Profile $profile = null;

    public string $full_name = '';
    public string $bio = '';
    public $avatar = null;
    public string $city = '';
    public string $country = '';
    public string $job_title = '';
    public string $company = '';
    public ?int $sector_id = null;
    public string $education_start_year = '';
    public string $education_end_year = '';
    public string $linkedin_url = '';
    public string $portfolio_url = '';
    public array $selectedSkills = [];

    protected function rules(): array
    {
        return [
            'full_name'            => ['required', 'string', 'max:255'],
            'bio'                  => ['nullable', 'string', 'max:500'],
            'avatar'               => ['nullable', 'image', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'country'              => ['required', 'string'],
            'job_title'            => ['required', 'string', 'max:255'],
            'sector_id'            => ['required', 'exists:sectors,id'],
            'linkedin_url'         => ['nullable', 'url'],
            'portfolio_url'        => ['nullable', 'url'],
        ];
    }

    public function mount(): void
    {
        $profile = Auth::user()->profile;

        if (! $profile) {
            $this->redirect(route('profile.create'), navigate: true);
            return;
        }

        $this->authorize('update', $profile);

        $this->profile = $profile;

        $this->full_name            = $profile->full_name;
        $this->bio                  = $profile->bio ?? '';
        $this->city                 = $profile->city ?? '';
        $this->country              = $profile->country;
        $this->job_title            = $profile->job_title;
        $this->company              = $profile->company ?? '';
        $this->sector_id            = $profile->sector_id;
        $this->education_start_year = $profile->education_start_year ?? '';
        $this->education_end_year   = $profile->education_end_year ?? '';
        $this->linkedin_url         = $profile->linkedin_url ?? '';
        $this->portfolio_url        = $profile->portfolio_url ?? '';
        $this->selectedSkills       = $profile->skills->pluck('id')->toArray();
    }

    public function save(): void
    {
        $this->authorize('update', $this->profile);

        $this->validate();

        $avatarUrl = $this->profile->avatar_url;

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $avatarUrl = Storage::disk('public')->url($path);
        }

        $this->profile->update([
            'full_name'            => $this->full_name,
            'bio'                  => $this->bio ?: null,
            'avatar_url'           => $avatarUrl,
            'city'                 => $this->city ?: null,
            'country'              => $this->country,
            'job_title'            => $this->job_title,
            'company'              => $this->company ?: null,
            'sector_id'            => $this->sector_id,
            'education_start_year' => $this->education_start_year ?: null,
            'education_end_year'   => $this->education_end_year ?: null,
            'linkedin_url'         => $this->linkedin_url ?: null,
            'portfolio_url'        => $this->portfolio_url ?: null,
        ]);

        $this->profile->skills()->sync($this->selectedSkills);

        $this->redirect(route('profile.show', $this->profile), navigate: true);
    }

    public function render()
    {
        return view('livewire.profile.edit-profile');
    }
}
