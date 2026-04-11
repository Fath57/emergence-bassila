<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">Rôles &amp; permissions</h1>
        <p class="text-gray-500 mt-1">Matrice des permissions attribuées à chaque rôle.</p>
    </div>

    <div class="bg-amber-50 border border-amber-200 p-4 mb-6">
        <p class="text-sm text-amber-800">
            <strong>Documentation en lecture seule.</strong>
            Les rôles et leurs permissions sont définis en code (voir
            <code class="text-xs bg-amber-100 px-1.5 py-0.5">database/seeders/RolePermissionSeeder.php</code>).
            Pour ajouter un nouveau rôle ou modifier les permissions d'un rôle existant, contactez un développeur.
        </p>
    </div>

    @php
        // French labels for each domain header
        $domainLabels = [
            'posts'      => 'Articles',
            'comments'   => 'Commentaires',
            'profiles'   => 'Profils',
            'users'      => 'Utilisateurs',
            'roles'      => 'Rôles',
            'settings'   => 'Paramètres',
            'newsletter' => 'Newsletter',
            'admin'      => 'Panneau admin',
        ];
    @endphp

    <div class="bg-white border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Permission</th>
                    @foreach ($roles as $role)
                        @php
                            $headerColor = match ($role->name) {
                                'admin'  => 'text-red-700',
                                'editor' => 'text-blue-700',
                                'member' => 'text-gray-600',
                                default  => 'text-gray-500',
                            };
                        @endphp
                        <th class="text-center px-4 py-3 text-xs font-bold uppercase tracking-wider w-24 {{ $headerColor }}">
                            {{ $role->name }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($grouped as $domain => $permissions)
                    <tr class="bg-gray-50 border-t border-b border-gray-200">
                        <td colspan="{{ $roles->count() + 1 }}" class="px-4 py-2 text-xs font-bold text-[#111827] uppercase tracking-wider">
                            {{ $domainLabels[$domain] ?? $domain }}
                        </td>
                    </tr>
                    @foreach ($permissions as $permission)
                        <tr class="border-t border-gray-100 hover:bg-gray-50/50 transition">
                            <td class="px-4 py-2.5 text-sm text-gray-600">
                                <code class="text-xs bg-gray-100 px-1.5 py-0.5">{{ $permission->name }}</code>
                            </td>
                            @foreach ($roles as $role)
                                <td class="text-center px-4 py-2.5">
                                    @if ($role->permissions->contains('name', $permission->name))
                                        <span class="inline-flex items-center justify-center w-5 h-5 bg-green-100 text-green-700">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-4">
        <strong>Note</strong> — Le membre (<code>member</code>) a techniquement la permission <code>posts.create</code>, mais la création effective est soumise au réglage <code>blog.public_creation</code> dans les paramètres.
    </p>
</div>
