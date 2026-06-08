@section('title', 'Créer mon profil')
@section('description', 'Renseignez votre profil pour rejoindre l\'annuaire Bassila Émergence.')
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

<div>

{{-- Page header --}}
<div class="bg-[#0A1628] relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none"
         style="background-image: linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                                  linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
                background-size: 48px 48px;"></div>
    <div class="absolute bottom-0 left-0 right-0 h-px bg-[#DC143C] opacity-60"></div>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Rejoindre la communauté</p>
        <h1 class="text-2xl font-bold text-white">Créer mon profil</h1>
        <p class="text-sm text-white/40 mt-1">Votre profil sera visible par tous les membres de la communauté.</p>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Step progress --}}
    <div class="mb-10">
        <div class="flex items-center gap-0">
            @php
                $steps = [
                    1 => 'Identité',
                    2 => 'Localisation',
                    3 => 'À propos',
                    4 => 'Médias',
                ];
            @endphp
            @foreach ($steps as $n => $label)
                <div class="flex-1 flex flex-col items-center relative">
                    {{-- connector line before (not on first) --}}
                    @if ($n > 1)
                        <div class="absolute top-4 right-1/2 w-full h-px {{ $step >= $n ? 'bg-[#0066CC]' : 'bg-gray-200' }} transition-colors duration-300"></div>
                    @endif
                    <div class="relative z-10 w-8 h-8 flex items-center justify-center text-xs font-bold transition-all duration-300
                        {{ $step > $n ? 'bg-[#0066CC] text-white' : ($step === $n ? 'bg-[#0A1628] text-white border-2 border-[#0066CC]' : 'bg-white text-gray-400 border-2 border-gray-200') }}">
                        @if ($step > $n)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $n }}
                        @endif
                    </div>
                    <span class="mt-2 text-xs font-semibold {{ $step === $n ? 'text-[#0066CC]' : 'text-gray-400' }} transition-colors duration-300 hidden sm:block">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white border border-gray-200 relative overflow-hidden">
        <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#0066CC]"></div>

        {{-- ─── STEP 1 — Identité ─── --}}
        @if ($step === 1)
            <div class="p-8 pl-10">
                <div class="mb-7">
                    <h2 class="text-lg font-bold text-[#111827]">Qui êtes-vous ?</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Décrivez votre identité professionnelle.</p>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                                Prénom <span class="text-[#DC143C]">*</span>
                            </label>
                            <input wire:model="first_name"
                                   id="first_name" type="text" autocomplete="given-name"
                                   placeholder="Votre prénom"
                                   class="w-full border px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('first_name') border-red-400 @else border-gray-200 @enderror">
                            @error('first_name') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                                Nom <span class="text-[#DC143C]">*</span>
                            </label>
                            <input wire:model="last_name"
                                   id="last_name" type="text" autocomplete="family-name"
                                   placeholder="Votre nom"
                                   class="w-full border px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('last_name') border-red-400 @else border-gray-200 @enderror">
                            @error('last_name') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <p class="block text-xs font-semibold text-gray-500 mb-2.5 uppercase tracking-wider">Sexe</p>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="gender" type="radio" value="M"
                                       class="text-[#0066CC] focus:ring-[#0066CC]">
                                <span class="text-sm text-gray-700">Homme</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model="gender" type="radio" value="F"
                                       class="text-[#0066CC] focus:ring-[#0066CC]">
                                <span class="text-sm text-gray-700">Femme</span>
                            </label>
                        </div>
                        @error('gender') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="job_title" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                                Poste <span class="text-[#DC143C]">*</span>
                            </label>
                            <input wire:model="job_title"
                                   id="job_title" type="text"
                                   placeholder="Ex : Développeur, Médecin…"
                                   class="w-full border px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('job_title') border-red-400 @else border-gray-200 @enderror">
                            @error('job_title') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="company" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                                Entreprise / Organisation
                            </label>
                            <input wire:model="company"
                                   id="company" type="text"
                                   placeholder="Nom de l'employeur"
                                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition">
                        </div>
                    </div>

                    <div>
                        <label for="sector_id" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                            Secteur d'activité <span class="text-[#DC143C]">*</span>
                        </label>
                        <select wire:model="sector_id"
                                id="sector_id"
                                class="w-full border px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white @error('sector_id') border-red-400 @else border-gray-200 @enderror">
                            <option value="">Choisir un secteur…</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}">{{ $sector->name }}</option>
                            @endforeach
                        </select>
                        @error('sector_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── STEP 2 — Localisation & Contact ─── --}}
        @if ($step === 2)
            <div class="p-8 pl-10">
                <div class="mb-7">
                    <h2 class="text-lg font-bold text-[#111827]">Localisation</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Où vivez-vous et votre lien avec Bassila ?</p>
                </div>

                <div class="space-y-5">
                    {{-- Pays selector --}}
                    <div>
                        <label for="country_id" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                            Pays de résidence <span class="text-[#DC143C]">*</span>
                        </label>
                        <select wire:model="country_id"
                                id="country_id"
                                class="w-full border px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white @error('country_id') border-red-400 @else border-gray-200 @enderror">
                            <option value="">Choisir un pays…</option>
                            @php $lastSort = null; @endphp
                            @foreach ($countries as $c)
                                @if ($lastSort !== null && $lastSort !== $c->sort_order)
                                    <option disabled>──────────────</option>
                                @endif
                                <option value="{{ $c->id }}">{{ $c->flag }} {{ $c->name }}</option>
                                @php $lastSort = $c->sort_order; @endphp
                            @endforeach
                        </select>
                        @error('country_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Ville --}}
                    <div>
                        <label for="city" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                            Ville
                        </label>
                        <input wire:model="city"
                               id="city" type="text"
                               placeholder="Ex : Cotonou, Paris…"
                               class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition">
                    </div>

                    <div>
                        <label for="village_id" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                            Village d'origine à Bassila
                        </label>
                        <select wire:model="village_id"
                                id="village_id"
                                class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white @error('village_id') border-red-400 @enderror">
                            <option value="">Choisir un village…</option>
                            @php $lastArr = null; @endphp
                            @foreach ($villages as $v)
                                @if ($lastArr !== $v->arrondissement)
                                    @if ($lastArr !== null)</optgroup>@endif
                                    <optgroup label="{{ $v->arrondissement ?? 'Autres' }}">
                                @endif
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @php $lastArr = $v->arrondissement; @endphp
                            @endforeach
                            @if ($lastArr !== null)</optgroup>@endif
                        </select>
                        @error('village_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Niveau d'étude --}}
                    <div class="pt-2 border-t border-gray-100">
                        <label for="education_level" class="block text-xs text-gray-400 mb-1.5">Niveau d'étude <span class="text-gray-400">(optionnel)</span></label>
                        <select wire:model="education_level"
                                id="education_level"
                                class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white">
                            <option value="">Sélectionnez votre niveau…</option>
                            @foreach (\App\Models\Profile::EDUCATION_LEVELS as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                        @error('education_level') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Formation --}}
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-4 uppercase tracking-wider">Formation à Bassila <span class="text-gray-400 normal-case font-normal">(optionnel)</span></p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="education_start_year" class="block text-xs text-gray-400 mb-1.5">Année de début</label>
                                <input wire:model="education_start_year"
                                       id="education_start_year"
                                       type="number" min="1950" max="{{ date('Y') }}"
                                       placeholder="Ex : 2000"
                                       class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition">
                            </div>
                            <div>
                                <label for="education_end_year" class="block text-xs text-gray-400 mb-1.5">Année de fin</label>
                                <input wire:model="education_end_year"
                                       id="education_end_year"
                                       type="number" min="1950" max="{{ date('Y') + 10 }}"
                                       placeholder="Ex : 2005"
                                       class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition">
                            </div>
                        </div>
                    </div>

                    {{-- Contact public : bloc séparé (hors du flux localisation / formation) --}}
                    <div class="rounded-xl border border-[#0066CC]/25 bg-[#0066CC]/[0.04] p-6 space-y-5">
                        <div>
                            <h3 class="text-sm font-bold text-[#0A1628]">Contact sur votre fiche publique</h3>
                            <p class="text-xs text-gray-500 mt-1">Optionnel. Remplissez puis cochez « Visible » pour afficher sur votre profil.</p>
                        </div>

                        <div class="space-y-4">
                            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end sm:gap-x-4">
                                <div class="min-w-0">
                                    <label for="email_contact" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Email</label>
                                    <input wire:model.live.debounce.300ms="email_contact"
                                           id="email_contact" type="email"
                                           placeholder="contact@exemple.com"
                                           class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white @error('email_contact') border-red-400 @enderror">
                                    @error('email_contact') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <label @class([
                                    'flex items-center gap-2 pb-1 select-none sm:min-w-[9rem]',
                                    'cursor-pointer' => filled(trim($email_contact)),
                                    'cursor-not-allowed opacity-50' => ! filled(trim($email_contact)),
                                ])>
                                    <input type="checkbox" wire:model.live="show_email_contact" @disabled(! filled(trim($email_contact)))
                                           class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC] disabled:opacity-40 shrink-0">
                                    <span class="text-xs font-semibold text-gray-600">Visible</span>
                                </label>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end sm:gap-x-4">
                                <div class="min-w-0">
                                    <label for="phone" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Téléphone</label>
                                    <input wire:model.live.debounce.300ms="phone"
                                           id="phone" type="tel"
                                           placeholder="+229 01 00 00 00"
                                           class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white @error('phone') border-red-400 @enderror">
                                    @error('phone') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <label @class([
                                    'flex items-center gap-2 pb-1 select-none sm:min-w-[9rem]',
                                    'cursor-pointer' => filled(trim($phone)),
                                    'cursor-not-allowed opacity-50' => ! filled(trim($phone)),
                                ])>
                                    <input type="checkbox" wire:model.live="show_phone" @disabled(! filled(trim($phone)))
                                           class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC] disabled:opacity-40 shrink-0">
                                    <span class="text-xs font-semibold text-gray-600">Visible</span>
                                </label>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end sm:gap-x-4">
                                <div class="min-w-0">
                                    <label for="whatsapp" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">WhatsApp</label>
                                    <input wire:model.live.debounce.300ms="whatsapp"
                                           id="whatsapp" type="tel"
                                           placeholder="+229 01 00 00 00"
                                           class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition bg-white @error('whatsapp') border-red-400 @enderror">
                                    @error('whatsapp') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <label @class([
                                    'flex items-center gap-2 pb-1 select-none sm:min-w-[9rem]',
                                    'cursor-pointer' => filled(trim($whatsapp)),
                                    'cursor-not-allowed opacity-50' => ! filled(trim($whatsapp)),
                                ])>
                                    <input type="checkbox" wire:model.live="show_whatsapp" @disabled(! filled(trim($whatsapp)))
                                           class="w-4 h-4 rounded text-[#0066CC] focus:ring-[#0066CC] disabled:opacity-40 shrink-0">
                                    <span class="text-xs font-semibold text-gray-600">Visible</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ─── STEP 3 — À propos ─── --}}
        @if ($step === 3)
            <div class="p-8 pl-10">
                <div class="mb-7">
                    <h2 class="text-lg font-bold text-[#111827]">Parlez-nous de vous</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Une courte présentation et vos domaines d'expertise.</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="bio" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                            Biographie <span class="text-gray-400 normal-case font-normal">(optionnel)</span>
                        </label>
                        <textarea wire:model="bio"
                                  id="bio" rows="4"
                                  placeholder="Présentez-vous en quelques mots : votre parcours, vos passions, ce qui vous rend unique…"
                                  maxlength="500"
                                  class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition resize-none @error('bio') border-red-400 @enderror"></textarea>
                        <div class="flex items-center justify-between mt-1.5">
                            @error('bio')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @else
                                <span></span>
                            @enderror
                            <span class="text-xs text-gray-400 ml-auto">{{ strlen($bio) }}/500</span>
                        </div>
                    </div>

                    {{-- ── Skill picker : search + selected pills + category accordion ── --}}
                    @php
                        $skillGroups     = $this->skillGroups;
                        $searchActive    = trim($skillSearch) !== '';
                        $openCategories  = $searchActive
                            ? $skillGroups->keys()->all()
                            : $expandedCategories;
                    @endphp

                    <div>
                        <div class="flex items-baseline justify-between mb-3">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Compétences <span class="text-gray-400 normal-case font-normal">(optionnel)</span>
                            </label>
                            @if (count($selectedSkills) > 0)
                                <span class="text-xs text-[#0066CC] font-semibold">{{ count($selectedSkills) }} sélectionnée{{ count($selectedSkills) > 1 ? 's' : '' }}</span>
                            @endif
                        </div>

                        {{-- Search --}}
                        <div class="relative mb-3">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" />
                            </svg>
                            <input type="text"
                                   wire:model.live.debounce.250ms="skillSearch"
                                   placeholder="Rechercher une compétence…"
                                   class="w-full border border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:border-[#0066CC] transition">
                            @if ($searchActive)
                                <button type="button" wire:click="$set('skillSearch', '')"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Selected skills pills --}}
                        @if (count($selectedSkills) > 0)
                            <div class="bg-blue-50/50 border border-blue-100 p-3 mb-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($selectedSkillModels as $skill)
                                        <button type="button"
                                                wire:click="toggleSkill({{ $skill->id }})"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold bg-[#0066CC] text-white pl-2.5 pr-1.5 py-1 hover:bg-blue-800 transition">
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
                        <div class="border border-gray-200 divide-y divide-gray-100">
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
                                        <div class="px-4 pb-4 pt-1">
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach ($categorySkills as $skill)
                                                    <button type="button"
                                                            wire:click="toggleSkill({{ $skill->id }})"
                                                            class="text-xs font-semibold px-3 py-1.5 border transition {{ in_array($skill->id, $selectedSkills) ? 'bg-[#0066CC] text-white border-[#0066CC]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#0066CC] hover:text-[#0066CC]' }}">
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
                </div>
            </div>
        @endif

        {{-- ─── STEP 4 — Médias ─── --}}
        @if ($step === 4)
            <div class="p-8 pl-10">
                <div class="mb-7">
                    <h2 class="text-lg font-bold text-[#111827]">Photo & liens</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Tout est optionnel — vous pouvez compléter plus tard.</p>
                </div>

                <div class="space-y-6">
                    {{-- Avatar upload zone --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">Photo de profil</label>
                        <div class="flex items-center gap-6">
                            <div class="relative shrink-0 cursor-pointer group" onclick="document.getElementById('avatar-input').click()">
                                @if ($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}"
                                         class="w-20 h-20 object-cover border-2 border-[#0066CC]"
                                         alt="Aperçu">
                                @else
                                    <div class="w-20 h-20 border-2 border-dashed border-gray-300 group-hover:border-[#0066CC] flex flex-col items-center justify-center transition bg-gray-50">
                                        <svg class="w-6 h-6 text-gray-300 group-hover:text-[#0066CC] transition mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="text-xs text-gray-300 group-hover:text-[#0066CC] transition font-semibold">Photo</span>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">Changer</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Cliquez sur la zone pour choisir une image.</p>
                                <p class="text-xs text-gray-400">JPG ou PNG · min 200×200px · max 2 Mo</p>
                                <p class="text-xs text-gray-400 mt-0.5">Un avatar initiales sera généré automatiquement si vous ne téléversez rien.</p>
                            </div>
                        </div>
                        <input id="avatar-input"
                               wire:model="avatar"
                               type="file" accept="image/*"
                               class="hidden">
                        @error('avatar') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="avatar" class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Chargement de l'image…
                        </div>
                    </div>

                    {{-- Social links --}}
                    <div class="space-y-4">
                        <div>
                            <label for="linkedin_url" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                                LinkedIn
                            </label>
                            <div class="flex items-stretch">
                                <span class="flex items-center px-3 border border-r-0 border-gray-200 bg-gray-50 text-xs text-gray-400 font-mono shrink-0">in/</span>
                                <input wire:model="linkedin_url"
                                       id="linkedin_url" type="url"
                                       placeholder="https://linkedin.com/in/votre-profil"
                                       class="flex-1 border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('linkedin_url') border-red-400 @enderror">
                            </div>
                            @error('linkedin_url') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="portfolio_url" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                                Portfolio / Site web
                            </label>
                            <div class="flex items-stretch">
                                <span class="flex items-center px-3 border border-r-0 border-gray-200 bg-gray-50 text-xs text-gray-400 font-mono shrink-0">http</span>
                                <input wire:model="portfolio_url"
                                       id="portfolio_url" type="url"
                                       placeholder="https://monsite.com"
                                       class="flex-1 border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('portfolio_url') border-red-400 @enderror">
                            </div>
                            @error('portfolio_url') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Summary recap --}}
                    <div class="border border-gray-100 bg-gray-50 p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Récapitulatif</p>
                        <div class="space-y-1 text-sm text-gray-600">
                            <p><span class="font-semibold text-[#111827]">{{ trim($first_name . ' ' . $last_name) }}</span>{{ $job_title ? ' · ' . $job_title : '' }}{{ $company ? ' @ ' . $company : '' }}</p>
                            @if ($country_id)
                                @php $selectedCountry = $countries->firstWhere('id', $country_id); @endphp
                                <p class="text-xs text-gray-400">{{ $selectedCountry?->flag }} {{ $selectedCountry?->name }}{{ $city ? ', ' . $city : '' }}</p>
                            @endif
                            @if ($phone || $email_contact)
                                <p class="text-xs text-gray-400">{{ $email_contact }}{{ $email_contact && $phone ? ' · ' : '' }}{{ $phone }}</p>
                            @endif
                            @if (count($selectedSkills) > 0)
                                <p class="text-xs text-[#0066CC]">{{ count($selectedSkills) }} compétence(s)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Navigation buttons --}}
        <div class="px-8 pl-10 pb-8 flex items-center justify-between gap-3 mt-2">
            @if ($step > 1)
                <button type="button"
                        wire:click="prevStep"
                        class="border border-gray-200 hover:border-gray-400 text-gray-600 text-sm font-semibold px-5 py-3 transition">
                    ← Retour
                </button>
            @else
                <span></span>
            @endif

            @if ($step < 4)
                <button type="button"
                        wire:click="nextStep"
                        class="bg-[#0066CC] hover:bg-blue-800 text-white text-sm font-semibold px-6 py-3 transition"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75">
                    <span wire:loading.remove wire:target="nextStep">Continuer →</span>
                    <span wire:loading wire:target="nextStep">Vérification…</span>
                </button>
            @else
                <button type="button"
                        wire:click="save"
                        class="bg-[#0066CC] hover:bg-blue-800 text-white text-sm font-semibold px-6 py-3 transition"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75">
                    <span wire:loading.remove wire:target="save">Créer mon profil</span>
                    <span wire:loading wire:target="save">Création en cours…</span>
                </button>
            @endif
        </div>
    </div>

</div>
</div>
