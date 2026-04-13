<section class="mt-12 border border-red-200 p-6 bg-red-50" x-data="{ open: false }">
    <h2 class="text-lg font-bold text-red-800 mb-2">Zone dangereuse</h2>
    <p class="text-sm text-red-900 mb-4">
        La suppression de votre compte entraîne la disparition définitive de votre profil. Vos articles et commentaires seront conservés de manière anonyme pour préserver la continuité éditoriale. Cette action peut être annulée pendant 30 jours.
    </p>

    @if ($submitted)
        <p class="text-sm text-green-800 bg-green-50 border border-green-200 p-3">
            Demande enregistrée. Vérifiez votre boîte email pour confirmer la suppression.
        </p>
    @else
        <button type="button" @click="open = true"
                class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2">
            Supprimer mon compte
        </button>

        <div x-show="open" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div @click.outside="open = false"
                 class="bg-white max-w-md w-full p-6">
                <h3 class="text-base font-bold text-gray-900 mb-3">Confirmer la suppression</h3>
                <form wire:submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Mot de passe</label>
                        <input wire:model="password" type="password" autocomplete="current-password"
                               class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-red-500">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('lockout')  <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex gap-2 text-sm text-gray-700">
                        <input wire:model="understood" type="checkbox" class="mt-1">
                        <span>Je comprends que mon profil sera supprimé et que mes articles seront anonymisés.</span>
                    </label>
                    @error('understood') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="text-sm text-gray-600 hover:text-gray-900">Annuler</button>
                        <button type="submit"
                                class="bg-red-700 hover:bg-red-800 text-white text-sm font-semibold px-4 py-2">
                            Confirmer la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
