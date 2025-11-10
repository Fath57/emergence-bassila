@extends('layouts.modern')

@section('title', $opportunity->title . ' - Opportunités')

@section('content')
<!-- Hero Header -->
<div style="background: #2ecc71; padding: 80px 0; color: white;">
    <div class="container">
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 14px;">{{ $opportunity->type }}</span>
            @if($opportunity->contract_type)
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 14px;">{{ $opportunity->contract_type }}</span>
            @endif
            @if($opportunity->remote_possible)
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 14px;"> Télétravail possible</span>
            @endif
            @if($opportunity->is_featured)
                <span class="badge" style="background: #f39c12; color: white; font-size: 14px;"> À la une</span>
            @endif
        </div>

        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">{{ $opportunity->title }}</h1>

        <div style="display: flex; gap: 30px; font-size: 16px; margin-bottom: 20px; flex-wrap: wrap;">
            <div> <strong>{{ $opportunity->company_name }}</strong></div>
            <div> {{ $opportunity->location }}</div>
            @if($opportunity->salary_range)
                <div> {{ $opportunity->salary_range }}</div>
            @endif
        </div>

        <div style="display: flex; gap: 20px; font-size: 14px; opacity: 0.9; flex-wrap: wrap;">
            <span> Publié {{ $opportunity->created_at->diffForHumans() }}</span>
            <span>👁️ {{ $opportunity->views_count }} vues</span>
            @if($opportunity->deadline)
                <span style="background: #e74c3c; padding: 5px 12px; border-radius: 5px; font-weight: 600;">
                    ⏰ Date limite : {{ \Carbon\Carbon::parse($opportunity->deadline)->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">

        <!-- Colonne principale -->
        <div>
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 30px;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <!-- Description -->
            <div class="card">
                <h2 class="card-title">📄 Description du poste</h2>
                <div style="color: #555; line-height: 1.8; white-space: pre-line;">{{ $opportunity->description }}</div>
            </div>

            <!-- Prérequis -->
            @if($opportunity->requirements)
                <div class="card">
                    <h2 class="card-title">✅ Prérequis et compétences requises</h2>
                    <div style="color: #555; line-height: 1.8; white-space: pre-line;">{{ $opportunity->requirements }}</div>
                </div>
            @endif

            <!-- Informations complémentaires -->
            <div class="card">
                <h2 class="card-title">ℹ️ Informations complémentaires</h2>
                <div style="display: grid; gap: 15px;">
                    @if($opportunity->experience_required)
                        <div style="padding: 15px; background: #ecf0f1; border-radius: 8px;">
                            <strong style="color: #2c3e50;">🎯 Expérience requise :</strong>
                            <div style="color: #555; margin-top: 5px;">{{ $opportunity->experience_required }}</div>
                        </div>
                    @endif

                    @if($opportunity->start_date)
                        <div style="padding: 15px; background: #ecf0f1; border-radius: 8px;">
                            <strong style="color: #2c3e50;"> Date de début :</strong>
                            <div style="color: #555; margin-top: 5px;">{{ \Carbon\Carbon::parse($opportunity->start_date)->format('d/m/Y') }}</div>
                        </div>
                    @endif

                    @if($opportunity->company_website)
                        <div style="padding: 15px; background: #ecf0f1; border-radius: 8px;">
                            <strong style="color: #2c3e50;">🌐 Site web de l'entreprise :</strong>
                            <div style="margin-top: 5px;">
                                <a href="{{ $opportunity->company_website }}" target="_blank" style="color: #3498db; text-decoration: underline;">
                                    {{ $opportunity->company_website }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contact et candidature -->
            <div class="card" style="background: #f8f9fa; border: 2px solid #16a085;">
                <h2 class="card-title" style="color: #16a085;">📧 Comment postuler</h2>
                <div style="color: #555; line-height: 1.8;">
                    <p style="margin-bottom: 20px;">
                        Pour candidater à cette opportunité, veuillez contacter directement l'annonceur via les informations ci-dessous :
                    </p>

                    <div style="display: grid; gap: 15px;">
                        <div style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ecf0f1;">
                            <strong style="color: #2c3e50;">📧 Email :</strong>
                            <div style="margin-top: 5px;">
                                <a href="mailto:{{ $opportunity->contact_email }}" class="btn btn-primary btn-sm" style="display: inline-block; margin-top: 5px;">
                                    Envoyer un email
                                </a>
                            </div>
                        </div>

                        @if($opportunity->contact_phone)
                            <div style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ecf0f1;">
                                <strong style="color: #2c3e50;">📱 Téléphone :</strong>
                                <div style="color: #555; margin-top: 5px; font-size: 16px; font-weight: 600;">
                                    {{ $opportunity->contact_phone }}
                                </div>
                            </div>
                        @endif

                        @if($opportunity->application_url)
                            <div style="padding: 15px; background: white; border-radius: 8px; border: 1px solid #ecf0f1;">
                                <strong style="color: #2c3e50;">🔗 Formulaire de candidature :</strong>
                                <div style="margin-top: 5px;">
                                    <a href="{{ $opportunity->application_url }}" target="_blank" class="btn btn-success btn-sm" style="display: inline-block; margin-top: 5px;">
                                        Postuler en ligne
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Actions -->
            @auth
                @if(auth()->id() === $opportunity->user_id || auth()->user()->role === 'admin' || auth()->user()->role === 'moderator')
                    <div class="card">
                        <h3 class="card-title">⚙️ Actions</h3>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="{{ route('opportunities.edit', $opportunity->id) }}" class="btn btn-outline btn-sm">
                                ✏️ Modifier
                            </a>
                            <form method="POST" action="{{ route('opportunities.destroy', $opportunity->id) }}"
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette opportunité ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-block">
                                    🗑️ Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Info entreprise -->
            <div class="card">
                <h3 class="card-title"> À propos de l'entreprise</h3>
                <div style="text-align: center; margin-bottom: 15px;">
                    <div style="width: 80px; height: 80px; background: #2ecc71; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 32px; font-weight: 700;">
                        {{ strtoupper(substr($opportunity->company_name, 0, 1)) }}
                    </div>
                </div>
                <h4 style="text-align: center; font-size: 18px; color: #2c3e50; margin-bottom: 10px;">
                    {{ $opportunity->company_name }}
                </h4>
                <p style="text-align: center; color: #7f8c8d; font-size: 14px; margin-bottom: 15px;">
                     {{ $opportunity->location }}
                </p>
                @if($opportunity->company_website)
                    <a href="{{ $opportunity->company_website }}" target="_blank" class="btn btn-outline btn-sm btn-block">
                        🌐 Visiter le site web
                    </a>
                @endif
            </div>

            <!-- Annonceur -->
            <div class="card">
                <h3 class="card-title"> Annonceur</h3>
                <div style="text-align: center;">
                    <div style="width: 60px; height: 60px; background: #3498db; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 24px; font-weight: 700;">
                        {{ strtoupper(substr($opportunity->user->first_name, 0, 1) . substr($opportunity->user->last_name, 0, 1)) }}
                    </div>
                    <h4 style="font-size: 16px; color: #2c3e50; margin-bottom: 5px;">
                        {{ $opportunity->user->full_name }}
                    </h4>
                    @if($opportunity->user->current_profession)
                        <p style="color: #7f8c8d; font-size: 13px; margin-bottom: 10px;">
                            {{ $opportunity->user->current_profession }}
                        </p>
                    @endif
                    <a href="{{ route('members.show', $opportunity->user->id) }}" class="btn btn-outline btn-sm btn-block">
                        👁️ Voir le profil
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card" style="background: #ecf0f1;">
                <h3 class="card-title">📊 Statistiques</h3>
                <div style="display: grid; gap: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #7f8c8d;">Vues</span>
                        <strong style="color: #2c3e50;">{{ $opportunity->views_count }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #7f8c8d;">Publié</span>
                        <strong style="color: #2c3e50;">{{ $opportunity->created_at->diffForHumans() }}</strong>
                    </div>
                    @if($opportunity->category)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #7f8c8d;">Catégorie</span>
                            <strong style="color: #2c3e50;">{{ $opportunity->category->name }}</strong>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Boutons de partage -->
            <div class="card">
                <h3 class="card-title">📤 Partager</h3>
                <div style="display: grid; gap: 10px;">
                    <button onclick="window.print()" class="btn btn-outline btn-sm">
                        🖨️ Imprimer
                    </button>
                    <button onclick="copyToClipboard()" class="btn btn-outline btn-sm">
                        🔗 Copier le lien
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Opportunités similaires -->
    @if($similar->count() > 0)
        <div class="card" style="margin-top: 40px;">
            <h2 class="card-title"> Opportunités similaires</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @foreach($similar as $sim)
                    <a href="{{ route('opportunities.show', $sim->id) }}" style="text-decoration: none; color: inherit;">
                        <div style="border: 2px solid #ecf0f1; border-radius: 10px; padding: 20px; background: white; transition: all 0.3s; height: 100%;">
                            <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                <span class="badge badge-primary">{{ $sim->type }}</span>
                                @if($sim->remote_possible)
                                    <span class="badge badge-success"> Télétravail</span>
                                @endif
                            </div>
                            <h3 style="font-size: 16px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">{{ $sim->title }}</h3>
                            <p style="color: #7f8c8d; font-size: 13px;">
                                 {{ $sim->company_name }} •  {{ $sim->location }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function copyToClipboard() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(function() {
            alert('Lien copié dans le presse-papiers !');
        }, function() {
            alert('Erreur lors de la copie du lien.');
        });
    }
</script>
@endpush

@push('styles')
<style>
    @media (max-width: 768px) {
        .container > div {
            grid-template-columns: 1fr !important;
        }
    }

    a > div {
        transition: all 0.3s;
    }
    a > div:hover {
        border-color: #16a085 !important;
        box-shadow: 0 5px 15px rgba(22, 160, 133, 0.2);
        transform: translateY(-2px);
    }
</style>
@endpush
@endsection
