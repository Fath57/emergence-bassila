@extends('layouts.modern')

@section('title', 'Modifier l\'opportunité - Emergence Bassila')

@section('content')
<div style="background: #2ecc71; padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">✏️ Modifier l'opportunité</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 700px; margin: 0 auto;">
            Mettez à jour les informations de votre offre
        </p>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div class="card">
            @if(session('error'))
                <div class="alert alert-danger">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('opportunities.update', $opportunity->id) }}">
                @csrf
                @method('PUT')

                <!-- Type & Catégorie -->
                <div class="card-section">
                    <h2 class="card-title">Type d'opportunité</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type *</label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="Emploi" {{ old('type', $opportunity->type) == 'Emploi' ? 'selected' : '' }}>💼 Emploi</option>
                                <option value="Stage" {{ old('type', $opportunity->type) == 'Stage' ? 'selected' : '' }}>🎓 Stage</option>
                                <option value="Bénévolat" {{ old('type', $opportunity->type) == 'Bénévolat' ? 'selected' : '' }}>❤️ Bénévolat</option>
                                <option value="Collaboration" {{ old('type', $opportunity->type) == 'Collaboration' ? 'selected' : '' }}>🤝 Collaboration</option>
                                <option value="Autre" {{ old('type', $opportunity->type) == 'Autre' ? 'selected' : '' }}>📌 Autre</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id">Catégorie</label>
                            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">-- Aucune --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $opportunity->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'moderator')
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $opportunity->is_featured) ? 'checked' : '' }} style="width: 20px; height: 20px;">
                                <span>⭐ Mettre à la une</span>
                            </label>
                        </div>
                    @endif
                </div>

                <!-- Informations principales -->
                <div class="card-section">
                    <h2 class="card-title">Informations principales</h2>

                    <div class="form-group">
                        <label for="title">Titre du poste *</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $opportunity->title) }}" required maxlength="255" placeholder="Ex: Développeur Web Full Stack">
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description complète *</label>
                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="8" required placeholder="Décrivez le poste, les missions, l'environnement de travail...">{{ old('description', $opportunity->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="requirements">Prérequis et compétences requises</label>
                        <textarea name="requirements" id="requirements" class="form-control @error('requirements') is-invalid @enderror"
                                  rows="6" placeholder="Formation, diplômes, compétences techniques, soft skills...">{{ old('requirements', $opportunity->requirements) }}</textarea>
                        @error('requirements')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Entreprise -->
                <div class="card-section">
                    <h2 class="card-title">🏢 Informations sur l'entreprise</h2>

                    <div class="form-group">
                        <label for="company_name">Nom de l'entreprise/organisation *</label>
                        <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror"
                               value="{{ old('company_name', $opportunity->company_name) }}" required maxlength="255" placeholder="Ex: ABC Technologies">
                        @error('company_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_website">Site web de l'entreprise</label>
                        <input type="url" name="company_website" id="company_website" class="form-control @error('company_website') is-invalid @enderror"
                               value="{{ old('company_website', $opportunity->company_website) }}" placeholder="https://example.com">
                        @error('company_website')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Détails du contrat -->
                <div class="card-section">
                    <h2 class="card-title">📋 Détails du contrat</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contract_type">Type de contrat</label>
                            <select name="contract_type" id="contract_type" class="form-control @error('contract_type') is-invalid @enderror">
                                <option value="">-- Sélectionner --</option>
                                <option value="CDI" {{ old('contract_type', $opportunity->contract_type) == 'CDI' ? 'selected' : '' }}>CDI</option>
                                <option value="CDD" {{ old('contract_type', $opportunity->contract_type) == 'CDD' ? 'selected' : '' }}>CDD</option>
                                <option value="Freelance" {{ old('contract_type', $opportunity->contract_type) == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                <option value="Stage" {{ old('contract_type', $opportunity->contract_type) == 'Stage' ? 'selected' : '' }}>Stage</option>
                                <option value="Bénévolat" {{ old('contract_type', $opportunity->contract_type) == 'Bénévolat' ? 'selected' : '' }}>Bénévolat</option>
                                <option value="Autre" {{ old('contract_type', $opportunity->contract_type) == 'Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('contract_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="location">Localisation *</label>
                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location', $opportunity->location) }}" required maxlength="255" placeholder="Ex: Cotonou, Bénin">
                            @error('location')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="remote_possible" value="1" {{ old('remote_possible', $opportunity->remote_possible) ? 'checked' : '' }} style="width: 20px; height: 20px;">
                            <span>🌍 Télétravail possible</span>
                        </label>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="salary_range">Fourchette salariale</label>
                            <input type="text" name="salary_range" id="salary_range" class="form-control @error('salary_range') is-invalid @enderror"
                                   value="{{ old('salary_range', $opportunity->salary_range) }}" maxlength="255" placeholder="Ex: 500 000 - 800 000 FCFA/mois">
                            @error('salary_range')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="experience_required">Expérience requise</label>
                            <input type="text" name="experience_required" id="experience_required" class="form-control @error('experience_required') is-invalid @enderror"
                                   value="{{ old('experience_required', $opportunity->experience_required) }}" maxlength="255" placeholder="Ex: 2-5 ans d'expérience">
                            @error('experience_required')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Date de début souhaitée</label>
                            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', $opportunity->start_date) }}">
                            @error('start_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deadline">Date limite de candidature</label>
                            <input type="date" name="deadline" id="deadline" class="form-control @error('deadline') is-invalid @enderror"
                                   value="{{ old('deadline', $opportunity->deadline) }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            @error('deadline')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact et candidature -->
                <div class="card-section">
                    <h2 class="card-title">📧 Contact et candidature</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_email">Email de contact *</label>
                            <input type="email" name="contact_email" id="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                                   value="{{ old('contact_email', $opportunity->contact_email) }}" required placeholder="contact@example.com">
                            @error('contact_email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">Téléphone de contact</label>
                            <input type="tel" name="contact_phone" id="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                                   value="{{ old('contact_phone', $opportunity->contact_phone) }}" maxlength="50" placeholder="+229 XX XX XX XX">
                            @error('contact_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="application_url">Lien de candidature (optionnel)</label>
                        <input type="url" name="application_url" id="application_url" class="form-control @error('application_url') is-invalid @enderror"
                               value="{{ old('application_url', $opportunity->application_url) }}" placeholder="https://formulaire-candidature.com">
                        @error('application_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small style="color: #7f8c8d;">Si vous avez un formulaire de candidature en ligne, vous pouvez ajouter le lien ici</small>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 2px solid #ecf0f1;">
                    <a href="{{ route('opportunities.show', $opportunity->id) }}" class="btn btn-secondary">
                        ← Annuler
                    </a>
                    <button type="submit" class="btn btn-primary btn-icon">
                        ✅ Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="card" style="background: #ecf0f1;">
            <h3 style="font-size: 18px; margin-bottom: 15px; color: #2c3e50;">💡 Conseils de rédaction</h3>
            <ul style="color: #555; line-height: 1.8; margin: 0; padding-left: 20px;">
                <li>Rédigez un titre clair et précis</li>
                <li>Décrivez les missions et responsabilités du poste</li>
                <li>Listez les compétences et qualifications requises</li>
                <li>Indiquez la fourchette salariale si possible</li>
                <li>Précisez le type de contrat et la localisation</li>
                <li>Ajoutez une date limite de candidature pour créer un sentiment d'urgence</li>
                @if(auth()->user()->role !== 'admin' && auth()->user()->role !== 'moderator')
                    <li style="font-weight: 600; color: #e74c3c;">Votre opportunité sera en attente de validation avant publication</li>
                @else
                    <li style="font-weight: 600; color: #27ae60;">En tant qu'administrateur, votre opportunité sera publiée immédiatement</li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection
