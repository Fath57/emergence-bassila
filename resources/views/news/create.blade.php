@extends('layouts.modern')

@section('title', 'Publier une actualité - Emergence Bassila')

@section('content')
<div style="background: linear-gradient(135deg, #8e44ad, #3498db); padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">✍️ Publier une actualité</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 700px; margin: 0 auto;">
            Partagez des nouvelles, événements et annonces avec la communauté
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

            <form method="POST" action="{{ route('news.store') }}">
                @csrf

                <!-- Type & Catégorie -->
                <div class="card-section">
                    <h2 class="card-title">Type de publication</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type *</label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="Actualité" {{ old('type') == 'Actualité' ? 'selected' : '' }}>📰 Actualité</option>
                                <option value="Événement" {{ old('type') == 'Événement' ? 'selected' : '' }}>📅 Événement</option>
                                <option value="Annonce" {{ old('type') == 'Annonce' ? 'selected' : '' }}>📢 Annonce</option>
                                <option value="Culture" {{ old('type') == 'Culture' ? 'selected' : '' }}>🎭 Culture</option>
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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="width: 20px; height: 20px;">
                                <span>⭐ Mettre à la une</span>
                            </label>
                        </div>
                    @endif
                </div>

                <!-- Informations principales -->
                <div class="card-section">
                    <h2 class="card-title">Informations principales</h2>

                    <div class="form-group">
                        <label for="title">Titre *</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required maxlength="255" placeholder="Ex: Réunion annuelle de la communauté">
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Résumé court</label>
                        <textarea name="excerpt" id="excerpt" class="form-control @error('excerpt') is-invalid @enderror"
                                  rows="3" maxlength="500" placeholder="Un court résumé qui apparaîtra sur la liste des actualités">{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small style="color: #7f8c8d;">Maximum 500 caractères</small>
                    </div>

                    <div class="form-group">
                        <label for="content">Contenu complet *</label>
                        <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror"
                                  rows="12" required placeholder="Décrivez votre actualité en détail...">{{ old('content') }}</textarea>
                        @error('content')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags (séparés par des virgules)</label>
                        <input type="text" name="tags" id="tags" class="form-control @error('tags') is-invalid @enderror"
                               value="{{ old('tags') }}" placeholder="Ex: bassila, réunion, culture, tradition">
                        @error('tags')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror>
                        <small style="color: #7f8c8d;">Séparez les tags par des virgules (Ex: bassila, réunion, culture)</small>
                    </div>
                </div>

                <!-- Informations événement (visible uniquement si type = Événement) -->
                <div id="event-fields" class="card-section" style="display: none;">
                    <h2 class="card-title">📅 Informations de l'événement</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date">Date de l'événement</label>
                            <input type="date" name="event_date" id="event_date" class="form-control @error('event_date') is-invalid @enderror"
                                   value="{{ old('event_date') }}">
                            @error('event_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror>
                        </div>

                        <div class="form-group">
                            <label for="event_location">Lieu de l'événement</label>
                            <input type="text" name="event_location" id="event_location" class="form-control @error('event_location') is-invalid @enderror"
                                   value="{{ old('event_location') }}" placeholder="Ex: Bassila, Place publique">
                            @error('event_location')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 2px solid #ecf0f1;">
                    <a href="{{ route('news.index') }}" class="btn btn-secondary">
                        ← Annuler
                    </a>
                    <button type="submit" class="btn btn-primary btn-icon">
                        ✅ Publier
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="card" style="background: #ecf0f1;">
            <h3 style="font-size: 18px; margin-bottom: 15px; color: #2c3e50;">💡 Conseils de rédaction</h3>
            <ul style="color: #555; line-height: 1.8; margin: 0; padding-left: 20px;">
                <li>Choisissez un titre accrocheur et descriptif</li>
                <li>Le résumé doit donner envie de lire l'article complet</li>
                <li>Pour les événements, n'oubliez pas d'indiquer la date et le lieu</li>
                <li>Utilisez des tags pertinents pour faciliter la recherche</li>
                @if(auth()->user()->role !== 'admin' && auth()->user()->role !== 'moderator')
                    <li>Votre article sera en mode brouillon et nécessitera une validation avant publication</li>
                @else
                    <li>En tant qu'administrateur, votre article sera publié immédiatement</li>
                @endif
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Afficher/masquer les champs événement selon le type sélectionné
    document.getElementById('type').addEventListener('change', function() {
        const eventFields = document.getElementById('event-fields');
        const eventDateInput = document.getElementById('event_date');
        const eventLocationInput = document.getElementById('event_location');

        if (this.value === 'Événement') {
            eventFields.style.display = 'block';
            eventDateInput.setAttribute('required', 'required');
        } else {
            eventFields.style.display = 'none';
            eventDateInput.removeAttribute('required');
            eventLocationInput.value = '';
            eventDateInput.value = '';
        }
    });

    // Vérifier au chargement si un type est déjà sélectionné (en cas d'erreur de validation)
    if (document.getElementById('type').value === 'Événement') {
        document.getElementById('event-fields').style.display = 'block';
        document.getElementById('event_date').setAttribute('required', 'required');
    }

    // Compteur de caractères pour le résumé
    const excerptTextarea = document.getElementById('excerpt');
    if (excerptTextarea) {
        const maxLength = 500;
        excerptTextarea.addEventListener('input', function() {
            const remaining = maxLength - this.value.length;
            const counter = this.nextElementSibling?.nextElementSibling;
            if (counter) {
                counter.textContent = `${remaining} caractères restants`;
                counter.style.color = remaining < 50 ? '#e74c3c' : '#7f8c8d';
            }
        });
    }
</script>
@endpush
@endsection
