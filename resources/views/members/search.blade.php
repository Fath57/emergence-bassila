@extends('layouts.modern')

@section('title', 'Recherche Avancée - Emergence Bassila')

@section('content')
<div style="background: linear-gradient(135deg, #9b59b6, #e74c3c); padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">Recherche Avancée</h1>
        <p style="font-size: 18px; opacity: 0.95;">
            Trouvez des membres par localisation, compétences, profession...
        </p>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">
    <!-- Search Form -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Filtres de recherche</h2>
        </div>

        <form action="{{ route('members.search') }}" method="GET">
            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="name">Nom ou prénom</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ request('name') }}" placeholder="Ex: Jean">
                </div>

                <div class="form-group">
                    <label for="profession">Profession</label>
                    <input type="text" id="profession" name="profession" class="form-control" value="{{ request('profession') }}" placeholder="Ex: Développeur">
                </div>
            </div>

            <div class="form-row cols-3">
                <div class="form-group">
                    <label for="country">Pays actuel</label>
                    <select id="country" name="country" class="form-select">
                        <option value="">Tous les pays</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="city">Ville actuelle</label>
                    <select id="city" name="city" class="form-select">
                        <option value="">Toutes les villes</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="village">Village d'origine</label>
                    <input type="text" id="village" name="village" class="form-control" value="{{ request('village') }}" placeholder="Ex: Bassila">
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="skill">Compétence</label>
                    <input type="text" id="skill" name="skill" class="form-control" value="{{ request('skill') }}" placeholder="Ex: PHP, Marketing...">
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="form-check">
                        <input type="checkbox" id="open_to_opportunities" name="open_to_opportunities" class="form-check-input" value="1" {{ request('open_to_opportunities') ? 'checked' : '' }}>
                        <label for="open_to_opportunities" class="form-check-label">
                            Ouvert aux opportunités uniquement
                        </label>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <a href="{{ route('members.search') }}" class="btn btn-secondary">Réinitialiser</a>
                <button type="submit" class="btn btn-primary btn-icon">
                    🔍 Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Results -->
    @if(request()->hasAny(['name', 'profession', 'country', 'city', 'village', 'skill', 'open_to_opportunities']))
        <div style="margin-top: 40px;">
            <h2 style="margin-bottom: 20px; color: #2c3e50;">
                Résultats de la recherche
                <span style="color: #7f8c8d; font-size: 18px; font-weight: normal;">({{ $members->total() }} membre{{ $members->total() > 1 ? 's' : '' }})</span>
            </h2>

            @if($members->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px;">
                    @foreach($members as $member)
                        <div class="profile-card">
                            <div class="profile-header"></div>
                            <div class="profile-avatar">
                                {{ strtoupper(substr($member->first_name, 0, 1)) }}{{ strtoupper(substr($member->last_name, 0, 1)) }}
                            </div>
                            <div class="profile-body">
                                <h3 class="profile-name">{{ $member->full_name }}</h3>
                                <p class="profile-title">
                                    {{ $member->current_profession ?? 'Profession non renseignée' }}
                                </p>

                                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 15px;">
                                    @if($member->current_city)
                                        <span class="badge badge-primary">{{ $member->current_city }}</span>
                                    @endif
                                    @if($member->current_country)
                                        <span class="badge badge-info">{{ $member->current_country }}</span>
                                    @endif
                                    @if($member->open_to_opportunities)
                                        <span class="badge badge-success">Ouvert aux opportunités</span>
                                    @endif
                                </div>

                                @if($member->skills->count() > 0)
                                    <div style="margin-bottom: 15px;">
                                        <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 8px; font-weight: 600;">COMPÉTENCES</div>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: center;">
                                            @foreach($member->skills->take(3) as $skill)
                                                <span style="background: #ecf0f1; color: #2c3e50; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 500;">
                                                    {{ $skill->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <a href="{{ route('members.show', $member->id) }}" class="btn btn-primary btn-sm btn-block">
                                    Voir le profil
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div style="display: flex; justify-content: center; margin: 40px 0;">
                    {{ $members->links() }}
                </div>
            @else
                <div class="card text-center" style="padding: 60px 20px;">
                    <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucun résultat</h3>
                    <p style="color: #95a5a6;">Essayez de modifier vos critères de recherche</p>
                </div>
            @endif
        </div>
    @else
        <div class="card text-center" style="margin-top: 40px; padding: 60px 20px;">
            <h3 style="color: #7f8c8d; margin-bottom: 10px;">Commencez votre recherche</h3>
            <p style="color: #95a5a6;">Utilisez les filtres ci-dessus pour trouver des membres</p>
        </div>
    @endif
</div>
@endsection
