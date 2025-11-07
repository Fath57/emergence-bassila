@extends('layouts.modern')

@section('title', 'Mes opportunités - Emergence Bassila')

@section('content')
<div style="background: linear-gradient(135deg, #16a085, #2ecc71); padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">💼 Mes opportunités</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 700px; margin: 0 auto;">
            Gérez vos offres d'emploi, stages et collaborations
        </p>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 30px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 30px;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <!-- Actions -->
    <div class="card" style="text-align: center; margin-bottom: 30px;">
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('opportunities.create') }}" class="btn btn-primary btn-icon">
                ➕ Nouvelle opportunité
            </a>
            <a href="{{ route('opportunities.index') }}" class="btn btn-secondary">
                📋 Toutes les opportunités
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="card text-center" style="background: linear-gradient(135deg, #3498db, #2ecc71);">
            <div style="color: white;">
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['total'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">Total</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
            <div style="color: white;">
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['active'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">Actives</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <div style="color: white;">
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['pending'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">En attente</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
            <div style="color: white;">
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['views'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">Vues totales</div>
            </div>
        </div>
    </div>

    <!-- Liste des opportunités -->
    @if($opportunities->count() > 0)
        <div class="card">
            <h2 class="card-title">Mes opportunités ({{ $opportunities->total() }})</h2>

            <div style="display: grid; gap: 20px;">
                @foreach($opportunities as $opp)
                    <div style="border: 2px solid #ecf0f1; border-radius: 10px; padding: 20px; background: #f8f9fa; transition: all 0.3s;">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 300px;">
                                <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                    <span class="badge badge-primary">{{ $opp->type }}</span>
                                    @if($opp->status === 'active')
                                        <span class="badge badge-success">✅ Active</span>
                                    @elseif($opp->status === 'pending')
                                        <span class="badge badge-warning">⏳ En attente</span>
                                    @elseif($opp->status === 'closed')
                                        <span class="badge badge-secondary">📦 Fermée</span>
                                    @else
                                        <span class="badge badge-danger">❌ Expirée</span>
                                    @endif
                                    @if($opp->is_featured)
                                        <span class="badge badge-warning">⭐ À la une</span>
                                    @endif
                                    @if($opp->remote_possible)
                                        <span class="badge badge-success">🌍 Télétravail</span>
                                    @endif
                                    @if($opp->contract_type)
                                        <span class="badge badge-light">{{ $opp->contract_type }}</span>
                                    @endif
                                </div>

                                <h3 style="font-size: 20px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">
                                    @if($opp->status === 'active')
                                        <a href="{{ route('opportunities.show', $opp->id) }}" style="color: #2c3e50; text-decoration: none;">
                                            {{ $opp->title }}
                                        </a>
                                    @else
                                        {{ $opp->title }}
                                    @endif
                                </h3>

                                <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 10px;">
                                    🏢 {{ $opp->company_name }} • 📍 {{ $opp->location }}
                                </p>

                                @if($opp->salary_range)
                                    <p style="color: #27ae60; font-size: 14px; font-weight: 600; margin-bottom: 10px;">
                                        💰 {{ $opp->salary_range }}
                                    </p>
                                @endif

                                @if($opp->deadline)
                                    <div style="background: white; padding: 10px; border-radius: 8px; margin-bottom: 10px; display: inline-block;">
                                        <span style="font-size: 13px; color: {{ \Carbon\Carbon::parse($opp->deadline)->isPast() ? '#e74c3c' : '#7f8c8d' }};">
                                            ⏰ Date limite : {{ \Carbon\Carbon::parse($opp->deadline)->format('d/m/Y') }}
                                            @if(\Carbon\Carbon::parse($opp->deadline)->isPast())
                                                (Expirée)
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                <div style="display: flex; gap: 20px; font-size: 13px; color: #95a5a6; margin-top: 10px;">
                                    <span>📅 Créé {{ $opp->created_at->diffForHumans() }}</span>
                                    <span>👁️ {{ $opp->views_count }} vues</span>
                                    @if($opp->category)
                                        <span>📂 {{ $opp->category->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 10px; min-width: 150px;">
                                @if($opp->status === 'active')
                                    <a href="{{ route('opportunities.show', $opp->id) }}" class="btn btn-primary btn-sm btn-block">
                                        👁️ Voir
                                    </a>
                                @endif
                                <a href="{{ route('opportunities.edit', $opp->id) }}" class="btn btn-outline btn-sm btn-block">
                                    ✏️ Modifier
                                </a>
                                <form method="POST" action="{{ route('opportunities.destroy', $opp->id) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette opportunité ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-block">
                                        🗑️ Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="display: flex; justify-content: center; margin-top: 30px;">
                {{ $opportunities->links() }}
            </div>
        </div>
    @else
        <div class="card text-center" style="padding: 60px 20px;">
            <div style="font-size: 64px; margin-bottom: 20px;">💼</div>
            <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucune opportunité pour le moment</h3>
            <p style="color: #95a5a6; margin-bottom: 30px;">Commencez par publier votre première opportunité</p>
            <a href="{{ route('opportunities.create') }}" class="btn btn-primary btn-icon" style="display: inline-block;">
                ➕ Créer ma première opportunité
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
    .card > div:hover {
        border-color: #16a085 !important;
        box-shadow: 0 5px 15px rgba(22, 160, 133, 0.1);
    }
</style>
@endpush
@endsection
