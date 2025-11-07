<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergence Bassila - Innovation & Excellence</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <span class="logo">Emergence Bassila</span>
            </div>
            <ul class="nav-menu">
                <li><a href="#home">Accueil</a></li>
                <li><a href="{{ route('members.index') }}">Annuaire</a></li>
                <li><a href="{{ route('members.search') }}">Rechercher</a></li>
                <li><a href="#about">À Propos</a></li>
                @auth
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                @else
                    <li><a href="{{ route('login') }}">Connexion</a></li>
                @endauth
            </ul>
            @auth
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-cta" style="background: #e74c3c;">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('register') }}" class="nav-cta" style="text-decoration: none; color: white;">S'inscrire</a>
            @endauth
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Plateforme Communautaire des
                    <span class="gradient-text">Ressortissants de Bassila</span>
                </h1>
                <p class="hero-description">
                    Connectez-vous avec les membres de la diaspora de Bassila à travers le monde.
                    Partagez votre parcours, trouvez des opportunités et restez en contact avec votre communauté.
                </p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary" style="text-decoration: none; display: inline-block;">Rejoindre la Communauté</a>
                    <a href="{{ route('members.index') }}" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">Voir l'Annuaire</a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">250+</span>
                        <span class="stat-label">Projets Réalisés</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">98%</span>
                        <span class="stat-label">Satisfaction Client</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Experts</span>
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
                <h2 class="section-title">Nos Services</h2>
                <p class="section-subtitle">Des solutions complètes pour tous vos besoins digitaux</p>
            </div>
            <div class="features-grid">
                <div class="feature-card" style="--card-color: #3498db">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <h3>Développement Web</h3>
                    <p>Création de sites web modernes et performants avec les dernières technologies</p>
                </div>
                <div class="feature-card" style="--card-color: #2ecc71">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2"></rect>
                            <line x1="12" y1="18" x2="12" y2="18"></line>
                        </svg>
                    </div>
                    <h3>Applications Mobile</h3>
                    <p>Applications natives et hybrides pour iOS et Android</p>
                </div>
                <div class="feature-card" style="--card-color: #e67e22">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <h3>Solutions Cloud</h3>
                    <p>Infrastructure cloud scalable et sécurisée pour votre entreprise</p>
                </div>
                <div class="feature-card" style="--card-color: #9b59b6">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                    <h3>Intelligence Artificielle</h3>
                    <p>Intégration d'IA et Machine Learning dans vos processus</p>
                </div>
                <div class="feature-card" style="--card-color: #e74c3c">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3>Cybersécurité</h3>
                    <p>Protection avancée de vos données et infrastructures</p>
                </div>
                <div class="feature-card" style="--card-color: #1abc9c">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h3>Consulting Digital</h3>
                    <p>Accompagnement stratégique pour votre transformation digitale</p>
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
                    <h2 class="section-title">Qui Sommes-Nous ?</h2>
                    <p>
                        Emergence Bassila est une entreprise technologique innovante qui accompagne
                        les organisations dans leur transformation digitale. Avec plus de 10 ans
                        d'expérience, nous combinons expertise technique et vision stratégique.
                    </p>
                    <div class="about-features">
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Équipe d'experts certifiés</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Méthodologies agiles</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Support 24/7</span>
                        </div>
                        <div class="about-feature">
                            <div class="check-icon">✓</div>
                            <span>Innovation continue</span>
                        </div>
                    </div>
                    <button class="btn btn-primary">En Savoir Plus</button>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à Démarrer Votre Projet ?</h2>
                <p>Contactez-nous dès aujourd'hui pour discuter de vos besoins</p>
                <button class="btn btn-white">Demander un Devis Gratuit</button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Emergence Bassila</h3>
                    <p>Votre partenaire technologique de confiance</p>
                </div>
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="#">Développement Web</a></li>
                        <li><a href="#">Applications Mobile</a></li>
                        <li><a href="#">Cloud Computing</a></li>
                        <li><a href="#">Consulting</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Entreprise</h4>
                    <ul>
                        <li><a href="#">À Propos</a></li>
                        <li><a href="#">Carrières</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Email: contact@emergence-bassila.com</li>
                        <li>Tél: +229 XX XX XX XX</li>
                        <li>Bassila, Bénin</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Emergence Bassila. Tous droits réservés.</p>
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
