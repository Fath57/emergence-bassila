<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Emergence Bassila')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/modern.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="{{ route('landing') }}" class="navbar-brand">Emergence Bassila</a>
            <ul class="navbar-nav">
                <li><a href="{{ route('landing') }}" class="{{ request()->routeIs('landing') ? 'active' : '' }}">Accueil</a></li>
                <li><a href="{{ route('members.index') }}" class="{{ request()->routeIs('members.index') ? 'active' : '' }}">Annuaire</a></li>
                <li><a href="{{ route('opportunities.index') }}" class="{{ request()->routeIs('opportunities.*') ? 'active' : '' }}">Opportunités</a></li>
                <li><a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.*') ? 'active' : '' }}">Actualités</a></li>

                @auth
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'moderator')
                        <li><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    @endif
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Déconnexion</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Connexion</a></li>
                    <li><a href="{{ route('register') }}" class="btn btn-primary btn-sm">S'inscrire</a></li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer style="background: #2c3e50; color: white; padding: 40px 0; margin-top: 60px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                <div>
                    <h3 style="margin-bottom: 15px;">Emergence Bassila</h3>
                    <p style="color: #bdc3c7;">Plateforme communautaire des ressortissants de Bassila</p>
                </div>
                <div>
                    <h4 style="margin-bottom: 15px;">Liens rapides</h4>
                    <ul style="list-style: none;">
                        <li style="margin-bottom: 8px;"><a href="{{ route('members.index') }}" style="color: #ecf0f1;">Annuaire</a></li>
                        <li style="margin-bottom: 8px;"><a href="{{ route('opportunities.index') }}" style="color: #ecf0f1;">Opportunités</a></li>
                        <li style="margin-bottom: 8px;"><a href="{{ route('news.index') }}" style="color: #ecf0f1;">Actualités</a></li>
                        <li style="margin-bottom: 8px;"><a href="{{ route('register') }}" style="color: #ecf0f1;">S'inscrire</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="margin-bottom: 15px;">Contact</h4>
                    <p style="color: #bdc3c7;">
                        Email: contact@emergence-bassila.com<br>
                        Bassila, Donga, Bénin
                    </p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #34495e;">
                <p style="color: #95a5a6;">&copy; 2024 Emergence Bassila. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
