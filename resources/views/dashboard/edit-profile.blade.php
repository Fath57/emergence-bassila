@extends('layouts.modern')

@section('title', 'Modifier mon profil - Emergence Bassila')

@section('content')
<div style="background: linear-gradient(135deg, #e67e22, #e74c3c); padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 10px; font-weight: 700;">Modifier mon profil</h1>
        <p style="font-size: 18px; opacity: 0.95;">Mettez à jour vos informations personnelles et professionnelles</p>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">
    @if(session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <strong>Erreurs dans le formulaire:</strong>
            <ul style="margin: 10px 0 0 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dashboard.profile.update') }}">
        @csrf
        @method('PUT')

        <!-- Informations personnelles -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">👤 Informations personnelles</h2>
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="first_name" class="required">Prénom</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div class="form-group">
                    <label for="last_name" class="required">Nom</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+229 XX XX XX XX">
                </div>
                <div class="form-group">
                    <label for="birth_date">Date de naissance</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control" value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}">
                </div>
                <div class="form-group">
                    <label for="gender">Genre</label>
                    <select id="gender" name="gender" class="form-select">
                        <option value="">Sélectionner</option>
                        <option value="M" {{ old('gender', $user->gender) == 'M' ? 'selected' : '' }}>Masculin</option>
                        <option value="F" {{ old('gender', $user->gender) == 'F' ? 'selected' : '' }}>Féminin</option>
                        <option value="Autre" {{ old('gender', $user->gender) == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Origine à Bassila -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🏘️ Origine à Bassila</h2>
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="village_origin">Village d'origine</label>
                    <input type="text" id="village_origin" name="village_origin" class="form-control" value="{{ old('village_origin', $user->village_origin) }}" placeholder="Ex: Bassila Centre">
                </div>
                <div class="form-group">
                    <label for="quartier">Quartier</label>
                    <input type="text" id="quartier" name="quartier" class="form-control" value="{{ old('quartier', $user->quartier) }}" placeholder="Ex: Quartier Nord">
                </div>
            </div>
        </div>

        <!-- Localisation actuelle -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📍 Localisation actuelle</h2>
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="current_city">Ville actuelle</label>
                    <input type="text" id="current_city" name="current_city" class="form-control" value="{{ old('current_city', $user->current_city) }}" placeholder="Ex: Cotonou, Paris...">
                </div>
                <div class="form-group">
                    <label for="current_country">Pays actuel</label>
                    <input type="text" id="current_country" name="current_country" class="form-control" value="{{ old('current_country', $user->current_country) }}" placeholder="Ex: Bénin, France...">
                </div>
            </div>

            <div class="form-group">
                <label for="current_address">Adresse complète</label>
                <textarea id="current_address" name="current_address" class="form-textarea" placeholder="Adresse complète (optionnel)">{{ old('current_address', $user->current_address) }}</textarea>
            </div>
        </div>

        <!-- Informations professionnelles -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">💼 Informations professionnelles</h2>
            </div>

            <div class="form-row cols-3">
                <div class="form-group">
                    <label for="current_profession">Profession actuelle</label>
                    <input type="text" id="current_profession" name="current_profession" class="form-control" value="{{ old('current_profession', $user->current_profession) }}" placeholder="Ex: Développeur Web">
                </div>
                <div class="form-group">
                    <label for="current_company">Entreprise actuelle</label>
                    <input type="text" id="current_company" name="current_company" class="form-control" value="{{ old('current_company', $user->current_company) }}" placeholder="Ex: Google">
                </div>
                <div class="form-group">
                    <label for="professional_status">Statut professionnel</label>
                    <select id="professional_status" name="professional_status" class="form-select">
                        <option value="">Sélectionner</option>
                        <option value="Employé" {{ old('professional_status', $user->professional_status) == 'Employé' ? 'selected' : '' }}>Employé</option>
                        <option value="Entrepreneur" {{ old('professional_status', $user->professional_status) == 'Entrepreneur' ? 'selected' : '' }}>Entrepreneur</option>
                        <option value="Freelance" {{ old('professional_status', $user->professional_status) == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="Étudiant" {{ old('professional_status', $user->professional_status) == 'Étudiant' ? 'selected' : '' }}>Étudiant</option>
                        <option value="En recherche" {{ old('professional_status', $user->professional_status) == 'En recherche' ? 'selected' : '' }}>En recherche d'emploi</option>
                        <option value="Retraité" {{ old('professional_status', $user->professional_status) == 'Retraité' ? 'selected' : '' }}>Retraité</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Biographie et présentation -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📝 À propos de moi</h2>
            </div>

            <div class="form-group">
                <label for="bio">Biographie / Présentation</label>
                <textarea id="bio" name="bio" class="form-textarea" style="min-height: 150px;" placeholder="Parlez-nous de vous, votre parcours, vos passions...">{{ old('bio', $user->bio) }}</textarea>
                <small style="color: #7f8c8d; font-size: 13px;">Maximum 1000 caractères</small>
            </div>

            <div class="form-group">
                <label for="skills_summary">Résumé des compétences</label>
                <textarea id="skills_summary" name="skills_summary" class="form-textarea" placeholder="Listez vos compétences principales...">{{ old('skills_summary', $user->skills_summary) }}</textarea>
            </div>

            <div class="form-group">
                <label for="interests">Centres d'intérêt</label>
                <textarea id="interests" name="interests" class="form-textarea" placeholder="Sport, musique, lecture, voyages...">{{ old('interests', $user->interests) }}</textarea>
            </div>
        </div>

        <!-- Réseaux sociaux -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🌐 Réseaux sociaux</h2>
            </div>

            <div class="form-group">
                <label for="linkedin_url">LinkedIn</label>
                <input type="url" id="linkedin_url" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $user->linkedin_url) }}" placeholder="https://www.linkedin.com/in/votre-profil">
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="facebook_url">Facebook</label>
                    <input type="url" id="facebook_url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $user->facebook_url) }}" placeholder="https://www.facebook.com/votre-profil">
                </div>
                <div class="form-group">
                    <label for="twitter_url">Twitter</label>
                    <input type="url" id="twitter_url" name="twitter_url" class="form-control" value="{{ old('twitter_url', $user->twitter_url) }}" placeholder="https://twitter.com/votre-profil">
                </div>
            </div>
        </div>

        <!-- Préférences -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚙️ Préférences</h2>
            </div>

            <div class="form-check">
                <input type="checkbox" id="open_to_opportunities" name="open_to_opportunities" class="form-check-input" value="1" {{ old('open_to_opportunities', $user->open_to_opportunities) ? 'checked' : '' }}>
                <label for="open_to_opportunities" class="form-check-label">
                    <strong>Je suis ouvert aux opportunités professionnelles</strong>
                    <br>
                    <small style="color: #7f8c8d;">Les autres membres pourront voir que vous êtes disponible</small>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px;">
            <a href="{{ route('dashboard.profile') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection
