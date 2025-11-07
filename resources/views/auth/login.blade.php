@extends('layouts.app')

@section('title', 'Connexion - Emergence Bassila')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1>Bienvenue !</h1>
        <p>Connectez-vous à votre compte</p>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
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

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center;">
                <input type="checkbox" id="remember" name="remember" style="width: auto; margin-right: 8px;">
                <label for="remember" style="margin: 0; font-weight: normal;">Se souvenir de moi</label>
            </div>

            <button type="submit" class="btn btn-primary">Se connecter</button>

            <p class="text-center text-muted mt-3">
                Pas encore membre ? <a href="{{ route('register') }}">S'inscrire</a>
            </p>
        </form>
    </div>
</div>
@endsection
