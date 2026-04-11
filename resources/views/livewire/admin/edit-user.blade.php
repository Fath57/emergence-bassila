<div class="max-w-3xl">
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">{{ $user->name }}</h1>
        <p class="text-gray-500 mt-1">{{ $user->email }}</p>
    </div>

    @if ($isLastActiveAdmin)
        <div class="bg-amber-50 border border-amber-200 p-4 mb-6">
            <p class="text-sm font-semibold text-amber-800">
                Cet utilisateur est le <strong>dernier administrateur actif</strong>.
                Vous ne pouvez pas le rétrograder ni le désactiver — au moins un administrateur actif doit toujours rester.
            </p>
        </div>
    @endif

    {{-- Section: Basic info --}}
    <div class="bg-white border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider mb-5">Informations</h2>
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Prénom</label>
                    <input wire:model="first_name" id="first_name" type="text"
                           class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('first_name') border-red-400 @enderror">
                    @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Nom</label>
                    <input wire:model="last_name" id="last_name" type="text"
                           class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('last_name') border-red-400 @enderror">
                    @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label for="email" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Email</label>
                <input wire:model="email" id="email" type="email"
                       class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('email') border-red-400 @enderror">
                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-5 py-2 text-sm transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    {{-- Section: Role --}}
    <div class="bg-white border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider mb-5">Rôle</h2>
        <div>
            <label for="role" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Rôle actuel</label>
            <select id="role"
                    wire:change="changeRole($event.target.value)"
                    @if ($isLastActiveAdmin) disabled @endif
                    class="w-full sm:w-72 border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] disabled:bg-gray-100 disabled:text-gray-400">
                <option value="admin"  @if ($currentRole === 'admin')  selected @endif>Admin — accès complet</option>
                <option value="editor" @if ($currentRole === 'editor') selected @endif>Editor — publication sans modération</option>
                <option value="member" @if ($currentRole === 'member') selected @endif>Member — accès standard</option>
            </select>
            @if ($isLastActiveAdmin)
                <p class="text-xs text-gray-500 mt-2">Changement de rôle désactivé — dernier administrateur actif.</p>
            @endif
        </div>
    </div>

    {{-- Section: Status --}}
    <div class="bg-white border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-bold text-[#111827] uppercase tracking-wider mb-5">Statut</h2>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-[#111827]">
                    Compte {{ $user->is_active ? 'actif' : 'désactivé' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $user->is_active
                        ? "L'utilisateur peut se connecter et apparaît dans l'annuaire."
                        : "L'utilisateur ne peut pas se connecter ; son profil public est masqué." }}
                </p>
            </div>
            @if ($user->is_active)
                <button type="button"
                        wire:click="toggleActive"
                        wire:confirm="Désactiver ce compte ?"
                        @if ($isLastActiveAdmin) disabled @endif
                        class="text-xs font-semibold px-4 py-2 border border-[#DC143C] text-[#DC143C] hover:bg-[#DC143C] hover:text-white transition disabled:border-gray-200 disabled:text-gray-400 disabled:hover:bg-transparent disabled:cursor-not-allowed">
                    Désactiver le compte
                </button>
            @else
                <button type="button"
                        wire:click="toggleActive"
                        class="text-xs font-semibold px-4 py-2 bg-green-600 text-white hover:bg-green-700 transition">
                    Réactiver le compte
                </button>
            @endif
        </div>
    </div>

    {{-- Section: History --}}
    <div class="bg-white border border-gray-200 mt-10">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-[#111827] uppercase tracking-wider">Historique</h3>
        </div>
        <ul class="divide-y divide-gray-100">
            @forelse ($history as $activity)
                <li class="px-6 py-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span>
                            <strong class="text-[#111827]">{{ $activity->causer?->name ?? 'Système' }}</strong>
                            @if ($activity->description === 'role_changed')
                                a changé le rôle : <code class="text-xs bg-gray-100 px-1.5 py-0.5">{{ $activity->properties['old'] ?? '?' }}</code>
                                → <code class="text-xs bg-gray-100 px-1.5 py-0.5">{{ $activity->properties['new'] ?? '?' }}</code>
                            @elseif ($activity->description === 'updated')
                                a mis à jour le compte
                            @elseif ($activity->description === 'created')
                                a créé le compte
                            @else
                                — {{ $activity->description }}
                            @endif
                        </span>
                        <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                </li>
            @empty
                <li class="px-6 py-6 text-center text-sm text-gray-400">Aucun événement enregistré.</li>
            @endforelse
        </ul>
    </div>
</div>
