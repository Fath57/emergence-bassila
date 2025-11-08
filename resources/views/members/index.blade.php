@extends('layouts.modern')

@section('title', 'Annuaire des Ressortissants - Emergence Bassila')

@section('content')
<div style="background: #3498db; padding: 60px 0; color: white;">
    <div class="container text-center">
        <h1 style="font-size: 42px; margin-bottom: 20px; font-weight: 700;">Annuaire des Ressortissants</h1>
        <p style="font-size: 18px; opacity: 0.95; max-width: 600px; margin: 0 auto;">
            Découvrez les membres de la communauté de Bassila à travers le monde
        </p>

        <!-- Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; max-width: 800px; margin: 40px auto 0;">
            <div style="background: rgba(255,255,255,0.15); padding: 25px; border-radius: 15px; backdrop-filter: blur(10px);">
                <div style="font-size: 36px; font-weight: 700;">{{ $stats['total'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">Membres actifs</div>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 25px; border-radius: 15px; backdrop-filter: blur(10px);">
                <div style="font-size: 36px; font-weight: 700;">{{ $stats['countries'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">Pays représentés</div>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 25px; border-radius: 15px; backdrop-filter: blur(10px);">
                <div style="font-size: 36px; font-weight: 700;">{{ $stats['cities'] }}</div>
                <div style="font-size: 14px; opacity: 0.9;">Villes</div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-top: -40px; margin-bottom: 60px;">
    <!-- Search Bar -->
    <div class="card" style="margin-bottom: 40px;">
        <form action="{{ route('members.search') }}" method="GET" class="d-flex gap-2" style="align-items: center;">
            <input type="text" name="name" placeholder="Rechercher un membre par nom..." class="form-control" style="flex: 1;">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            <a href="{{ route('members.search') }}" class="btn btn-secondary">Recherche avancée</a>
        </form>
    </div>

    <!-- Members Grid -->
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
                            @if($member->village_origin)
                                <span class="badge badge-light">{{ $member->village_origin }}</span>
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
                                    @if($member->skills->count() > 3)
                                        <span style="background: #ecf0f1; color: #7f8c8d; padding: 4px 10px; border-radius: 20px; font-size: 11px;">
                                            +{{ $member->skills->count() - 3 }}
                                        </span>
                                    @endif
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
            <h3 style="color: #7f8c8d; margin-bottom: 10px;">Aucun membre trouvé</h3>
            <p style="color: #95a5a6;">Soyez le premier à rejoindre la communauté !</p>
            <a href="{{ route('register') }}" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">
                S'inscrire maintenant
            </a>
        </div>
    @endif
</div>
@endsection
