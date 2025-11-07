<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration - Emergence Bassila')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/modern.css') }}">
    <style>
        .admin-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: linear-gradient(180deg, #2c3e50, #34495e);
            color: white;
            padding: 20px;
        }

        .admin-sidebar-logo {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
        }

        .admin-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .admin-nav li {
            margin-bottom: 5px;
        }

        .admin-nav a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .admin-main {
            background: #f5f7fa;
            padding: 30px;
            overflow-y: auto;
        }

        .admin-header {
            background: white;
            padding: 20px 30px;
            margin: -30px -30px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }

        .stat-card.warning {
            border-left-color: #f39c12;
        }

        .stat-card.success {
            border-left-color: #27ae60;
        }

        .stat-card.danger {
            border-left-color: #e74c3c;
        }

        .stat-card.info {
            border-left-color: #3498db;
        }

        .stat-label {
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                display: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-sidebar-logo">
                🛡️ Administration
            </div>

            <ul class="admin-nav">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        👥 Utilisateurs
                        @if($pendingUsersCount ?? 0 > 0)
                            <span style="margin-left: auto; background: #e74c3c; padding: 2px 8px; border-radius: 10px; font-size: 11px;">{{ $pendingUsersCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                        📰 Actualités
                        @if($pendingNewsCount ?? 0 > 0)
                            <span style="margin-left: auto; background: #e74c3c; padding: 2px 8px; border-radius: 10px; font-size: 11px;">{{ $pendingNewsCount }}</span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.opportunities.index') }}" class="{{ request()->routeIs('admin.opportunities.*') ? 'active' : '' }}">
                        💼 Opportunités
                        @if($pendingOpportunitiesCount ?? 0 > 0)
                            <span style="margin-left: auto; background: #e74c3c; padding: 2px 8px; border-radius: 10px; font-size: 11px;">{{ $pendingOpportunitiesCount }}</span>
                        @endif
                    </a>
                </li>
                <li style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="{{ route('landing') }}">
                        🏠 Retour au site
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="width: 100%; background: none; border: none; cursor: pointer; text-align: left;">
                            <a style="cursor: pointer;">
                                🚪 Déconnexion
                            </a>
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Header -->
            <div class="admin-header">
                <div>
                    <h1 style="font-size: 24px; color: #2c3e50; margin: 0;">@yield('page-title', 'Dashboard')</h1>
                    <p style="color: #7f8c8d; margin: 5px 0 0;">@yield('page-subtitle', 'Bienvenue dans l\'espace d\'administration')</p>
                </div>
                <div>
                    <span style="color: #7f8c8d;">👤 {{ auth()->user()->full_name }}</span>
                    <span style="margin-left: 10px; background: #3498db; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                        {{ auth()->user()->role === 'admin' ? 'Administrateur' : 'Modérateur' }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
