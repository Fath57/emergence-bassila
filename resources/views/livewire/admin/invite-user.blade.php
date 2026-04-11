<div class="max-w-2xl">
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">Inviter un utilisateur</h1>
        <p class="text-gray-500 mt-1">Envoyez un email d'invitation avec un rôle pré-assigné.</p>
    </div>

    <form wire:submit.prevent="send" class="bg-white border border-gray-200 p-8 space-y-5">

        <div>
            <label for="email" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Adresse email <span class="text-[#DC143C]">*</span>
            </label>
            <input wire:model="email"
                   id="email" type="email"
                   placeholder="nouvelle.personne@exemple.com"
                   class="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-[#0066CC] @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                    Prénom <span class="text-gray-400 normal-case font-normal">(optionnel)</span>
                </label>
                <input wire:model="first_name"
                       id="first_name" type="text"
                       placeholder="Prénom"
                       class="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-[#0066CC]">
            </div>
            <div>
                <label for="last_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                    Nom <span class="text-gray-400 normal-case font-normal">(optionnel)</span>
                </label>
                <input wire:model="last_name"
                       id="last_name" type="text"
                       placeholder="Nom"
                       class="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-[#0066CC]">
            </div>
        </div>

        <div>
            <label for="role" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Rôle <span class="text-[#DC143C]">*</span>
            </label>
            <select wire:model="role"
                    id="role"
                    class="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-[#0066CC]">
                <option value="member">Member — accès de base à la communauté</option>
                <option value="editor">Editor — peut publier ses propres articles sans modération</option>
                <option value="admin">Admin — accès complet au panneau d'administration</option>
            </select>
            @error('role') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="message" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Message personnel <span class="text-gray-400 normal-case font-normal">(optionnel)</span>
            </label>
            <textarea wire:model="message"
                      id="message" rows="4" maxlength="500"
                      placeholder="Un mot d'accueil personnalisé (inclus dans l'email d'invitation)"
                      class="w-full border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:border-[#0066CC] resize-none"></textarea>
            @error('message') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.users') }}"
               wire:navigate
               class="text-sm font-semibold text-gray-500 hover:text-[#111827] px-4 py-2">
                Annuler
            </a>
            <button type="submit"
                    class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-6 py-2.5 text-sm transition"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75">
                <span wire:loading.remove>Envoyer l'invitation</span>
                <span wire:loading>Envoi en cours…</span>
            </button>
        </div>
    </form>
</div>
