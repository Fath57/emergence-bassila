<header class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center shrink-0">
                <img src="{{ asset('images/logo-trans.png') }}"
                     alt="Bassila Émergence"
                     class="h-9 w-auto">
            </a>

            {{-- Nav links --}}
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('home') }}"
                   class="text-sm font-medium {{ request()->routeIs('home') ? 'text-[#0066CC]' : 'text-gray-600 hover:text-[#111827]' }} transition">
                    Accueil
                </a>
                <a href="{{ route('blog.index') }}"
                   class="text-sm font-medium {{ request()->routeIs('blog.*') ? 'text-[#0066CC]' : 'text-gray-600 hover:text-[#111827]' }} transition">
                    Blog
                </a>
                <a href="{{ route('directory.index') }}"
                   class="text-sm font-medium {{ request()->routeIs('directory.*') ? 'text-[#0066CC]' : 'text-gray-600 hover:text-[#111827]' }} transition">
                    Annuaire
                </a>
            </nav>

            {{-- Auth --}}
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('profile.edit') }}"
                       class="text-sm font-medium text-gray-600 hover:text-[#111827] transition hidden sm:block">
                        Mon profil
                    </a>
                    <a href="{{ route('blog.mine') }}"
                       class="text-sm font-medium {{ request()->routeIs('blog.mine') ? 'text-[#0066CC]' : 'text-gray-600 hover:text-[#111827]' }} transition hidden sm:block">
                        Mes articles
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-[#DC143C] transition">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="text-sm font-medium text-gray-600 hover:text-[#111827] transition hidden sm:block">
                        Connexion
                    </a>
                    @if (setting('site.registration_open', true))
                        <a href="{{ route('register') }}"
                           class="text-sm font-semibold bg-[#0066CC] hover:bg-blue-800 text-white px-4 py-2 transition">
                            Créer un profil
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</header>
