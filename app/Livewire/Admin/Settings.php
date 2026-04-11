<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class Settings extends Component
{
    /** @var array<string, array<string, mixed>> */
    public array $values = [];

    public function mount(): void
    {
        // Livewire v3 wire:model interprets dot-notation as nested array access
        // ($values['blog']['public_creation']), so we mirror that structure here.
        // Every setting key in the system follows the `group.field` convention.
        $this->values = Setting::query()
            ->get()
            ->reduce(function (array $acc, Setting $s): array {
                [$group, $field] = explode('.', $s->key, 2);
                $acc[$group][$field] = $s->casted_value;
                return $acc;
            }, []);
    }

    public function save(): void
    {
        foreach ($this->values as $group => $fields) {
            foreach ($fields as $field => $value) {
                $key = "{$group}.{$field}";
                $setting = Setting::where('key', $key)->first();
                if (! $setting) {
                    continue;
                }

                $serialized = match ($setting->type) {
                    'bool'  => $value ? '1' : '0',
                    default => (string) $value,
                };

                if ($setting->value !== $serialized) {
                    $setting->update([
                        'value'      => $serialized,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }
        }

        session()->flash('success', 'Paramètres enregistrés.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $groups = Setting::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        // Order by id (not created_at) so events sharing a timestamp stay
        // deterministically ordered; the history shows newest events first.
        $recentChanges = Activity::query()
            ->inLog('settings')
            ->with(['causer', 'subject'])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('livewire.admin.settings', [
            'groups'        => $groups,
            'recentChanges' => $recentChanges,
        ]);
    }
}
