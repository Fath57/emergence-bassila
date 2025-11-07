@extends('layouts.modern')

@section('title', 'Opportunités - Emergence Bassila')

@section('content')
<div style="background: linear-gradient(135deg, #16a085, #2ecc71); padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">💼 Opportunités</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 700px; margin: 0 auto;">
            Découvrez les offres d'emploi, stages, collaborations et bénévolat proposés par la communauté
        </p>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">

    <!-- Statistiques -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="card text-center" style="background: linear-gradient(135deg, #3498db, #2ecc71);">
            <div style="color: white;">
                <div style="font-size: 36px; font-weight: 700; margin-bottom: 10px;">{{ $stats['total'] }}</div>
                <div style="font-size: 14px; opacity: 0.95;">Total</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
            <div style="color: white;">
                <div style="font-size: 36px; font-weight: 700; margin-bottom: 10px;">{{ $stats['emploi'] }}</div>
                <div style="font-size: 14px; opacity: 0.95;">Emplois</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <div style="color: white;">
                <div style="font-size: 36px; font-weight: 700; margin-bottom: 10px;">{{ $stats['stage'] }}</div>
                <div style="font-size: 14px; opacity: 0.95;">Stages</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <div style="color: white;">
                <div style="font-size: 36px; font-weight: 700; margin-bottom: 10px;">{{ $stats['collaboration'] }}</div>
                <div style="font-size: 14px; opacity: 0.95;">Collaborations</div>
            </div>
        </div>
    </div>

    <!-- À la une -->
    @if($featured->count() > 0)
        <div class="card" style="margin-bottom: 40px;">
            <h2 class="card-title">⭐ À la une</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @foreach($featured as $opp)
                    <a href="{{ route('opportunities.show', $opp->id) }}" style="text-decoration: none; color: inherit;">
                        <div style="border: 2px solid #f39c12; border-radius: 10px; padding: 20px; background: linear-gradient(135deg, #fff9e6, #ffffff); transition: all 0.3s; height: 100%;">
                            <div style="display: flex; gap: 10px; margin-bottom: 12px; flex-wrap: wrap;">
                                <span class="badge badge-warning">⭐ À la une</span>
                                <span class="badge badge-primary">{{ $opp->type }}</span>
                                @if($opp->remote_possible)
                                    <span class="badge badge-success">🌍 Télétravail</span>
                                @endif
                            </div>
                            <h3 style="font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">{{ $opp->title }}</h3>
                            <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 10px;">
                                🏢 {{ $opp->company_name }} • 📍 {{ $opp->location }}
                            </p>
                            @if($opp->deadline)
                                <p style="color: #e74c3c; font-size: 13px; font-weight: 600;">
                                    ⏰ Date limite : {{ \Carbon\Carbon::parse($opp->deadline)->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card" style="margin-bottom: 30px;">
        <h2 class="card-title">🔍 Filtrer les opportunités</h2>
        <form method="GET" action="{{ route('opportunities.index') }}">
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">-- Tous --</option>
                        <option value="Emploi" {{ request('type') == 'Emploi' ? 'selected' : '' }}>💼 Emploi</option>
                        <option value="Stage" {{ request('type') == 'Stage' ? 'selected' : '' }}>🎓 Stage</option>
                        <option value="Bénévolat" {{ request('type') == 'Bénévolat' ? 'selected' : '' }}>❤️ Bénévolat</option>
                        <option value="Collaboration" {{ request('type') == 'Collaboration' ? 'selected' : '' }}>🤝 Collaboration</option>
                        <option value="Autre" {{ request('type') == 'Autre' ? 'selected' : '' }}>📌 Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contract_type">Type de contrat</label>
                    <select name="contract_type" id="contract_type" class="form-control">
                        <option value="">-- Tous --</option>
                        <option value="CDI" {{ request('contract_type') == 'CDI' ? 'selected' : '' }}>CDI</option>
                        <option value="CDD" {{ request('contract_type') == 'CDD' ? 'selected' : '' }}>CDD</option>
                        <option value="Freelance" {{ request('contract_type') == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                        <option value="Stage" {{ request('contract_type') == 'Stage' ? 'selected' : '' }}>Stage</option>
                        <option value="Bénévolat" {{ request('contract_type') == 'Bénévolat' ? 'selected' : '' }}>Bénévolat</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="location">Localisation</label>
                    <input type="text" name="location" id="location" class="form-control"
                           value="{{ request('location') }}" placeholder="Ville, pays...">
                </div>

                <div class="form-group">
                    <label for="category_id">Catégorie</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">-- Toutes --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="remote_possible" value="1"
                           {{ request('remote_possible') ? 'checked' : '' }} style="width: 20px; height: 20px;">
                    <span>🌍 Télétravail possible uniquement</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">
                    🔍 Rechercher
                </button>
                <a href="{{ route('opportunities.index') }}" class="btn btn-secondary">
                    🔄 Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Liste des opportunités -->
    @if($opportunities->count() > 0)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="card-title" style="margin: 0;">Toutes les opportunités ({{ $opportunities->total() }})</h2>
                @auth
                    <a href="{{ route('opportunities.create') }}" class="btn btn-primary btn-sm">
                        ➕ Publier une opportunité
                    </a>
                @endauth
            </div>

            <div style="display: grid; gap: 20px;">
                @foreach($opportunities as $opp)
                    <a href="{{ route('opportunities.show', $opp->id) }}" style="text-decoration: none; color: inherit;">
                        <div style="border: 2px solid #ecf0f1; border-radius: 10px; padding: 20px; background: white; transition: all 0.3s;">
                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 300px;">
                                    <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                        <span class="badge badge-primary">{{ $opp->type }}</span>
                                        @if($opp->contract_type)
                                            <span class="badge badge-secondary">{{ $opp->contract_type }}</span>
                                        @endif
                                        @if($opp->remote_possible)
                                            <span class="badge badge-success">🌍 Télétravail</span>
                                        @endif
                                        @if($opp->is_featured)
                                            <span class="badge badge-warning">⭐</span>
                                        @endif
                                        @if($opp->category)
                                            <span class="badge badge-light">{{ $opp->category->name }}</span>
                                        @endif
                                    </div>

                                    <h3 style="font-size: 20px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">
                                        {{ $opp->title }}
                                    </h3>

                                    <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 10px;">
                                        🏢 <strong>{{ $opp->company_name }}</strong> • 📍 {{ $opp->location }}
                                    </p>

                                    @if($opp->salary_range)
                                        <p style="color: #27ae60; font-size: 14px; font-weight: 600; margin-bottom: 10px;">
                                            💰 {{ $opp->salary_range }}
                                        </p>
                                    @endif

                                    <p style="color: #555; font-size: 14px; line-height: 1.6; margin-bottom: 10px;">
                                        {{ Str::limit($opp->description, 150) }}
                                    </p>

                                    <div style="display: flex; gap: 20px; font-size: 13px; color: #95a5a6; margin-top: 10px;">
                                        <span>📅 Publié {{ $opp->created_at->diffForHumans() }}</span>
                                        <span>👁️ {{ $opp->views_count }} vues</span>
                                        @if($opp->deadline)
                                            <span style="color: #e74c3c; font-weight: 600;">
                                                ⏰ Date limite : {{ \Carbon\Carbon::parse($opp->deadline)->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div style="text-align: center;">
                                    <div class="btn btn-primary btn-sm">
                                        👁️ Voir l'offre
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="display: flex; justify-content: center; margin-top: 30px;">
                {{ $opportunities->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="card text-center" style="padding: 60px 20px;">
            <div style="font-size: 64px; margin-bottom: 20px;">💼</div>
            <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucune opportunité trouvée</h3>
            <p style="color: #95a5a6; margin-bottom: 30px;">
                @if(request()->hasAny(['type', 'category_id', 'location', 'remote_possible', 'contract_type']))
                    Aucune opportunité ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                @else
                    Soyez le premier à publier une opportunité pour la communauté !
                @endif
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                @if(request()->hasAny(['type', 'category_id', 'location', 'remote_possible', 'contract_type']))
                    <a href="{{ route('opportunities.index') }}" class="btn btn-secondary">
                        🔄 Réinitialiser les filtres
                    </a>
                @endif
                @auth
                    <a href="{{ route('opportunities.create') }}" class="btn btn-primary">
                        ➕ Publier une opportunité
                    </a>
                @endauth
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    a > div {
        transition: all 0.3s;
    }
    a > div:hover {
        border-color: #3498db !important;
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        transform: translateY(-2px);
    }
</style>
@endpush
@endsection
