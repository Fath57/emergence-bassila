@extends('layouts.app')

@section('title', 'Inscription - Emergence Bassila')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8f9fa; padding: 40px 20px;">
    <div style="max-width: 500px; width: 100%; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 40px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="font-size: 28px; color: #2c3e50; margin-bottom: 10px;">Rejoindre la Communauté</h1>
            <p style="color: #7f8c8d; font-size: 15px;">Créez votre compte en quelques étapes</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="first_name" class="required">Prénom</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="{{ old('first_name') }}" required autofocus>
                    @error('first_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="last_name" class="required">Nom</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    @error('last_name')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="required">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+229 XX XX XX XX">
                @error('phone')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="village_origin">Village d'origine à Bassila</label>
                <input type="text" id="village_origin" name="village_origin" class="form-control" value="{{ old('village_origin') }}" placeholder="Ex: Bassila centre, Pénésoulou...">
                @error('village_origin')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="current_city">Ville actuelle</label>
                    <input type="text" id="current_city" name="current_city" class="form-control" value="{{ old('current_city') }}" placeholder="Ex: Cotonou">
                    @error('current_city')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="current_country">Pays actuel</label>
                    <input type="text" id="current_country" name="current_country" class="form-control" value="{{ old('current_country') }}" placeholder="Ex: Bénin">
                    @error('current_country')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row cols-2">
                <div class="form-group">
                    <label for="password" class="required">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="required">Confirmer</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 13px; color: #7f8c8d;">
                <strong style="color: #2c3e50;">Note:</strong> Votre compte sera vérifié par un administrateur avant activation. Vous pourrez compléter votre profil après validation.
            </div>

            <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>

            <p style="text-align: center; margin-top: 20px; color: #7f8c8d; font-size: 14px;">
                Déjà membre ? <a href="{{ route('login') }}" style="color: #27ae60; font-weight: 600; text-decoration: none;">Se connecter</a>
            </p>
        </form>
    </div>
</div>
@endsection
