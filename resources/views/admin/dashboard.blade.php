@extends('layouts.admin')

@section('page-title', 'Dashboard Administration')
@section('page-subtitle', 'Vue d\'ensemble de la plateforme')

@section('content')

<!-- Statistiques -->
<div class="stat-grid">
    <!-- Utilisateurs -->
    <div class="stat-card info">
        <div class="stat-label">Total Utilisateurs</div>
        <div class="stat-value">{{ $stats['total_users'] }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">En attente</div>
        <div class="stat-value">{{ $stats['pending_users'] }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Actifs</div>
        <div class="stat-value">{{ $stats['active_users'] }}</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-label">Suspendus</div>
        <div class="stat-value">{{ $stats['suspended_users'] }}</div>
    </div>
</div>

<div class="stat-grid">
    <!-- Actualités -->
    <div class="stat-card info">
        <div class="stat-label">Total Actualités</div>
        <div class="stat-value">{{ $stats['total_news'] }}</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-label">Brouillons</div>
        <div class="stat-value">{{ $stats['pending_news'] }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Publiées</div>
        <div class="stat-value">{{ $stats['published_news'] }}</div>
    </div>

    <!-- Opportunités -->
    <div class="stat-card info">
        <div class="stat-label">Total Opportunités</div>
        <div class="stat-value">{{ $stats['total_opportunities'] }}</div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card warning">
        <div class="stat-label">Opp. En attente</div>
        <div class="stat-value">{{ $stats['pending_opportunities'] }}</div>
    </div>
    <div class="stat-card success">
        <div class="stat-label">Opp. Actives</div>
        <div class="stat-value">{{ $stats['active_opportunities'] }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-top: 30px;">

    <!-- Utilisateurs en attente -->
    @if($pendingUsers->count() > 0)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="card-title" style="margin: 0;">⏳ Utilisateurs en attente</h2>
                <a href="{{ route('admin.users.pending') }}" class="btn btn-sm btn-primary">Voir tout</a>
            </div>

            <div style="display: grid; gap: 15px;">
                @foreach($pendingUsers as $user)
                    <div style="border: 2px solid #ecf0f1; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 15px;">
                            <div style="flex: 1;">
                                <h4 style="font-size: 16px; color: #2c3e50; margin-bottom: 5px;">
                                    {{ $user->full_name }}
                                </h4>
                                <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 5px;">
                                    📧 {{ $user->email }}
                                </p>
                                <p style="font-size: 12px; color: #95a5a6;">
                                    📅 Inscrit {{ $user->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <form method="POST" action="{{ route('admin.users.approve', $user->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✅</button>
                                </form>
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline btn-sm" title="Voir">👁️</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Actualités en attente -->
    @if($pendingNews->count() > 0)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="card-title" style="margin: 0;">📰 Actualités en attente</h2>
                <a href="{{ route('admin.news.pending') }}" class="btn btn-sm btn-primary">Voir tout</a>
            </div>

            <div style="display: grid; gap: 15px;">
                @foreach($pendingNews as $news)
                    <div style="border: 2px solid #ecf0f1; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 15px;">
                            <div style="flex: 1;">
                                <div style="margin-bottom: 8px;">
                                    <span class="badge badge-primary">{{ $news->type }}</span>
                                </div>
                                <h4 style="font-size: 16px; color: #2c3e50; margin-bottom: 5px;">
                                    {{ Str::limit($news->title, 50) }}
                                </h4>
                                <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 5px;">
                                    👤 {{ $news->user->full_name }}
                                </p>
                                <p style="font-size: 12px; color: #95a5a6;">
                                    📅 {{ $news->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <form method="POST" action="{{ route('admin.news.publish', $news->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Publier">✅</button>
                                </form>
                                <a href="{{ route('admin.news.show', $news->id) }}" class="btn btn-outline btn-sm" title="Voir">👁️</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Opportunités en attente -->
    @if($pendingOpportunities->count() > 0)
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="card-title" style="margin: 0;">💼 Opportunités en attente</h2>
                <a href="{{ route('admin.opportunities.pending') }}" class="btn btn-sm btn-primary">Voir tout</a>
            </div>

            <div style="display: grid; gap: 15px;">
                @foreach($pendingOpportunities as $opp)
                    <div style="border: 2px solid #ecf0f1; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 15px;">
                            <div style="flex: 1;">
                                <div style="margin-bottom: 8px;">
                                    <span class="badge badge-primary">{{ $opp->type }}</span>
                                </div>
                                <h4 style="font-size: 16px; color: #2c3e50; margin-bottom: 5px;">
                                    {{ Str::limit($opp->title, 50) }}
                                </h4>
                                <p style="font-size: 13px; color: #7f8c8d; margin-bottom: 5px;">
                                    🏢 {{ $opp->company_name }} • 👤 {{ $opp->user->full_name }}
                                </p>
                                <p style="font-size: 12px; color: #95a5a6;">
                                    📅 {{ $opp->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <form method="POST" action="{{ route('admin.opportunities.activate', $opp->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Approuver">✅</button>
                                </form>
                                <a href="{{ route('admin.opportunities.show', $opp->id) }}" class="btn btn-outline btn-sm" title="Voir">👁️</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Messages d'aide si rien en attente -->
@if($pendingUsers->count() === 0 && $pendingNews->count() === 0 && $pendingOpportunities->count() === 0)
    <div class="card text-center" style="margin-top: 30px; padding: 60px 20px;">
        <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
        <h3 style="color: #27ae60; margin-bottom: 10px;">Tout est à jour !</h3>
        <p style="color: #7f8c8d;">Aucun élément en attente de validation pour le moment.</p>
    </div>
@endif

@endsection
