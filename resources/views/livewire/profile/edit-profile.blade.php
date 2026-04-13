@section('title', 'Modifier mon profil')
@section('description', 'Mettez à jour votre profil Bassila Émergence.')
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

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

            <!-- First name + last name -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input
                        wire:model="first_name"
                        id="first_name"
                        type="text"
                        autocomplete="given-name"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('first_name') border-red-400 @enderror"
                    >
                    @error('first_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input
                        wire:model="last_name"
                        id="last_name"
                        type="text"
                        autocomplete="family-name"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('last_name') border-red-400 @enderror"
                    >
                    @error('last_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
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

            <!-- Skills — searchable, grouped by category -->
            @php
                $skillGroups    = $this->skillGroups;
                $searchActive   = trim($skillSearch) !== '';
                $openCategories = $searchActive
                    ? $skillGroups->keys()->all()
                    : $expandedCategories;
            @endphp

            <div>
                <div class="flex items-baseline justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Compétences</label>
                    @if (count($selectedSkills) > 0)
                        <span class="text-xs text-[#0066CC] font-semibold">{{ count($selectedSkills) }} sélectionnée{{ count($selectedSkills) > 1 ? 's' : '' }}</span>
                    @endif
                </div>

                {{-- Search --}}
                <div class="relative mb-2">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.250ms="skillSearch"
                           placeholder="Rechercher une compétence…"
                           class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]">
                    @if ($searchActive)
                        <button type="button" wire:click="$set('skillSearch', '')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Selected pills --}}
                @if (count($selectedSkills) > 0)
                    <div class="bg-blue-50/50 border border-blue-100 p-3 mb-2 rounded-lg">
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($selectedSkillModels as $skill)
                                <button type="button"
                                        wire:click="toggleSkill({{ $skill->id }})"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#0066CC] text-white pl-2.5 pr-1.5 py-1 hover:bg-blue-800 transition rounded">
                                    {{ $skill->name }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Category accordion --}}
                <div class="border border-gray-200 divide-y divide-gray-100 rounded-lg overflow-hidden">
                    @forelse ($skillGroups as $category => $categorySkills)
                        @php
                            $isOpen = in_array($category, $openCategories, true);
                            $selectedInCat = $categorySkills->filter(fn ($s) => in_array($s->id, $selectedSkills))->count();
                        @endphp
                        <div>
                            <button type="button"
                                    wire:click="toggleCategory({{ json_encode($category) }})"
                                    @if ($searchActive) disabled @endif
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition @if ($searchActive) cursor-default @endif">
                                <span class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-[#111827]">{{ $category }}</span>
                                    <span class="text-xs text-gray-400">({{ $categorySkills->count() }})</span>
                                    @if ($selectedInCat > 0)
                                        <span class="text-xs font-semibold text-[#0066CC]">· {{ $selectedInCat }} sélectionnée{{ $selectedInCat > 1 ? 's' : '' }}</span>
                                    @endif
                                </span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform {{ $isOpen ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            @if ($isOpen)
                                <div class="px-4 pb-4 pt-1 bg-gray-50/30">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($categorySkills as $skill)
                                            <button type="button"
                                                    wire:click="toggleSkill({{ $skill->id }})"
                                                    class="text-xs font-semibold px-3 py-1.5 border rounded transition {{ in_array($skill->id, $selectedSkills) ? 'bg-[#0066CC] text-white border-[#0066CC]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#0066CC] hover:text-[#0066CC]' }}">
                                                {{ $skill->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-sm text-gray-400">
                            Aucune compétence ne correspond à « {{ $skillSearch }} ».
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Contact info -->
<div class="space-y-4">
    <p class="text-sm font-semibold text-gray-700">Coordonnées de contact <span class="text-xs font-normal text-gray-400">(optionnel)</span></p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="email_contact" class="block text-sm font-medium text-gray-700 mb-1">Email de contact</label>
            <input
                wire:model="email_contact"
                id="email_contact"
                type="email"
                placeholder="contact@exemple.com"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('email_contact') border-red-400 @enderror"
            >
            @error('email_contact') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                <input type="checkbox" wire:model="show_email_contact" class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC]">
                <span class="text-xs text-gray-500">Afficher sur mon profil public</span>
            </label>
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone / WhatsApp</label>
            <input
                wire:model="phone"
                id="phone"
                type="tel"
                placeholder="+229 01 00 00 00"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('phone') border-red-400 @enderror"
            >
            @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <label class="flex items-center gap-2 mt-2 cursor-pointer select-none">
                <input type="checkbox" wire:model="show_phone" class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC]">
                <span class="text-xs text-gray-500">Afficher sur mon profil public</span>
            </label>
        </div>
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
