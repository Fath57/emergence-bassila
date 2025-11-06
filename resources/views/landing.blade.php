<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bassila Connect - Plateforme Communautaire</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <span class="logo">Bassila Connect</span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Accueil</a></li>
                <li><a href="#features">Fonctionnalités</a></li>
                <li><a href="#about">À Propos</a></li>
            </ul>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-cta">Mon Profil</a>
            @else
                <a href="{{ route('login') }}" class="nav-cta">Connexion</a>
            @endauth
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Plateforme Communautaire
                    <span class="gradient-text">Bassila Connect</span>
                </h1>
                <p class="hero-description">
                    Restez connectés avec les ressortissants de Bassila. Partagez vos compétences,
                    trouvez des opportunités et maintenez le lien avec notre belle commune du Donga.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary">Rejoindre la Communauté</a>
                    <a href="#features" class="btn btn-secondary">En Savoir Plus</a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Membres Inscrits</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">25+</span>
                        <span class="stat-label">Pays Représentés</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">100+</span>
                        <span class="stat-label">Compétences Diverses</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card card-1"></div>
                <div class="floating-card card-2"></div>
                <div class="floating-card card-3"></div>
                <div class="hero-illustration"></div>
            </div>
        </div>
        <div class="wave-divider">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
                <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
                <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nos Fonctionnalités</h2>
                <p class="section-subtitle">Une plateforme complète pour rester connectés</p>
            </div>
            <div class="features-grid">
                <div class="feature-card" style="--card-color: #3498db">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Annuaire des Membres</h3>
                    <p>Retrouvez facilement les ressortissants de Bassila partout dans le monde</p>
                </div>
                <div class="feature-card" style="--card-color: #2ecc71">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                    <h3>Profils Détaillés</h3>
                    <p>Partagez votre parcours, compétences et expériences professionnelles</p>
                </div>
                <div class="feature-card" style="--card-color: #e67e22">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <h3>Réseau International</h3>
                    <p>Connectez-vous avec la diaspora bassiloise à travers le monde</p>
                </div>
                <div class="feature-card" style="--card-color: #9b59b6">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h3>Opportunités Professionnelles</h3>
                    <p>Dénicher les talents pour vos projets ou trouvez des opportunités</p>
                </div>
                <div class="feature-card" style="--card-color: #e74c3c">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <path d="M21 15l-5-5L5 21"></path>
                        </svg>
                    </div>
                    <h3>Galerie Communautaire</h3>
                    <p>Partagez photos et moments de Bassila et des événements communautaires</p>
                </div>
                <div class="feature-card" style="--card-color: #1abc9c">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <h3>Actualités</h3>
                    <p>Restez informés des nouvelles de Bassila et des événements communautaires</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-image">
                    <div class="image-wrapper">
                        <div class="floating-shape shape-1"></div>
                        <div class="floating-shape shape-2"></div>
                        <div class="floating-shape shape-3"></div>
                    </div>
                </div>
                <div class="about-text">
                    <h2 class="section-title">À Propos de Bassila Connect</h2>
                    <p>
                        Bassila Connect est une plateforme communautaire dédiée aux ressortissants
                        de Bassila, commune du département du Donga au Bénin. Notre mission est de
                        maintenir et renforcer les liens entre les fils et filles de Bassila dispersés
                        à travers le monde.
                    </p>
                    <div class="about-features">
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Réseau communautaire fort</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Plateforme de recrutement</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Partage de compétences</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Validation par administration</span>
                        </div>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-primary">Rejoindre Maintenant</a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à Rejoindre la Communauté ?</h2>
                <p>Inscrivez-vous dès aujourd'hui et connectez-vous avec les ressortissants de Bassila</p>
                <a href="{{ route('register') }}" class="btn btn-white">Créer Mon Compte</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Bassila Connect</h3>
                    <p>La plateforme communautaire des ressortissants de Bassila</p>
                </div>
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="#home">Accueil</a></li>
                        <li><a href="#features">Fonctionnalités</a></li>
                        <li><a href="#about">À Propos</a></li>
                        <li><a href="{{ route('register') }}">S'inscrire</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Communauté</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Se Connecter</a></li>
                        <li><a href="#">Galerie</a></li>
                        <li><a href="#">Actualités</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Email: contact@bassilaconnect.com</li>
                        <li>Bassila, Donga, Bénin</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Bassila Connect. Tous droits réservés.</p>
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
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = 'none';
            }
        });

        // Animate on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.feature-card, .about-content > *').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
