@extends('layouts.modern')

@section('title', $article->title . ' - Emergence Bassila')

@section('content')
<!-- Header Article -->
<div style="background: #8e44ad; padding: 80px 0; color: white;">
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <span class="badge" style="background: rgba(255,255,255,0.3); font-size: 14px;">{{ $article->type }}</span>
            @if($article->category)
                <span class="badge" style="background: rgba(255,255,255,0.2); font-size: 14px;">{{ $article->category->name }}</span>
            @endif
            @if($article->is_featured)
                <span class="badge badge-warning" style="font-size: 14px;">⭐ À la une</span>
            @endif
        </div>

        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700; line-height: 1.3;">
            {{ $article->title }}
        </h1>

        @if($article->excerpt)
            <p style="font-size: 20px; opacity: 0.95; line-height: 1.6;">
                {{ $article->excerpt }}
            </p>
        @endif

        <div style="display: flex; gap: 30px; margin-top: 30px; align-items: center; flex-wrap: wrap; font-size: 15px;">
            <a href="{{ route('members.show', $article->user->id) }}" style="display: flex; align-items: center; gap: 10px; color: white; text-decoration: none;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700;">
                    {{ strtoupper(substr($article->user->first_name, 0, 1)) }}{{ strtoupper(substr($article->user->last_name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight: 600;">{{ $article->user->full_name }}</div>
                    <div style="opacity: 0.8; font-size: 13px;">{{ $article->user->current_profession ?? 'Membre' }}</div>
                </div>
            </a>

            <div style="opacity: 0.9;">
                <div style="font-size: 13px; opacity: 0.8;">Publié le</div>
                <div style="font-weight: 600;">{{ $article->published_at->format('d M Y') }}</div>
            </div>

            <div style="opacity: 0.9;">
                <div style="font-size: 13px; opacity: 0.8;">Lectures</div>
                <div style="font-weight: 600;">{{ $article->views_count }} vues</div>
            </div>
        </div>

        @if($article->type == 'Événement' && $article->event_date)
            <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 15px; margin-top: 30px; backdrop-filter: blur(10px);">
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div style="font-size: 48px;">📅</div>
                    <div>
                        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Date de l'événement</div>
                        <div style="font-size: 24px; font-weight: 700;">{{ \Carbon\Carbon::parse($article->event_date)->format('d M Y') }}</div>
                        @if($article->event_location)
                            <div style="font-size: 15px; margin-top: 5px; opacity: 0.95;">📍 {{ $article->event_location }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">

        <!-- Main Content -->
        <div>
            <div class="card">
                @if(session('success'))
                    <div class="alert alert-success">
                        ✅ {{ session('success') }}
                    </div>
                @endif

                <div style="font-size: 17px; line-height: 1.8; color: #2c3e50;">
                    {!! nl2br(e($article->content)) !!}
                </div>

                <!-- Tags -->
                @if($article->tags && count(json_decode($article->tags)) > 0)
                    <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #ecf0f1;">
                        <h3 style="font-size: 16px; color: #7f8c8d; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Tags</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            @foreach(json_decode($article->tags) as $tag)
                                <span style="background: #ecf0f1; color: #2c3e50; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 500;">
                                    #{{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Articles similaires -->
            @if($related->count() > 0)
                <div class="card">
                    <h2 class="card-title">Articles similaires</h2>
                    <div style="display: grid; gap: 20px;">
                        @foreach($related as $relatedArticle)
                            <a href="{{ route('news.show', $relatedArticle->slug) }}" style="text-decoration: none;">
                                <div style="display: flex; gap: 15px; padding: 15px; background: #f8f9fa; border-radius: 10px; transition: all 0.3s;">
                                    <div style="width: 100px; height: 100px; background: #3498db; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 30px;">
                                        @if($relatedArticle->type == 'Événement')📅
                                        @elseif($relatedArticle->type == 'Culture')🎭
                                        @elseif($relatedArticle->type == 'Annonce')📢
                                        @else 📰@endif
                                    </div>
                                    <div style="flex: 1;">
                                        <span class="badge badge-primary" style="margin-bottom: 8px;">{{ $relatedArticle->type }}</span>
                                        <h4 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">
                                            {{ Str::limit($relatedArticle->title, 50) }}
                                        </h4>
                                        <div style="font-size: 13px; color: #7f8c8d;">
                                            {{ $relatedArticle->published_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Actions -->
            <div class="card">
                <h3 class="card-title">Actions</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="{{ route('news.index') }}" class="btn btn-secondary btn-block">
                        ← Retour aux actualités
                    </a>

                    @auth
                        @if(auth()->id() === $article->user_id || auth()->user()->role === 'admin')
                            <button class="btn btn-outline btn-block">
                                ✏️ Modifier
                            </button>
                        @endif
                    @endauth

                    <button class="btn btn-outline btn-block" onclick="window.print()">
                        🖨️ Imprimer
                    </button>

                    <button class="btn btn-outline btn-block" onclick="navigator.share ? navigator.share({title: '{{ $article->title }}', url: window.location.href}) : alert('Partage non supporté')">
                        🔗 Partager
                    </button>
                </div>
            </div>

            <!-- Info Auteur -->
            <div class="card">
                <h3 class="card-title">À propos de l'auteur</h3>
                <a href="{{ route('members.show', $article->user->id) }}" style="text-decoration: none;">
                    <div style="text-align: center;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: #3498db; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: 700;">
                            {{ strtoupper(substr($article->user->first_name, 0, 1)) }}{{ strtoupper(substr($article->user->last_name, 0, 1)) }}
                        </div>
                        <h4 style="font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">
                            {{ $article->user->full_name }}
                        </h4>
                        <p style="color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">
                            {{ $article->user->current_profession ?? 'Membre de la communauté' }}
                        </p>
                        @if($article->user->bio)
                            <p style="color: #555; font-size: 13px; line-height: 1.6;">
                                {{ Str::limit($article->user->bio, 100) }}
                            </p>
                        @endif
                        <a href="{{ route('members.show', $article->user->id) }}" class="btn btn-primary btn-sm btn-block" style="margin-top: 15px;">
                            Voir le profil
                        </a>
                    </div>
                </a>
            </div>

            <!-- Stats -->
            <div class="card">
                <h3 class="card-title">Statistiques</h3>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ecf0f1;">
                        <span style="color: #7f8c8d;">👁️ Vues</span>
                        <span style="font-weight: 700; color: #2c3e50;">{{ $article->views_count }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ecf0f1;">
                        <span style="color: #7f8c8d;">📅 Publié</span>
                        <span style="font-weight: 700; color: #2c3e50;">{{ $article->published_at->diffForHumans() }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                        <span style="color: #7f8c8d;">🔄 Mis à jour</span>
                        <span style="font-weight: 700; color: #2c3e50;">{{ $article->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 992px) {
        .container > div {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
@endsection
