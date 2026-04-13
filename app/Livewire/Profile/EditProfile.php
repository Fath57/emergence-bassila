<?php

namespace App\Livewire\Profile;

use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
use App\Models\Village;
use App\Support\SectorSkillCategoryMap;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProfile extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public ?Profile $profile = null;

    public string $first_name = '';

    public string $last_name = '';

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

    public string $phone = '';

    public string $gender = '';

    public string $whatsapp = '';

    public ?int $village_id = null;

    public string $email_contact = '';

    public bool $show_phone = false;

    public bool $show_email_contact = false;

    public bool $show_whatsapp = false;

    public array $selectedSkills = [];

    // Skill picker state
    public string $skillSearch = '';

    /** @var array<int, string> */
    public array $expandedCategories = [];

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'country' => ['required', 'string'],
            'job_title' => ['required', 'string', 'max:255'],
            'sector_id' => ['required', 'exists:sectors,id'],
            'linkedin_url' => ['nullable', 'url'],
            'portfolio_url' => ['nullable', 'url'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email_contact' => ['nullable', 'email', 'max:255'],
            'show_phone' => ['boolean'],
            'show_email_contact' => ['boolean'],
            'show_whatsapp' => ['boolean'],
            'gender' => ['nullable', 'in:M,F'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'village_id' => ['nullable', 'exists:villages,id'],
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

        $this->first_name = $profile->first_name;
        $this->last_name = $profile->last_name;
        $this->bio = $profile->bio ?? '';
        $this->city = $profile->city ?? '';
        $this->country = $profile->country;
        $this->job_title = $profile->job_title;
        $this->company = $profile->company ?? '';
        $this->sector_id = $profile->sector_id;
        $this->education_start_year = $profile->education_start_year ?? '';
        $this->education_end_year = $profile->education_end_year ?? '';
        $this->linkedin_url = $profile->linkedin_url ?? '';
        $this->portfolio_url = $profile->portfolio_url ?? '';
        $this->phone = $profile->phone ?? '';
        $this->gender = $profile->gender ?? '';
        $this->whatsapp = $profile->whatsapp ?? '';
        $this->village_id = $profile->village_id;
        $this->email_contact = $profile->email_contact ?? '';
        $this->show_phone = (bool) ($profile->show_phone ?? false);
        $this->show_email_contact = (bool) ($profile->show_email_contact ?? false);
        $this->show_whatsapp = (bool) ($profile->show_whatsapp ?? false);
        $this->selectedSkills = $profile->skills->pluck('id')->toArray();

        // Pre-expand the categories of already-selected skills so the user
        // sees their current picks without clicking around.
        $this->expandedCategories = Skill::whereIn('id', $this->selectedSkills)
            ->whereNotNull('category')
            ->pluck('category')
            ->unique()
            ->values()
            ->all();

        // Also expand the sector's default category (if user has no skills yet)
        if (empty($this->expandedCategories) && $profile->sector_id) {
            $sector = Sector::find($profile->sector_id);
            $cat = $sector ? SectorSkillCategoryMap::for($sector->name) : null;
            if ($cat) {
                $this->expandedCategories[] = $cat;
            }
        }
    }

    public function updatedEmailContact(string $value): void
    {
        if (trim($value) === '') {
            $this->show_email_contact = false;
        }
    }

    public function updatedPhone(string $value): void
    {
        if (trim($value) === '') {
            $this->show_phone = false;
        }
    }

    public function updatedWhatsapp(string $value): void
    {
        if (trim($value) === '') {
            $this->show_whatsapp = false;
        }
    }

    public function toggleSkill(int $id): void
    {
        if (in_array($id, $this->selectedSkills)) {
            $this->selectedSkills = array_values(array_filter($this->selectedSkills, fn ($s) => $s !== $id));
        } else {
            $this->selectedSkills[] = $id;
        }
    }

    public function toggleCategory(string $category): void
    {
        if (in_array($category, $this->expandedCategories, true)) {
            $this->expandedCategories = array_values(array_filter(
                $this->expandedCategories,
                fn ($c) => $c !== $category,
            ));
        } else {
            $this->expandedCategories[] = $category;
        }
    }

    #[Computed]
    public function skillGroups(): Collection
    {
        $query = Skill::query()
            ->whereNotNull('category')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name');

        $search = trim($this->skillSearch);
        if ($search !== '') {
            $query->whereRaw('LOWER(name) LIKE ?', ['%'.Str::lower($search).'%']);
        }

        return $query->get()->groupBy('category');
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender ?: null,
            'bio' => $this->bio ?: null,
            'avatar_url' => $avatarUrl,
            'city' => $this->city ?: null,
            'country' => $this->country,
            'village_id' => $this->village_id,
            'job_title' => $this->job_title,
            'company' => $this->company ?: null,
            'sector_id' => $this->sector_id,
            'education_start_year' => $this->education_start_year ?: null,
            'education_end_year' => $this->education_end_year ?: null,
            'linkedin_url' => $this->linkedin_url ?: null,
            'portfolio_url' => $this->portfolio_url ?: null,
            'phone' => $this->phone ?: null,
            'whatsapp' => $this->whatsapp ?: null,
            'email_contact' => $this->email_contact ?: null,
            'show_phone' => $this->phone ? $this->show_phone : false,
            'show_email_contact' => $this->email_contact ? $this->show_email_contact : false,
            'show_whatsapp' => $this->whatsapp ? $this->show_whatsapp : false,
        ]);

        $this->profile->skills()->sync($this->selectedSkills);

        $this->redirect(route('profile.show', $this->profile), navigate: true);
    }

    public function render()
    {
        return view('livewire.profile.edit-profile', [
            'sectors' => Sector::orderBy('name')->get(),
            'selectedSkillModels' => Skill::whereIn('id', $this->selectedSkills)
                ->orderBy('name')->get(),
            'villages' => Village::query()
                ->active()
                ->orderBy('arrondissement')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
