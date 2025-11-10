<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergence Bassila - Plateforme Communautaire</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #2c3e50;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Navigation */
        .navbar {
            background: #ffffff;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #27ae60;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-menu a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-menu a:hover {
            color: #27ae60;
        }

        .nav-cta {
            background: #27ae60;
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .nav-cta:hover {
            background: #229954;
        }

        /* Hero Section */
        .hero {
            background: url('https://images.unsplash.com/photo-1542222024-c1b012a1e268?w=1600&q=80') center/cover;
            padding: 100px 0;
            position: relative;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(44, 62, 80, 0.75);
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 48px;
            line-height: 1.2;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .hero-title .highlight {
            color: #27ae60;
        }

        .hero-description {
            font-size: 18px;
            color: #ecf0f1;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #27ae60;
            color: white;
        }

        .btn-primary:hover {
            background: #229954;
        }

        .btn-secondary {
            background: white;
            color: #27ae60;
            border: 2px solid #27ae60;
        }

        .btn-secondary:hover {
            background: #27ae60;
            color: white;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 32px;
            font-weight: 700;
            color: #27ae60;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #ecf0f1;
        }

        .hero-image {
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 36px;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .section-subtitle {
            font-size: 18px;
            color: #7f8c8d;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .feature-card {
            padding: 30px;
            border-radius: 10px;
            background: #ecf0f1;
            text-align: center;
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 50px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .feature-card p {
            color: #7f8c8d;
        }

        /* About Section */
        .about {
            padding: 80px 0;
            background: #ecf0f1;
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .about-image {
            height: 350px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-text h2 {
            margin-bottom: 20px;
        }

        .about-text p {
            color: #7f8c8d;
            margin-bottom: 25px;
            line-height: 1.8;
        }

        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 30px;
        }

        .about-feature {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .check-icon {
            background: #27ae60;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: #27ae60;
            text-align: center;
            color: white;
        }

        .cta h2 {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .cta p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .btn-white {
            background: white;
            color: #27ae60;
        }

        .btn-white:hover {
            background: #ecf0f1;
        }

        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            margin-bottom: 15px;
            color: #27ae60;
        }

        .footer-section h4 {
            margin-bottom: 15px;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: #27ae60;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #34495e;
            color: #bdc3c7;
        }

        @media (max-width: 768px) {
            .hero-content,
            .about-content {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 32px;
            }

            .nav-menu {
                flex-direction: column;
                gap: 15px;
            }

            .hero-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌍 Emergence Bassila</div>
            <ul class="nav-menu">
                <li><a href="#home">Accueil</a></li>
                <li><a href="{{ route('members.index') }}">Annuaire</a></li>
                <li><a href="{{ route('opportunities.index') }}">Opportunités</a></li>
                <li><a href="{{ route('news.index') }}">Actualités</a></li>
                @auth
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-cta" style="background: #e74c3c;">Déconnexion</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Connexion</a></li>
                    <li><a href="{{ route('register') }}" class="nav-cta" style="text-decoration: none; color: white; display: inline-block;">S'inscrire</a></li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        Restez connectés avec
                        <span class="highlight">la communauté de Bassila</span>
                    </h1>
                    <p class="hero-description">
                        Plateforme communautaire qui rassemble tous les ressortissants de Bassila à travers le monde.
                        Retrouvez vos proches, partagez votre parcours, découvrez des opportunités et contribuez au développement de notre région.
                    </p>
                    <div class="hero-buttons">
                        <a href="{{ route('register') }}" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Rejoindre la Communauté</a>
                        <a href="{{ route('members.index') }}" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">Voir l'Annuaire</a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <span class="stat-number">👥</span>
                            <span class="stat-label">Membres connectés</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">🌍</span>
                            <span class="stat-label">Plusieurs pays</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">💼</span>
                            <span class="stat-label">Opportunités partagées</span>
                        </div>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800&q=80"
                         alt="Communauté de Bassila"
                         loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Que pouvez-vous faire sur la plateforme ?</h2>
                <p class="section-subtitle">Des fonctionnalités pour rester connecté avec votre communauté</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Annuaire des Membres</h3>
                    <p>Retrouvez tous les ressortissants de Bassila où qu'ils soient dans le monde. Recherchez par village, profession ou localisation actuelle.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💼</div>
                    <h3>Opportunités d'Emploi</h3>
                    <p>Découvrez et partagez des offres d'emploi, de stage, de collaboration et de bénévolat au sein de la communauté.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📰</div>
                    <h3>Actualités & Événements</h3>
                    <p>Restez informé des nouvelles de Bassila et des événements organisés par la diaspora partout dans le monde.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Recherche de Profils</h3>
                    <p>Trouvez rapidement des personnes avec des compétences spécifiques pour vos projets ou besoins.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Réseau Professionnel</h3>
                    <p>Créez des synergies professionnelles et des partenariats avec d'autres membres de la communauté.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏡</div>
                    <h3>Contribuer au Développement</h3>
                    <p>Participez activement aux initiatives de développement de Bassila depuis n'importe où.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1509099836639-18ba1795216d?w=800&q=80"
                         alt="Développement communautaire en Afrique"
                         loading="lazy">
                </div>
                <div class="about-text">
                    <h2 class="section-title">À propos d'Emergence Bassila</h2>
                    <p>
                        Emergence Bassila est une plateforme communautaire créée pour maintenir et renforcer les liens entre tous les ressortissants de Bassila, qu'ils vivent au Bénin ou à l'étranger.
                    </p>
                    <p>
                        Notre mission est de faciliter la communication, l'entraide et la collaboration entre les membres de notre communauté, tout en contribuant au développement socio-économique de notre région d'origine.
                    </p>
                    <div class="about-features">
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Connexion mondiale</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Entraide communautaire</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Partage d'opportunités</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Développement local</span>
                        </div>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Rejoindre Maintenant</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Rejoignez la communauté Emergence Bassila</h2>
            <p>Inscrivez-vous gratuitement et restez connecté avec vos frères et sœurs de Bassila à travers le monde</p>
            <a href="{{ route('register') }}" class="btn btn-white" style="text-decoration: none; display: inline-block;">S'inscrire Gratuitement</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Emergence Bassila</h3>
                    <p style="color: #bdc3c7;">Plateforme communautaire des ressortissants de Bassila</p>
                </div>
                <div class="footer-section">
                    <h4>Liens Rapides</h4>
                    <ul>
                        <li><a href="{{ route('members.index') }}">Annuaire</a></li>
                        <li><a href="{{ route('opportunities.index') }}">Opportunités</a></li>
                        <li><a href="{{ route('news.index') }}">Actualités</a></li>
                        <li><a href="{{ route('register') }}">S'inscrire</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Communauté</h4>
                    <ul>
                        <li><a href="#">À Propos</a></li>
                        <li><a href="#">Événements</a></li>
                        <li><a href="#">Projets</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Email: contact@emergence-bassila.com</li>
                        <li>Bassila, Donga, Bénin</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Emergence Bassila. Plateforme communautaire - Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>
