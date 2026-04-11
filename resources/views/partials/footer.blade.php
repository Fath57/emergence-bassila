<footer class="bg-[#0A1628] text-gray-400 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-10 pb-10 border-b border-white/10">

            {{-- Brand --}}
            <div class="md:col-span-2">
                <a href="{{ route('home') }}" class="inline-block mb-4">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Bassila Émergence"
                         width="180" height="44"
                         class="h-11 w-auto">
                </a>
                <p class="text-sm leading-relaxed max-w-xs">
                    La plateforme de networking des Bassilais à travers le monde. Connectés, engagés, inspirants.
                </p>
            </div>

            {{-- Plateforme --}}
            <div>
                <h4 class="text-white text-xs font-semibold uppercase tracking-widest mb-4">Plateforme</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Accueil</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition">Blog</a></li>
                    <li><a href="{{ route('directory.index') }}" class="hover:text-white transition">Annuaire</a></li>
                </ul>
            </div>

            {{-- À propos --}}
            <div>
                <h4 class="text-white text-xs font-semibold uppercase tracking-widest mb-4">À propos</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('pages.about-us') }}" class="hover:text-white transition">Qui sommes-nous</a></li>
                    <li><a href="{{ route('pages.about-bassila') }}" class="hover:text-white transition">À propos de Bassila</a></li>
                </ul>
            </div>

            {{-- Compte --}}
            <div>
                <h4 class="text-white text-xs font-semibold uppercase tracking-widest mb-4">Compte</h4>
                <ul class="space-y-2 text-sm">
                    @auth
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-white transition">Mon profil</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="hover:text-white transition text-sm text-left">Déconnexion</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Connexion</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">S'inscrire</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-6 text-xs">
            <p>© {{ date('Y') }} Bassila Émergence. Tous droits réservés.</p>
            <div class="flex gap-5">
                <a href="#" class="hover:text-white transition">Confidentialité</a>
                <a href="#" class="hover:text-white transition">Conditions d'utilisation</a>
            </div>
        </div>
    </div>
</footer>
