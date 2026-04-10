<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-[#333333] mb-2">Modifier mon profil</h1>
        <p class="text-sm text-gray-500 mb-6">Mettez à jour vos informations professionnelles.</p>

        <form wire:submit.prevent="save" class="space-y-5">

            <!-- Avatar -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo de profil</label>
                <div class="flex items-center gap-4 mb-2">
                    @if ($profile && $profile->avatar_url)
                        <img src="{{ $profile->avatar_url }}" class="h-16 w-16 rounded-full object-cover border border-gray-200" alt="{{ $profile->full_name }}">
                    @endif
                    @if ($avatar)
                        <div>
                            <img src="{{ $avatar->temporaryUrl() }}" class="h-16 w-16 rounded-full object-cover border-2 border-[#0066CC]" alt="Nouvelle photo">
                            <p class="text-xs text-[#0066CC] mt-1">Nouvelle photo</p>
                        </div>
                    @endif
                </div>
                <input
                    wire:model="avatar"
                    type="file"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-[#0066CC] hover:file:bg-blue-100 cursor-pointer"
                >
                @error('avatar') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Full name -->
            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                <input
                    wire:model="full_name"
                    id="full_name"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('full_name') border-red-400 @enderror"
                >
                @error('full_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Job title + Company -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="job_title" class="block text-sm font-medium text-gray-700 mb-1">Poste <span class="text-red-500">*</span></label>
                    <input
                        wire:model="job_title"
                        id="job_title"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('job_title') border-red-400 @enderror"
                    >
                    @error('job_title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700 mb-1">Entreprise / Organisation</label>
                    <input
                        wire:model="company"
                        id="company"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]"
                    >
                </div>
            </div>

            <!-- Sector -->
            <div>
                <label for="sector_id" class="block text-sm font-medium text-gray-700 mb-1">Secteur d'activité <span class="text-red-500">*</span></label>
                <select
                    wire:model="sector_id"
                    id="sector_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('sector_id') border-red-400 @enderror"
                >
                    <option value="">Choisir un secteur</option>
                    @foreach (\App\Models\Sector::orderBy('name')->get() as $sector)
                        <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                    @endforeach
                </select>
                @error('sector_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Country + City -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Pays <span class="text-red-500">*</span></label>
                    <input
                        wire:model="country"
                        id="country"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('country') border-red-400 @enderror"
                    >
                    @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                    <input
                        wire:model="city"
                        id="city"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]"
                    >
                </div>
            </div>

            <!-- Bio -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Biographie</label>
                <textarea
                    wire:model="bio"
                    id="bio"
                    rows="3"
                    maxlength="500"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] resize-none @error('bio') border-red-400 @enderror"
                ></textarea>
                <p class="mt-1 text-xs text-gray-400">{{ strlen($bio) }}/500 caractères</p>
                @error('bio') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Education years -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="education_start_year" class="block text-sm font-medium text-gray-700 mb-1">Année début études</label>
                    <input
                        wire:model="education_start_year"
                        id="education_start_year"
                        type="number"
                        min="1950"
                        max="{{ date('Y') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]"
                    >
                </div>
                <div>
                    <label for="education_end_year" class="block text-sm font-medium text-gray-700 mb-1">Année fin études</label>
                    <input
                        wire:model="education_end_year"
                        id="education_end_year"
                        type="number"
                        min="1950"
                        max="{{ date('Y') + 10 }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]"
                    >
                </div>
            </div>

            <!-- Skills -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Compétences</label>
                <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50">
                    @foreach (\App\Models\Skill::orderBy('name')->get() as $skill)
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="selectedSkills"
                                value="{{ $skill->id }}"
                                class="rounded border-gray-300 text-[#0066CC] focus:ring-[#0066CC]"
                            >
                            <span class="text-sm text-gray-700">{{ $skill->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Links -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                    <input
                        wire:model="linkedin_url"
                        id="linkedin_url"
                        type="url"
                        placeholder="https://linkedin.com/in/..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('linkedin_url') border-red-400 @enderror"
                    >
                    @error('linkedin_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="portfolio_url" class="block text-sm font-medium text-gray-700 mb-1">Portfolio / Site web</label>
                    <input
                        wire:model="portfolio_url"
                        id="portfolio_url"
                        type="url"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('portfolio_url') border-red-400 @enderror"
                    >
                    @error('portfolio_url') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="flex-1 bg-[#0066CC] hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition text-sm"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-not-allowed"
                >
                    <span wire:loading.remove>Enregistrer les modifications</span>
                    <span wire:loading>Enregistrement...</span>
                </button>
                @if ($profile)
                    <a href="{{ route('profile.show', $profile) }}" class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition" wire:navigate>
                        Annuler
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
