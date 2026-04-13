<?php

namespace App\Livewire\Admin;

use App\Models\Village;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ManageVillages extends Component
{
    use AuthorizesRequests;

    public string $newName = '';

    public string $newArrondissement = '';

    public bool $newIsActive = true;

    public int $newSortOrder = 0;

    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingArrondissement = '';

    public bool $editingIsActive = true;

    public int $editingSortOrder = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    public function create(): void
    {
        $this->authorize('admin.access');

        $this->validate([
            'newName' => ['required', 'string', 'max:150', $this->uniqueVillageNameRule('newArrondissement')],
            'newArrondissement' => ['nullable', 'string', 'max:100'],
            'newSortOrder' => ['required', 'integer', 'min:0', 'max:65535'],
            'newIsActive' => ['boolean'],
        ]);

        Village::create([
            'name' => trim($this->newName),
            'arrondissement' => $this->normalizedArrondissement('newArrondissement'),
            'is_active' => $this->newIsActive,
            'sort_order' => $this->newSortOrder,
        ]);

        $this->newName = '';
        $this->newArrondissement = '';
        $this->newIsActive = true;
        $this->newSortOrder = 0;

        session()->flash('success', 'Village créé.');
    }

    public function startEdit(int $id): void
    {
        $village = Village::findOrFail($id);
        $this->editingId = $village->id;
        $this->editingName = $village->name;
        $this->editingArrondissement = $village->arrondissement ?? '';
        $this->editingIsActive = $village->is_active;
        $this->editingSortOrder = $village->sort_order;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingName = '';
        $this->editingArrondissement = '';
        $this->editingIsActive = true;
        $this->editingSortOrder = 0;
    }

    public function saveEdit(): void
    {
        $this->authorize('admin.access');

        $this->validate([
            'editingName' => ['required', 'string', 'max:150', $this->uniqueVillageNameRule('editingArrondissement', $this->editingId)],
            'editingArrondissement' => ['nullable', 'string', 'max:100'],
            'editingSortOrder' => ['required', 'integer', 'min:0', 'max:65535'],
            'editingIsActive' => ['boolean'],
        ]);

        $village = Village::findOrFail($this->editingId);
        $village->update([
            'name' => trim($this->editingName),
            'arrondissement' => $this->normalizedArrondissement('editingArrondissement'),
            'is_active' => $this->editingIsActive,
            'sort_order' => $this->editingSortOrder,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Village mis à jour.');
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.access');

        $village = Village::findOrFail($id);
        $name = $village->name;

        $village->delete();

        session()->flash('success', "Village « {$name} » supprimé.");
    }

    public function render()
    {
        return view('livewire.admin.manage-villages', [
            'villages' => Village::query()
                ->withCount('profiles')
                ->orderBy('arrondissement')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function normalizedArrondissement(string $property): ?string
    {
        $raw = $this->{$property} ?? '';

        return trim((string) $raw) === '' ? null : trim((string) $raw);
    }

    private function uniqueVillageNameRule(string $arrondissementProperty, ?int $ignoreId = null): Unique
    {
        $rule = Rule::unique('villages', 'name')->where(function ($query) use ($arrondissementProperty) {
            $arr = $this->normalizedArrondissement($arrondissementProperty);
            if ($arr === null) {
                $query->whereNull('arrondissement');
            } else {
                $query->where('arrondissement', $arr);
            }
        });

        if ($ignoreId !== null) {
            $rule->ignore($ignoreId);
        }

        return $rule;
    }
}
