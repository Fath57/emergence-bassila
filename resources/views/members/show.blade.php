@extends('layouts.modern')

@section('title', $user->full_name . ' - Emergence Bassila')

@section('content')
<!-- Header Profile -->
<div style="background: #3498db; padding: 80px 0 100px; color: white; position: relative;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <div style="width: 150px; height: 150px; border-radius: 50%; border: 6px solid white; margin: 0 auto 20px; background: white; display: flex; align-items: center; justify-content: center; font-size: 60px; font-weight: 700; color: #3498db; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
            </div>
            <h1 style="font-size: 42px; margin-bottom: 10px; font-weight: 700;">{{ $user->full_name }}</h1>
            <p style="font-size: 20px; opacity: 0.95; margin-bottom: 20px;">
                {{ $user->current_profession ?? 'Profession non renseignée' }}
            </p>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                @if($user->current_city || $user->current_country)
                    <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 25px; backdrop-filter: blur(10px);">
                        📍 {{ $user->current_city }}{{ $user->current_city && $user->current_country ? ', ' : '' }}{{ $user->current_country }}
                    </div>
                @endif
                @if($user->village_origin)
                    <div style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 25px; backdrop-filter: blur(10px);">
                        🏘️ Originaire de {{ $user->village_origin }}
                    </div>
                @endif
                @if($user->open_to_opportunities)
                    <div style="background: rgba(46, 204, 113, 0.9); padding: 8px 16px; border-radius: 25px;">
                        ✅ Ouvert aux opportunités
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="container" style="margin-top: -60px; margin-bottom: 60px;">
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">

        <!-- Main Content -->
        <div>
            <!-- About -->
            @if($user->bio)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">À propos</h2>
                    </div>
                    <p style="line-height: 1.8; color: #555;">{{ $user->bio }}</p>
                </div>
            @endif

            <!-- Experience -->
            @if($user->experiences->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Expérience professionnelle</h2>
                    </div>
                    @foreach($user->experiences as $experience)
                        <div style="padding: 20px 0; {{ !$loop->last ? 'border-bottom: 1px solid #ecf0f1;' : '' }}">
                            <h3 style="font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                {{ $experience->job_title }}
                            </h3>
                            <div style="color: #3498db; font-weight: 500; margin-bottom: 5px;">
                                {{ $experience->company_name }}
                                @if($experience->company_location)
                                    · {{ $experience->company_location }}
                                @endif
                            </div>
                            <div style="color: #7f8c8d; font-size: 14px; margin-bottom: 10px;">
                                {{ $experience->start_date->format('M Y') }} -
                                @if($experience->is_current)
                                    Actuellement
                                @else
                                    {{ $experience->end_date ? $experience->end_date->format('M Y') : 'Aujourd\'hui' }}
                                @endif
                                @if($experience->employment_type)
                                    · {{ $experience->employment_type }}
                                @endif
                            </div>
                            @if($experience->description)
                                <p style="color: #555; line-height: 1.6; margin-top: 10px;">{{ $experience->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Education -->
            @if($user->educations->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Formation</h2>
                    </div>
                    @foreach($user->educations as $education)
                        <div style="padding: 20px 0; {{ !$loop->last ? 'border-bottom: 1px solid #ecf0f1;' : '' }}">
                            <h3 style="font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                {{ $education->degree }}
                            </h3>
                            <div style="color: #3498db; font-weight: 500; margin-bottom: 5px;">
                                {{ $education->institution }}
                                @if($education->location)
                                    · {{ $education->location }}
                                @endif
                            </div>
                            <div style="color: #7f8c8d; font-size: 14px; margin-bottom: 10px;">
                                {{ $education->field_of_study }}
                            </div>
                            <div style="color: #7f8c8d; font-size: 14px;">
                                {{ $education->start_date->format('Y') }} -
                                @if($education->is_current)
                                    En cours
                                @else
                                    {{ $education->end_date ? $education->end_date->format('Y') : 'Aujourd\'hui' }}
                                @endif
                            </div>
                            @if($education->description)
                                <p style="color: #555; line-height: 1.6; margin-top: 10px;">{{ $education->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Skills -->
            @if($user->skills->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Compétences</h3>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($user->skills as $skill)
                            <span class="badge badge-primary" style="font-size: 13px; padding: 8px 14px;">
                                {{ $skill->name }}
                                @if($skill->pivot->level)
                                    <span style="opacity: 0.8; margin-left: 4px;">·</span>
                                    <span style="opacity: 0.8; font-size: 11px;">{{ $skill->pivot->level }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Contact Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contact</h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    @if($user->email)
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; background: #ecf0f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                📧
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 2px;">Email</div>
                                <a href="mailto:{{ $user->email }}" style="color: #3498db; font-weight: 500;">{{ $user->email }}</a>
                            </div>
                        </div>
                    @endif

                    @if($user->phone)
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; background: #ecf0f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                📱
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 2px;">Téléphone</div>
                                <a href="tel:{{ $user->phone }}" style="color: #3498db; font-weight: 500;">{{ $user->phone }}</a>
                            </div>
                        </div>
                    @endif

                    @if($user->current_company)
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; background: #ecf0f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                🏢
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 12px; color: #7f8c8d; margin-bottom: 2px;">Entreprise</div>
                                <div style="color: #2c3e50; font-weight: 500;">{{ $user->current_company }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Social Links -->
            @if($user->linkedin_url || $user->facebook_url || $user->twitter_url)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Réseaux sociaux</h3>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        @if($user->linkedin_url)
                            <a href="{{ $user->linkedin_url }}" target="_blank" style="flex: 1; padding: 12px; background: #0077b5; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                LinkedIn
                            </a>
                        @endif
                        @if($user->facebook_url)
                            <a href="{{ $user->facebook_url }}" target="_blank" style="flex: 1; padding: 12px; background: #1877f2; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                Facebook
                            </a>
                        @endif
                        @if($user->twitter_url)
                            <a href="{{ $user->twitter_url }}" target="_blank" style="flex: 1; padding: 12px; background: #1da1f2; color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                Twitter
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="card">
                @auth
                    @if(auth()->id() !== $user->id)
                        <button class="btn btn-primary btn-block" style="margin-bottom: 10px;">
                            💬 Envoyer un message
                        </button>
                    @else
                        <a href="{{ route('dashboard.profile.edit') }}" class="btn btn-secondary btn-block">
                            ✏️ Modifier mon profil
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">
                        Connectez-vous pour contacter
                    </a>
                @endauth

                <a href="{{ route('members.index') }}" class="btn btn-outline btn-block" style="margin-top: 10px;">
                    ← Retour à l'annuaire
                </a>
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
