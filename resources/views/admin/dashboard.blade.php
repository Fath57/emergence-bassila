<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Emergence Bassila</title>
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
            background: #2c3e50;
            padding: 15px 0;
            color: white;
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
            color: white;
        }

        .navbar nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar a {
            text-decoration: none;
            color: white;
            font-weight: 500;
        }

        .btn-logout {
            background: #e74c3c;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .admin-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
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
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #e74c3c;
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

        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table th {
            background: #ecf0f1;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .user-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
        }

        .user-table tr:hover {
            background: #f8f9fa;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve {
            background: #2ecc71;
            color: white;
        }

        .btn-view {
            background: #3498db;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: #f39c12;
            color: white;
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
            <h2>Admin - Emergence Bassila</h2>
            <nav>
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('admin.users.pending') }}">En attente</a>
                <a href="{{ route('admin.users.index') }}">Tous les utilisateurs</a>
                <a href="{{ route('dashboard') }}">Mon profil</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-logout">Déconnexion</button>
                </form>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="container">
        <div class="admin-header">
            <h1>Tableau de bord Administrateur</h1>
            <p>Gérez les utilisateurs et validez les inscriptions</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total utilisateurs</h3>
                <div class="number">{{ $stats['total_users'] }}</div>
            </div>
            <div class="stat-card">
                <h3>En attente</h3>
                <div class="number">{{ $stats['pending_users'] }}</div>
            </div>
            <div class="stat-card">
                <h3>Actifs</h3>
                <div class="number">{{ $stats['active_users'] }}</div>
            </div>
            <div class="stat-card">
                <h3>Suspendus</h3>
                <div class="number">{{ $stats['suspended_users'] }}</div>
            </div>
        </div>

        <!-- Pending Users -->
        <div class="card">
            <h2>Utilisateurs en attente de validation ({{ $pendingUsers->count() }})</h2>

            @if($pendingUsers->count() > 0)
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Origine</th>
                            <th>Ville actuelle</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingUsers as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->village_origin ?? 'Non renseigné' }}</td>
                                <td>{{ $user->current_city ?? 'Non renseignée' }}</td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-view">Voir</a>
                                    <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-approve" onclick="return confirm('Approuver cet utilisateur ?')">Approuver</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #7f8c8d;">Aucun utilisateur en attente de validation.</p>
            @endif
        </div>

        <div class="card">
            <h2>Actions rapides</h2>
            <a href="{{ route('admin.users.pending') }}" class="btn btn-view">Voir tous les utilisateurs en attente</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-view">Gérer tous les utilisateurs</a>
        </div>
    </div>
</body>
</html>
