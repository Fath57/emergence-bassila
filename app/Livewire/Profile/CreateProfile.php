<?php

namespace App\Livewire\Profile;

use App\Models\Country;
use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
use App\Services\AvatarGenerator;
use App\Support\SectorSkillCategoryMap;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateProfile extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // Step 1 — Identité
    public string $first_name = '';
    public string $last_name = '';
    public string $job_title = '';
    public string $company = '';
    public ?int $sector_id = null;

    // Step 2 — Localisation
    public ?int $country_id = null;
    public string $city = '';
    public string $education_start_year = '';
    public string $education_end_year = '';

    // Step 2 — Contact
    public string $phone = '';
    public string $email_contact = '';
    public bool $show_phone = false;
    public bool $show_email_contact = false;

    // Step 3 — À propos
    public string $bio = '';
    public array $selectedSkills = [];

    // Skill picker state
    public string $skillSearch = '';
    /** @var array<int, string> */
    public array $expandedCategories = [];

    // Step 4 — Médias
    public $avatar = null;
    public string $linkedin_url = '';
    public string $portfolio_url = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user) {
            $this->first_name    = (string) ($user->first_name ?? '');
            $this->last_name     = (string) ($user->last_name ?? '');
            $this->email_contact = (string) ($user->email ?? '');
        }
    }

    private function stepRules(): array
    {
        return match ($this->step) {
            1 => [
                'first_name' => ['required', 'string', 'max:100'],
                'last_name'  => ['required', 'string', 'max:100'],
                'job_title'  => ['required', 'string', 'max:255'],
                'sector_id'  => ['required', 'exists:sectors,id'],
            ],
            2 => [
                'country_id'    => ['required', 'exists:countries,id'],
                'phone'              => ['nullable', 'string', 'max:30'],
                'email_contact'      => ['nullable', 'email', 'max:255'],
                'show_phone'         => ['boolean'],
                'show_email_contact' => ['boolean'],
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
            'first_name'           => ['required', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'bio'                  => ['nullable', 'string', 'max:500'],
            'avatar'               => ['nullable', 'image', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'country_id'           => ['required', 'exists:countries,id'],
            'job_title'            => ['required', 'string', 'max:255'],
            'sector_id'            => ['required', 'exists:sectors,id'],
            'phone'                => ['nullable', 'string', 'max:30'],
            'email_contact'        => ['nullable', 'email', 'max:255'],
            'show_phone'           => ['boolean'],
            'show_email_contact'   => ['boolean'],
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

    public function updatedSectorId($value): void
    {
        // Auto-expand the skill category that matches the selected sector
        $sector = $value ? Sector::find($value) : null;
        $category = $sector ? SectorSkillCategoryMap::for($sector->name) : null;

        if ($category && ! in_array($category, $this->expandedCategories, true)) {
            $this->expandedCategories[] = $category;
        }
    }

    /**
     * Skills grouped by category, filtered by $skillSearch.
     * Returns a Collection of [category => Collection<Skill>].
     */
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
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . Str::lower($search) . '%']);
        }

        return $query->get()->groupBy('category');
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
            $avatarUrl = $avatarGenerator->generate(trim($this->first_name . ' ' . $this->last_name));
        }

        $country = Country::find($this->country_id);

        $profile = Profile::create([
            'user_id'              => Auth::id(),
            'first_name'           => $this->first_name,
            'last_name'            => $this->last_name,
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
            'show_phone'           => $this->phone ? $this->show_phone : false,
            'show_email_contact'   => $this->email_contact ? $this->show_email_contact : false,
            'linkedin_url'         => $this->linkedin_url ?: null,
            'portfolio_url'        => $this->portfolio_url ?: null,
        ]);

        $profile->skills()->sync($this->selectedSkills);

        $this->redirect(route('profile.show', $profile), navigate: true);
    }

    public function render()
    {
        return view('livewire.profile.create-profile', [
            'sectors'        => Sector::orderBy('name')->get(),
            'selectedSkillModels' => Skill::whereIn('id', $this->selectedSkills)
                ->orderBy('name')->get(),
            'countries'      => Country::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}
