<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
            <h1 class="text-3xl font-bold text-[#111827]">Utilisateurs</h1>
            <p class="text-gray-500 mt-1">Gérez les membres de la plateforme et leurs accès.</p>
        </div>
        @can('users.invite')
            <a href="{{ route('admin.users.invite') }}"
               wire:navigate
               class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-5 py-2.5 text-sm transition">
                + Inviter un utilisateur
            </a>
        @endcan
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-gray-200">
        <button type="button"
                wire:click="$set('tab', 'active')"
                class="text-sm font-semibold px-4 py-2 transition border-b-2 -mb-px {{ $tab === 'active' ? 'border-[#0066CC] text-[#0066CC]' : 'border-transparent text-gray-500 hover:text-[#111827]' }}">
            Tous les utilisateurs ({{ $activeCount }})
        </button>
        <button type="button"
                wire:click="$set('tab', 'pending')"
                class="text-sm font-semibold px-4 py-2 transition border-b-2 -mb-px {{ $tab === 'pending' ? 'border-[#0066CC] text-[#0066CC]' : 'border-transparent text-gray-500 hover:text-[#111827]' }}">
            Invitations en attente ({{ $pendingCount }})
        </button>
    </div>

    @if ($tab === 'active')
        {{-- Filters bar --}}
        <div class="bg-white border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Rechercher par nom ou email…"
                       class="w-full border border-gray-200 pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
            </div>
            <select wire:model.live="roleFilter"
                    class="border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                <option value="all">Tous les rôles</option>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="member">Member</option>
            </select>
            <select wire:model.live="statusFilter"
                    class="border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                <option value="all">Tous les statuts</option>
                <option value="active">Actifs</option>
                <option value="inactive">Inactifs</option>
                <option value="unverified">Email non vérifié</option>
            </select>
        </div>

        {{-- Active users table --}}
        <div class="bg-white border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Membre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rôle</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Inscrit le</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $user)
                        @php
                            $role = $user->roles->first()?->name ?? '—';
                            $roleBadge = match ($role) {
                                'admin'  => 'bg-red-50 text-red-700 border-red-200',
                                'editor' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'member' => 'bg-gray-100 text-gray-600 border-gray-200',
                                default  => 'bg-gray-100 text-gray-500 border-gray-200',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-4">
                                <p class="font-semibold text-[#111827]">{{ $user->name }}</p>
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex text-xs font-semibold px-2 py-1 border {{ $roleBadge }}">{{ $role }}</span>
                            </td>
                            <td class="px-4 py-4">
                                @if (! $user->is_active)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-50 border border-red-100 px-2 py-0.5">
                                        Inactif
                                    </span>
                                @elseif (! $user->email_verified_at)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5">
                                        Non vérifié
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 px-2 py-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Actif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 hidden lg:table-cell">
                                <p class="text-xs text-gray-500">{{ $user->created_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-4 py-4 text-right">
                                @can('users.edit')
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       wire:navigate
                                       class="text-xs font-semibold text-[#0066CC] hover:underline">
                                        Éditer
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400">
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        {{-- Pending invitations table --}}
        <div class="bg-white border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Invité par</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rôle</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expire</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $invitation)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-4">
                                <p class="font-semibold text-[#111827]">{{ $invitation->email }}</p>
                                @if ($invitation->first_name || $invitation->last_name)
                                    <p class="text-xs text-gray-400">{{ trim($invitation->first_name . ' ' . $invitation->last_name) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 hidden md:table-cell">
                                <p class="text-sm text-gray-600">{{ $invitation->invitedBy?->name ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex text-xs font-semibold px-2 py-1 border bg-gray-100 text-gray-600 border-gray-200">{{ $invitation->role }}</span>
                            </td>
                            <td class="px-4 py-4">
                                @if ($invitation->isExpired())
                                    <span class="inline-flex text-xs font-semibold text-red-700 bg-red-50 border border-red-100 px-2 py-0.5">
                                        Expirée
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">{{ $invitation->expires_at->diffForHumans() }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                @can('users.invite')
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                wire:click="resend({{ $invitation->id }})"
                                                class="text-xs font-semibold text-[#0066CC] hover:underline">
                                            Relancer
                                        </button>
                                        <button type="button"
                                                wire:click="cancelInvitation({{ $invitation->id }})"
                                                wire:confirm="Annuler l'invitation pour {{ $invitation->email }} ?"
                                                class="text-xs font-semibold text-gray-500 hover:text-[#DC143C]">
                                            Annuler
                                        </button>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">
                                Aucune invitation en attente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
