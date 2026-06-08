<?php

namespace App\Livewire\Directory;

use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SearchDirectory extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $query = '';

    #[Url(history: true)]
    public ?int $sector = null;

    #[Url(history: true)]
    public string $country = '';

    #[Url(history: true)]
    public string $yearFrom = '';

    #[Url(history: true)]
    public string $yearTo = '';

    #[Url(history: true)]
    public string $educationLevel = '';

    #[Url(history: true)]
    public array $skills = [];

    #[Url(history: true)]
    public bool $verifiedOnly = false;

    // Métier / Domaine picker state (not persisted to the URL)
    public string $skillSearch = '';

    /** @var array<int, string> */
    public array $expandedCategories = [];

    public function updatingQuery(): void
    {
        $this->resetPage();
    }

    public function updatingSector(): void
    {
        $this->resetPage();
    }

    public function updatingCountry(): void
    {
        $this->resetPage();
    }

    public function updatingEducationLevel(): void
    {
        $this->resetPage();
    }

    public function updatingSkills(): void
    {
        $this->resetPage();
    }

    public function updatingVerifiedOnly(): void
    {
        $this->resetPage();
    }

    public function toggleSkill(int $id): void
    {
        if (in_array($id, array_map('intval', $this->skills), true)) {
            $this->skills = array_values(array_filter($this->skills, fn ($s) => (int) $s !== $id));
        } else {
            $this->skills[] = $id;
        }

        $this->resetPage();
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

    public function resetFilters(): void
    {
        $this->query = '';
        $this->sector = null;
        $this->country = '';
        $this->yearFrom = '';
        $this->yearTo = '';
        $this->educationLevel = '';
        $this->skills = [];
        $this->skillSearch = '';
        $this->expandedCategories = [];
        $this->verifiedOnly = false;
        $this->resetPage();
    }

    /**
     * Métiers / domaines (skills) grouped by category, filtered by $skillSearch.
     *
     * @return Collection<string, Collection<int, Skill>>
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
            $query->whereRaw('LOWER(name) LIKE ?', ['%'.Str::lower($search).'%']);
        }

        return $query->get()->groupBy('category');
    }

    public function results()
    {
        return Profile::with(['sector', 'skills'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->search($this->query ?: null)
            ->inSector($this->sector)
            ->inCountry($this->country ?: null)
            ->withEducationYears(
                $this->yearFrom ? (int) $this->yearFrom : null,
                $this->yearTo ? (int) $this->yearTo : null
            )
            ->withEducationLevel($this->educationLevel ?: null)
            ->withSkills($this->skills)
            ->when($this->verifiedOnly, fn ($q) => $q->verified())
            ->latest()
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.directory.search-directory', [
            'profiles' => $this->results(),
            'sectors' => Sector::orderBy('name')->get(),
            'educationLevels' => Profile::EDUCATION_LEVELS,
            'selectedSkillModels' => Skill::whereIn('id', $this->skills)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
