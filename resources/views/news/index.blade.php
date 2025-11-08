@extends('layouts.modern')

@section('title', 'Actualités & Événements - Emergence Bassila')

@section('content')
<div style="background: #8e44ad; padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">Actualités & Événements</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 700px; margin: 0 auto;">
            Restez informé des dernières nouvelles et événements de la communauté de Bassila
        </p>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">

    <!-- Actions -->
    @auth
        <div class="card" style="text-align: center; margin-bottom: 30px;">
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('news.create') }}" class="btn btn-primary btn-icon">
                    ✍️ Publier une actualité
                </a>
                <a href="{{ route('news.my') }}" class="btn btn-secondary">
                    📰 Mes actualités
                </a>
            </div>
        </div>
    @endauth

    <!-- Filtres -->
    <div class="card" style="margin-bottom: 30px;">
        <form method="GET" action="{{ route('news.index') }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <select name="type" class="form-select" style="flex: 1; min-width: 200px;">
                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Tous les types</option>
                <option value="Actualité" {{ request('type') == 'Actualité' ? 'selected' : '' }}>📰 Actualités</option>
                <option value="Événement" {{ request('type') == 'Événement' ? 'selected' : '' }}>📅 Événements</option>
                <option value="Annonce" {{ request('type') == 'Annonce' ? 'selected' : '' }}>📢 Annonces</option>
                <option value="Culture" {{ request('type') == 'Culture' ? 'selected' : '' }}>🎭 Culture</option>
            </select>

            @if($categories->count() > 0)
                <select name="category" class="form-select" style="flex: 1; min-width: 200px;">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="btn btn-primary">Filtrer</button>
            @if(request()->hasAny(['type', 'category']))
                <a href="{{ route('news.index') }}" class="btn btn-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- À la une -->
    @if($featured->count() > 0 && !request()->hasAny(['type', 'category']))
        <div style="margin-bottom: 50px;">
            <h2 style="font-size: 28px; margin-bottom: 25px; color: #2c3e50;">⭐ À la une</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
                @foreach($featured as $article)
                    <a href="{{ route('news.show', $article->slug) }}" style="text-decoration: none;">
                        <div class="card" style="height: 100%; transition: transform 0.3s;">
                            <div style="height: 200px; background: #3498db; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                                @if($article->type == 'Événement')
                                    📅
                                @elseif($article->type == 'Culture')
                                    🎭
                                @elseif($article->type == 'Annonce')
                                    📢
                                @else
                                    📰
                                @endif
                            </div>
                            <span class="badge badge-warning">À la une</span>
                            <span class="badge badge-primary" style="margin-left: 5px;">{{ $article->type }}</span>
                            <h3 style="font-size: 20px; font-weight: 700; color: #2c3e50; margin: 15px 0; line-height: 1.4;">
                                {{ $article->title }}
                            </h3>
                            <p style="color: #7f8c8d; font-size: 14px; line-height: 1.6;">
                                {{ Str::limit($article->excerpt, 100) }}
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #ecf0f1; font-size: 13px; color: #95a5a6;">
                                <span>👤 {{ $article->user->first_name }}</span>
                                <span>📅 {{ $article->published_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Toutes les actualités -->
    <div>
        <h2 style="font-size: 28px; margin-bottom: 25px; color: #2c3e50;">
            📋 Toutes les actualités
            <span style="font-size: 16px; color: #7f8c8d; font-weight: normal;">({{ $news->total() }})</span>
        </h2>

        @if($news->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; margin-bottom: 40px;">
                @foreach($news as $article)
                    <a href="{{ route('news.show', $article->slug) }}" style="text-decoration: none;">
                        <div class="card" style="height: 100%; transition: transform 0.3s;">
                            <div style="height: 180px; background: #3498db; border-radius: 10px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 42px;">
                                @if($article->type == 'Événement')
                                    📅
                                @elseif($article->type == 'Culture')
                                    🎭
                                @elseif($article->type == 'Annonce')
                                    📢
                                @else
                                    📰
                                @endif
                            </div>

                            <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                                <span class="badge badge-primary">{{ $article->type }}</span>
                                @if($article->category)
                                    <span class="badge badge-light">{{ $article->category->name }}</span>
                                @endif
                            </div>

                            <h3 style="font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 10px; line-height: 1.4;">
                                {{ Str::limit($article->title, 60) }}
                            </h3>

                            @if($article->excerpt)
                                <p style="color: #7f8c8d; font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                                    {{ Str::limit($article->excerpt, 80) }}
                                </p>
                            @endif

                            @if($article->type == 'Événement' && $article->event_date)
                                <div style="background: #ecf0f1; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                                    <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 3px;">📅 Date de l'événement</div>
                                    <div style="font-weight: 600; color: #2c3e50;">{{ \Carbon\Carbon::parse($article->event_date)->format('d/m/Y') }}</div>
                                    @if($article->event_location)
                                        <div style="font-size: 13px; color: #7f8c8d; margin-top: 5px;">📍 {{ $article->event_location }}</div>
                                    @endif
                                </div>
                            @endif

                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #ecf0f1; font-size: 13px; color: #95a5a6;">
                                <span>👤 {{ $article->user->first_name }}</span>
                                <span>👁️ {{ $article->views_count }} vues</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="display: flex; justify-content: center;">
                {{ $news->links() }}
            </div>
        @else
            <div class="card text-center" style="padding: 60px 20px;">
                <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucune actualité pour le moment</h3>
                <p style="color: #95a5a6;">Soyez le premier à partager des nouvelles avec la communauté !</p>
                @auth
                    <a href="{{ route('news.create') }}" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">
                        ✍️ Publier une actualité
                    </a>
                @endauth
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush
@endsection
