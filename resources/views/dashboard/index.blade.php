<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Emergence Bassila</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            color: #3498db;
        }

        .navbar nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #3498db;
        }

        .btn-logout {
            background: #e74c3c;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .welcome h1 {
            margin-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #3498db;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .btn-primary {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .profile-info div {
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .profile-info strong {
            display: block;
            color: #7f8c8d;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="container">
            <h2>Emergence Bassila</h2>
            <nav>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('dashboard.profile') }}">Mon Profil</a>
                <a href="{{ route('opportunities.my') }}">Mes Opportunités</a>
                <a href="{{ route('news.my') }}">Mes Actualités</a>
                <a href="{{ route('landing') }}">Accueil</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Déconnexion</button>
                </form>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="welcome">
            <h1>Bienvenue, {{ $user->first_name }} !</h1>
            <p>Content de vous revoir sur la plateforme communautaire de Bassila</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Messages</h3>
                <div class="number">{{ $stats['total_messages'] }}</div>
            </div>
            <div class="stat-card">
                <h3>Messages non lus</h3>
                <div class="number">{{ $stats['unread_messages'] }}</div>
            </div>
            <div class="stat-card">
                <h3>Opportunités postées</h3>
                <div class="number">{{ $stats['opportunities_posted'] }}</div>
            </div>
            <div class="stat-card">
                <h3>Actualités postées</h3>
                <div class="number">{{ $stats['news_posted'] }}</div>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="card">
            <h2>Résumé du profil</h2>
            <div class="profile-info">
                <div>
                    <strong>Nom complet</strong>
                    {{ $user->full_name }}
                </div>
                <div>
                    <strong>Email</strong>
                    {{ $user->email }}
                </div>
                <div>
                    <strong>Ville actuelle</strong>
                    {{ $user->current_city ?? 'Non renseignée' }}
                </div>
                <div>
                    <strong>Pays actuel</strong>
                    {{ $user->current_country ?? 'Non renseigné' }}
                </div>
                <div>
                    <strong>Profession</strong>
                    {{ $user->current_profession ?? 'Non renseignée' }}
                </div>
                <div>
                    <strong>Compétences</strong>
                    {{ $user->skills->count() }} compétence(s)
                </div>
            </div>
            <br>
            <a href="{{ route('dashboard.profile.edit') }}" class="btn-primary">Modifier mon profil</a>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h2>Actions rapides</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                <a href="{{ route('members.search') }}" class="btn-primary" style="text-align: center;">
                    🔍 Rechercher des membres
                </a>
                <a href="{{ route('opportunities.create') }}" class="btn-primary" style="text-align: center;">
                    💼 Publier une opportunité
                </a>
                <a href="{{ route('news.create') }}" class="btn-primary" style="text-align: center;">
                    ✍️ Publier une actualité
                </a>
                <a href="{{ route('opportunities.my') }}" class="btn-primary" style="text-align: center;">
                    💼 Mes opportunités
                </a>
                <a href="{{ route('news.my') }}" class="btn-primary" style="text-align: center;">
                    📰 Mes actualités
                </a>
                <a href="{{ route('members.index') }}" class="btn-primary" style="text-align: center;">
                    👥 Annuaire
                </a>
            </div>
            <p style="margin-top: 20px; color: #7f8c8d; font-size: 14px;">
                <strong>À venir :</strong> Messagerie interne, galerie photos
            </p>
        </div>
    </div>
</body>
</html>
