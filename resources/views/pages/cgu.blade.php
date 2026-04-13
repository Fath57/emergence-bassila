@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Conditions Générales d\'Utilisation')
        ->withDescription('Conditions Générales d\'Utilisation de la plateforme Bassila Emergence.')
        ->withOgType('website');

    $version = '1.0';
    $updatedAt = '14 avril 2026';
@endphp
@extends('layouts.app')
@section('title', 'Conditions Générales d\'Utilisation')
@section('description', 'CGU de Bassila Emergence.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs :items="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'CGU', 'url' => null],
    ]" :with-json-ld="true" />

    <header class="mb-10">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Informations légales</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight" style="font-family: 'Lora', serif;">
            Conditions Générales d'Utilisation
        </h1>
        <p class="text-sm text-gray-500 mt-3">Version {{ $version }} — Dernière mise à jour : {{ $updatedAt }}</p>
    </header>

    <nav aria-label="Sommaire" class="mb-10 p-6 bg-gray-50 rounded-sm">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-3">Sommaire</p>
        <ol class="list-decimal list-inside text-sm space-y-1">
            <li><a href="#objet" class="hover:underline">Objet et éditeur</a></li>
            <li><a href="#acces" class="hover:underline">Accès au service</a></li>
            <li><a href="#compte" class="hover:underline">Inscription et compte</a></li>
            <li><a href="#contenus" class="hover:underline">Contenus publiés</a></li>
            <li><a href="#conduite" class="hover:underline">Règles de conduite</a></li>
            <li><a href="#moderation" class="hover:underline">Modération</a></li>
            <li><a href="#pi" class="hover:underline">Propriété intellectuelle du site</a></li>
            <li><a href="#responsabilite" class="hover:underline">Responsabilité</a></li>
            <li><a href="#resiliation" class="hover:underline">Durée, résiliation, suppression de compte</a></li>
            <li><a href="#modifications" class="hover:underline">Modifications des CGU</a></li>
            <li><a href="#droit" class="hover:underline">Droit applicable et juridiction</a></li>
            <li><a href="#contact" class="hover:underline">Contact</a></li>
        </ol>
    </nav>

    <div class="prose prose-lg max-w-none">
        <section id="objet">
            <h2>1. Objet et éditeur</h2>
            <p>Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») régissent l'accès à la plateforme Bassila Emergence (ci-après « la plateforme »), éditée par le collectif communautaire Bassila Emergence, basé à Bassila, Département de la Donga, République du Bénin. Contact : contact@bassila-emergence.org.</p>
            <p>Toute utilisation de la plateforme implique l'acceptation pleine et entière des présentes CGU.</p>
        </section>

        <section id="acces">
            <h2>2. Accès au service</h2>
            <p>L'accès à la consultation de la plateforme (annuaire public, blog) est gratuit et libre. La publication d'articles est soumise à invitation préalable par un administrateur. L'inscription à l'annuaire est libre pour toute personne justifiant d'un lien avec la commune de Bassila.</p>
        </section>

        <section id="compte">
            <h2>3. Inscription et compte</h2>
            <p>L'inscription est réservée aux personnes âgées de seize (16) ans au moins. L'utilisateur s'engage à fournir des informations exactes et à jour. Un seul compte par personne physique est autorisé. L'usurpation d'identité entraîne la suspension immédiate du compte.</p>
        </section>

        <section id="contenus">
            <h2>4. Contenus publiés par les membres</h2>
            <p>L'utilisateur reste propriétaire des contenus qu'il publie (profil, articles, commentaires). En les publiant, il accorde à Bassila Emergence une licence non-exclusive, gratuite et mondiale, limitée à la diffusion de ces contenus sur la plateforme et aux canaux de communication qui en dépendent (newsletter, réseaux sociaux officiels). Cette licence s'éteint à la suppression du contenu ou du compte, sous réserve des dispositions de l'article 9.</p>
        </section>

        <section id="conduite">
            <h2>5. Règles de conduite</h2>
            <p>Sont strictement interdits : les contenus à caractère haineux, discriminatoire, diffamatoire, injurieux, pornographique ou illicite ; le spam ; l'usurpation d'identité ; la publication de données personnelles d'autrui sans consentement ; toute tentative d'atteinte à la sécurité de la plateforme.</p>
        </section>

        <section id="moderation">
            <h2>6. Modération</h2>
            <p>L'équipe Bassila Emergence se réserve le droit de retirer tout contenu contraire aux CGU et de suspendre ou supprimer tout compte en cas de manquement grave ou répété. Toute décision peut être contestée par email à contact@bassila-emergence.org ; une réponse est apportée dans un délai raisonnable, au plus tard sous trente (30) jours.</p>
        </section>

        <section id="pi">
            <h2>7. Propriété intellectuelle du site</h2>
            <p>Le logo, le design, le code source, la base de données et les éléments graphiques originaux de la plateforme sont la propriété exclusive de Bassila Emergence. Toute reproduction non autorisée est interdite.</p>
        </section>

        <section id="responsabilite">
            <h2>8. Responsabilité</h2>
            <p>La plateforme est fournie « en l'état », sans garantie de disponibilité continue. Bassila Emergence ne saurait être tenue responsable des contenus publiés par ses membres, sous réserve des obligations de retrait dès notification d'un contenu manifestement illicite. La responsabilité de Bassila Emergence est limitée dans les conditions prévues par le droit béninois applicable.</p>
        </section>

        <section id="resiliation">
            <h2>9. Durée, résiliation, suppression de compte</h2>
            <p>Tout membre peut demander la suppression de son compte à tout moment depuis son espace personnel. La procédure est détaillée dans la <a href="{{ route('pages.privacy') }}#suppression">Politique de confidentialité</a> : un délai de grâce de trente (30) jours précède la purge définitive, pendant lequel la demande peut être annulée. À l'issue de ce délai, le profil est supprimé ; les articles publiés et commentaires sont anonymisés afin de préserver l'intégrité éditoriale de la plateforme, dans le cadre de l'intérêt légitime d'information prévu au RGPD article 17.3.a.</p>
        </section>

        <section id="modifications">
            <h2>10. Modifications des CGU</h2>
            <p>Bassila Emergence se réserve le droit de modifier les présentes CGU. En cas de changement substantiel, les membres seront informés par email au moins trente (30) jours avant l'entrée en vigueur des nouvelles conditions. La poursuite de l'utilisation du service après cette date vaut acceptation.</p>
        </section>

        <section id="droit">
            <h2>11. Droit applicable et juridiction</h2>
            <p>Les présentes CGU sont soumises au droit béninois. Tout litige fera l'objet d'une tentative de règlement amiable préalable par échange écrit. À défaut d'accord dans un délai de trente (30) jours, les tribunaux de Cotonou seront seuls compétents.</p>
        </section>

        <section id="contact">
            <h2>12. Contact</h2>
            <p>Pour toute question relative aux présentes CGU : contact@bassila-emergence.org.</p>
        </section>
    </div>
</div>
@endsection
