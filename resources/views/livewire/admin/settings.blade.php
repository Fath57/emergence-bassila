<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">Paramètres</h1>
        <p class="text-gray-500 mt-1">Configurez le comportement global du site.</p>
    </div>

    <form wire:submit.prevent="save">
        @foreach ($groups as $groupName => $groupSettings)
            <div class="bg-white border border-gray-200 p-6 mb-6">
                <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider mb-5">
                    {{ __('settings.groups.'.$groupName) }}
                </h2>
                <div class="space-y-5">
                    @foreach ($groupSettings as $setting)
                        @php
                            [$wireGroup, $wireField] = explode('.', $setting->key, 2);
                            $wireBind = "values.{$wireGroup}.{$wireField}";
                        @endphp
                        @if ($setting->type === 'bool')
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox"
                                       wire:model="{{ $wireBind }}"
                                       class="mt-0.5 w-4 h-4 text-[#0066CC] border-gray-300">
                                <div>
                                    <p class="text-sm font-semibold text-[#111827]">{{ $setting->label }}</p>
                                    @if ($setting->description)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $setting->description }}</p>
                                    @endif
                                </div>
                            </label>
                        @elseif ($setting->type === 'string')
                            <div>
                                <label class="block text-sm font-semibold text-[#111827] mb-1">{{ $setting->label }}</label>
                                @if ($setting->description)
                                    <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                                @endif
                                <input type="text"
                                       wire:model="{{ $wireBind }}"
                                       class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit"
                class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-6 py-3 text-sm transition">
            Enregistrer les modifications
        </button>
    </form>

    <div class="bg-white border border-gray-200 mt-10">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Historique des modifications</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($recentChanges as $activity)
                <li class="px-6 py-3 flex items-center justify-between text-sm">
                    <span>
                        <strong class="text-[#111827]">{{ $activity->causer?->name ?? 'Système' }}</strong>
                        a modifié
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5">{{ $activity->subject?->key }}</code>
                        @if (isset($activity->properties['old']['value'], $activity->properties['attributes']['value']))
                            ({{ $activity->properties['old']['value'] }} → {{ $activity->properties['attributes']['value'] }})
                        @endif
                    </span>
                    <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="px-6 py-6 text-center text-sm text-gray-400">Aucune modification enregistrée.</li>
            @endforelse
        </ul>
    </div>
</div>
