<?php

namespace App\Livewire\Directory;

use App\Models\Profile;
use App\Models\Sector;
use App\Models\Skill;
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
    public array $skills = [];

    #[Url(history: true)]
    public bool $verifiedOnly = false;

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

    public function updatingVerifiedOnly(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->query       = '';
        $this->sector      = null;
        $this->country     = '';
        $this->yearFrom    = '';
        $this->yearTo      = '';
        $this->skills      = [];
        $this->verifiedOnly = false;
        $this->resetPage();
    }

    public function results()
    {
        return Profile::with(['sector', 'skills'])
            ->search($this->query ?: null)
            ->inSector($this->sector)
            ->inCountry($this->country ?: null)
            ->withEducationYears(
                $this->yearFrom ? (int) $this->yearFrom : null,
                $this->yearTo   ? (int) $this->yearTo   : null
            )
            ->withSkills($this->skills)
            ->when($this->verifiedOnly, fn ($q) => $q->verified())
            ->latest()
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.directory.search-directory', [
            'profiles'       => $this->results(),
            'sectors'        => Sector::orderBy('name')->get(),
            'availableSkills' => Skill::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
