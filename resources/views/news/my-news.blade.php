@extends('layouts.modern')

@section('title', 'Mes actualités - Emergence Bassila')

@section('content')
<div style="background: linear-gradient(135deg, #8e44ad, #3498db); padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">📰 Mes actualités</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 700px; margin: 0 auto;">
            Gérez vos articles et événements publiés
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
            <a href="{{ route('news.create') }}" class="btn btn-primary btn-icon">
                ✍️ Nouvelle publication
            </a>
            <a href="{{ route('news.index') }}" class="btn btn-secondary">
                📋 Toutes les actualités
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
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['published'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">Publiés</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
            <div style="color: white;">
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['drafts'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">Brouillons</div>
            </div>
        </div>
        <div class="card text-center" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
            <div style="color: white;">
                <div style="font-size: 42px; font-weight: 700; margin-bottom: 10px;">{{ $stats['views'] }}</div>
                <div style="font-size: 16px; opacity: 0.95;">Vues totales</div>
            </div>
        </div>
    </div>

    <!-- Liste des articles -->
    @if($articles->count() > 0)
        <div class="card">
            <h2 class="card-title">Mes publications ({{ $articles->total() }})</h2>

            <div style="display: grid; gap: 20px;">
                @foreach($articles as $article)
                    <div style="border: 2px solid #ecf0f1; border-radius: 10px; padding: 20px; background: #f8f9fa; transition: all 0.3s;">
                        <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 300px;">
                                <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                    <span class="badge badge-primary">{{ $article->type }}</span>
                                    @if($article->status === 'published')
                                        <span class="badge badge-success">✅ Publié</span>
                                    @elseif($article->status === 'draft')
                                        <span class="badge badge-warning">📝 Brouillon</span>
                                    @else
                                        <span class="badge badge-secondary">📦 Archivé</span>
                                    @endif
                                    @if($article->is_featured)
                                        <span class="badge badge-warning">⭐ À la une</span>
                                    @endif
                                    @if($article->category)
                                        <span class="badge badge-light">{{ $article->category->name }}</span>
                                    @endif
                                </div>

                                <h3 style="font-size: 20px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">
                                    @if($article->status === 'published')
                                        <a href="{{ route('news.show', $article->slug) }}" style="color: #2c3e50; text-decoration: none;">
                                            {{ $article->title }}
                                        </a>
                                    @else
                                        {{ $article->title }}
                                    @endif
                                </h3>

                                @if($article->excerpt)
                                    <p style="color: #7f8c8d; font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                                        {{ Str::limit($article->excerpt, 120) }}
                                    </p>
                                @endif

                                @if($article->type == 'Événement' && $article->event_date)
                                    <div style="background: white; padding: 10px; border-radius: 8px; margin-bottom: 10px; display: inline-block;">
                                        <span style="font-size: 13px; color: #7f8c8d;">📅 {{ \Carbon\Carbon::parse($article->event_date)->format('d/m/Y') }}</span>
                                        @if($article->event_location)
                                            <span style="font-size: 13px; color: #7f8c8d; margin-left: 10px;">📍 {{ $article->event_location }}</span>
                                        @endif
                                    </div>
                                @endif

                                <div style="display: flex; gap: 20px; font-size: 13px; color: #95a5a6; margin-top: 10px;">
                                    <span>📅 Créé {{ $article->created_at->diffForHumans() }}</span>
                                    @if($article->published_at)
                                        <span>🌐 Publié {{ $article->published_at->diffForHumans() }}</span>
                                    @endif
                                    <span>👁️ {{ $article->views_count }} vues</span>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 10px; min-width: 150px;">
                                @if($article->status === 'published')
                                    <a href="{{ route('news.show', $article->slug) }}" class="btn btn-primary btn-sm btn-block">
                                        👁️ Voir
                                    </a>
                                @endif
                                <button class="btn btn-outline btn-sm btn-block">
                                    ✏️ Modifier
                                </button>
                                <form method="POST" action="#" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
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
                {{ $articles->links() }}
            </div>
        </div>
    @else
        <div class="card text-center" style="padding: 60px 20px;">
            <div style="font-size: 64px; margin-bottom: 20px;">📝</div>
            <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucune publication pour le moment</h3>
            <p style="color: #95a5a6; margin-bottom: 30px;">Commencez par partager votre première actualité avec la communauté</p>
            <a href="{{ route('news.create') }}" class="btn btn-primary btn-icon" style="display: inline-block;">
                ✍️ Créer ma première publication
            </a>
        </div>
    @endif
</div>

@push('styles')
<style>
    .card > div:hover {
        border-color: #3498db !important;
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
    }
</style>
@endpush
@endsection
