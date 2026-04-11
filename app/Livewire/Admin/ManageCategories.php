<?php

namespace App\Livewire\Admin;

use App\Models\BlogCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ManageCategories extends Component
{
    use AuthorizesRequests;

    /** Create form */
    public string $newName = '';

    /** Inline-edit state */
    public ?int $editingId = null;
    public string $editingName = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.access'), 403);
    }

    protected function rules(): array
    {
        return [
            'newName'     => ['required', 'string', 'max:60', 'unique:blog_categories,name'],
            'editingName' => ['required', 'string', 'max:60'],
        ];
    }

    public function create(): void
    {
        $this->authorize('admin.access');
        $this->validateOnly('newName');

        BlogCategory::create([
            'name' => trim($this->newName),
            'slug' => $this->uniqueSlug(Str::slug($this->newName)),
        ]);

        $this->newName = '';
        session()->flash('success', 'Catégorie créée.');
    }

    public function startEdit(int $id): void
    {
        $category = BlogCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->editingName = $category->name;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingName = '';
    }

    public function saveEdit(): void
    {
        $this->authorize('admin.access');

        $this->validate([
            'editingName' => [
                'required', 'string', 'max:60',
                'unique:blog_categories,name,' . $this->editingId,
            ],
        ]);

        $category = BlogCategory::findOrFail($this->editingId);
        $category->update([
            'name' => trim($this->editingName),
            'slug' => $this->uniqueSlug(Str::slug($this->editingName), $category->id),
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Catégorie renommée.');
    }

    public function delete(int $id): void
    {
        $this->authorize('admin.access');

        $category = BlogCategory::findOrFail($id);
        $name = $category->name;

        // FK already declared ON DELETE SET NULL — existing posts will
        // have their category_id nullified (they become uncategorized).
        $category->delete();

        session()->flash('success', "Catégorie « {$name} » supprimée.");
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base ?: 'categorie';
        $n = 1;
        while (BlogCategory::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = ($base ?: 'categorie') . '-' . (++$n);
        }
        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.manage-categories', [
            'categories' => BlogCategory::query()
                ->withCount('posts')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
