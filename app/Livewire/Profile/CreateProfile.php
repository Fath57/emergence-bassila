<?php

namespace App\Livewire\Profile;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
use App\Services\AvatarGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateProfile extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1 — Identité
    public string $full_name = '';
    public string $job_title = '';
    public string $company = '';
    public ?int $sector_id = null;

    // Step 2 — Localisation
    public ?int $country_id = null;
    public string $city = '';
    public string $education_start_year = '';
    public string $education_end_year = '';

    // Step 3 — Contact
    public string $phone = '';
    public string $email_contact = '';

    // Step 3 — À propos
    public string $bio = '';
    public array $selectedSkills = [];

    // Step 4 — Médias
    public $avatar = null;
    public string $linkedin_url = '';
    public string $portfolio_url = '';

    private function stepRules(): array
    {
        return match ($this->step) {
            1 => [
                'full_name' => ['required', 'string', 'max:255'],
                'job_title' => ['required', 'string', 'max:255'],
                'sector_id' => ['required', 'exists:sectors,id'],
            ],
            2 => [
                'country_id'    => ['required', 'exists:countries,id'],
                'phone'         => ['nullable', 'string', 'max:30'],
                'email_contact' => ['nullable', 'email', 'max:255'],
            ],
            3 => [
                'bio' => ['nullable', 'string', 'max:500'],
            ],
            4 => [
                'avatar'        => ['nullable', 'image', 'max:2048', 'dimensions:min_width=200,min_height=200'],
                'linkedin_url'  => ['nullable', 'url'],
                'portfolio_url' => ['nullable', 'url'],
            ],
            default => [],
        };
    }

    protected function rules(): array
    {
        return [
            'full_name'            => ['required', 'string', 'max:255'],
            'bio'                  => ['nullable', 'string', 'max:500'],
            'avatar'               => ['nullable', 'image', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'country_id'           => ['required', 'exists:countries,id'],
            'job_title'            => ['required', 'string', 'max:255'],
            'sector_id'            => ['required', 'exists:sectors,id'],
            'phone'                => ['nullable', 'string', 'max:30'],
            'email_contact'        => ['nullable', 'email', 'max:255'],
            'linkedin_url'         => ['nullable', 'url'],
            'portfolio_url'        => ['nullable', 'url'],
        ];
    }

    public function nextStep(): void
    {
        $this->validate($this->stepRules());
        $this->step++;
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function toggleSkill(int $id): void
    {
        if (in_array($id, $this->selectedSkills)) {
            $this->selectedSkills = array_values(array_filter($this->selectedSkills, fn ($s) => $s !== $id));
        } else {
            $this->selectedSkills[] = $id;
        }
    }

    public function save(): void
    {
        $this->validate($this->stepRules());
        $this->validate();

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $avatarUrl = Storage::disk('public')->url($path);
        } else {
            $avatarGenerator = app(AvatarGenerator::class);
            $avatarUrl = $avatarGenerator->generate($this->full_name);
        }

        $country = Country::find($this->country_id);

        $profile = Profile::create([
            'user_id'              => Auth::id(),
            'full_name'            => $this->full_name,
            'bio'                  => $this->bio ?: null,
            'avatar_url'           => $avatarUrl,
            'city'                 => $this->city ?: null,
            'country'              => $country?->name,
            'country_id'           => $this->country_id,
            'job_title'            => $this->job_title,
            'company'              => $this->company ?: null,
            'sector_id'            => $this->sector_id,
            'education_start_year' => $this->education_start_year ?: null,
            'education_end_year'   => $this->education_end_year ?: null,
            'phone'                => $this->phone ?: null,
            'email_contact'        => $this->email_contact ?: null,
            'linkedin_url'         => $this->linkedin_url ?: null,
            'portfolio_url'        => $this->portfolio_url ?: null,
        ]);

        $profile->skills()->sync($this->selectedSkills);

        $this->redirect(route('profile.show', $profile), navigate: true);
    }

    public function render()
    {
        return view('livewire.profile.create-profile', [
            'sectors'   => Sector::orderBy('name')->get(),
            'skills'    => Skill::orderBy('name')->get(),
            'countries' => Country::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
