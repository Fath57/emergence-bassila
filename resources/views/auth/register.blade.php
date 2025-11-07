@extends('layouts.app')

@section('title', 'Inscription - Emergence Bassila')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1>Rejoindre la Communauté</h1>
        <p>Inscription des ressortissants de Bassila</p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <ul style="list-style: none; padding: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <h3 style="margin: 20px 0 15px; color: #2c3e50; font-size: 18px;">Informations de connexion</h3>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mot de passe *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirmer *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <h3 style="margin: 20px 0 15px; color: #2c3e50; font-size: 18px;">Informations personnelles</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Prénom *</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Nom *</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label for="birth_date">Date de naissance</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="gender">Genre</label>
                <select id="gender" name="gender">
                    <option value="">Sélectionner</option>
                    <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculin</option>
                    <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Féminin</option>
                    <option value="Autre" {{ old('gender') == 'Autre' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>

            <h3 style="margin: 20px 0 15px; color: #2c3e50; font-size: 18px;">Origine à Bassila</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="village_origin">Village d'origine</label>
                    <input type="text" id="village_origin" name="village_origin" value="{{ old('village_origin') }}">
                </div>
                <div class="form-group">
                    <label for="quartier">Quartier</label>
                    <input type="text" id="quartier" name="quartier" value="{{ old('quartier') }}">
                </div>
            </div>

            <h3 style="margin: 20px 0 15px; color: #2c3e50; font-size: 18px;">Localisation actuelle</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="current_city">Ville actuelle</label>
                    <input type="text" id="current_city" name="current_city" value="{{ old('current_city') }}">
                </div>
                <div class="form-group">
                    <label for="current_country">Pays actuel</label>
                    <input type="text" id="current_country" name="current_country" value="{{ old('current_country') }}">
                </div>
            </div>

            <h3 style="margin: 20px 0 15px; color: #2c3e50; font-size: 18px;">Informations professionnelles</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="current_profession">Profession actuelle</label>
                    <input type="text" id="current_profession" name="current_profession" value="{{ old('current_profession') }}">
                </div>
                <div class="form-group">
                    <label for="current_company">Entreprise actuelle</label>
                    <input type="text" id="current_company" name="current_company" value="{{ old('current_company') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="bio">Biographie / Présentation</label>
                <textarea id="bio" name="bio" placeholder="Parlez-nous de vous...">{{ old('bio') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">S'inscrire</button>

            <p class="text-center text-muted mt-3">
                Déjà membre ? <a href="{{ route('login') }}">Se connecter</a>
            </p>
        </form>
    </div>
</div>
@endsection
