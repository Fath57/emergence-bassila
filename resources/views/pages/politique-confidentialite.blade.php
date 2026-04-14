@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Politique de confidentialité')
        ->withDescription('Politique de confidentialité de la plateforme Bassila Emergence — vos données, vos droits.')
        ->withOgType('website')
        ->withNoindex();

    $version = '1.0';
    $updatedAt = '14 avril 2026';
@endphp
@extends('layouts.app')
@section('title', 'Politique de confidentialité')
@section('description', 'Politique de confidentialité de Bassila Emergence.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs :items="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'Politique de confidentialité', 'url' => null],
    ]" :with-json-ld="true" />

    <header class="mb-10">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Informations légales</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight" style="font-family: 'Lora', serif;">
            Politique de confidentialité
        </h1>
        <p class="text-sm text-gray-500 mt-3">Version {{ $version }} — Dernière mise à jour : {{ $updatedAt }}</p>
    </header>

    <nav aria-label="Sommaire" class="mb-10 p-6 bg-gray-50 rounded-sm">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-3">Sommaire</p>
        <ol class="list-decimal list-inside text-sm space-y-1">
            <li><a href="#responsable" class="hover:underline">Responsable de traitement</a></li>
            <li><a href="#donnees" class="hover:underline">Données collectées</a></li>
            <li><a href="#finalites" class="hover:underline">Finalités et bases légales</a></li>
            <li><a href="#destinataires" class="hover:underline">Destinataires</a></li>
            <li><a href="#transferts" class="hover:underline">Transferts hors UE / hors Bénin</a></li>
            <li><a href="#conservation" class="hover:underline">Durées de conservation</a></li>
            <li><a href="#droits" class="hover:underline">Droits</a></li>
            <li><a href="#suppression" class="hover:underline">Suppression de compte</a></li>
            <li><a href="#cookies" class="hover:underline">Cookies et traceurs</a></li>
            <li><a href="#securite" class="hover:underline">Sécurité</a></li>
            <li><a href="#reclamations" class="hover:underline">Réclamations</a></li>
            <li><a href="#modifications" class="hover:underline">Modifications de la politique</a></li>
            <li><a href="#contact" class="hover:underline">Contact</a></li>
        </ol>
    </nav>

    <div class="prose prose-lg max-w-none">

        <section id="responsable">
            <h2>1. Responsable de traitement</h2>
            <p>Le responsable du traitement des données personnelles collectées via la plateforme est le collectif communautaire <strong>Bassila Emergence</strong>, dont l'adresse est :</p>
            <p>Bassila, Département de la Donga, République du Bénin</p>
            <p>Contact : <a href="mailto:contact@bassila-emergence.org">contact@bassila-emergence.org</a></p>
        </section>

        <section id="donnees">
            <h2>2. Données collectées</h2>
            <p>Le tableau ci-dessous récapitule les catégories de données traitées par la plateforme.</p>
            <table>
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Exemples</th>
                        <th>Source</th>
                        <th>Obligatoire</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Identification</td>
                        <td>Prénom, nom, adresse email, mot de passe (haché)</td>
                        <td>Formulaire d'inscription</td>
                        <td>Oui</td>
                    </tr>
                    <tr>
                        <td>Profil public</td>
                        <td>Biographie, photo, profession, secteur d'activité, compétences, pays, ville, village</td>
                        <td>Formulaire de profil</td>
                        <td>Partiellement</td>
                    </tr>
                    <tr>
                        <td>Contact</td>
                        <td>Numéro de téléphone, identifiant WhatsApp (visibilité optionnelle choisie par le membre)</td>
                        <td>Formulaire de profil</td>
                        <td>Non</td>
                    </tr>
                    <tr>
                        <td>Technique</td>
                        <td>Adresse IP, user-agent du navigateur, journaux d'activité</td>
                        <td>Collecte automatique</td>
                        <td>Oui (sécurité)</td>
                    </tr>
                    <tr>
                        <td>Newsletter</td>
                        <td>Adresse email, statut d'abonnement, ouvertures via pixel de suivi</td>
                        <td>Formulaire d'abonnement (double opt-in)</td>
                        <td>Non</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section id="finalites">
            <h2>3. Finalités et bases légales</h2>
            <p>Conformément à l'article 6 du Règlement général sur la protection des données (RGPD) et à l'article 388 du code numérique du Bénin, les traitements reposent sur les bases légales suivantes :</p>
            <ul>
                <li><strong>Gestion de compte</strong> — exécution du contrat (inscription et accès au service).</li>
                <li><strong>Annuaire public</strong> — consentement granulaire exprimé lors de la publication du profil ; le membre choisit les informations rendues visibles.</li>
                <li><strong>Newsletter</strong> — consentement obtenu par double opt-in (inscription + confirmation par lien email).</li>
                <li><strong>Modération et sécurité</strong> — intérêt légitime de Bassila Emergence à garantir l'intégrité de la plateforme et la protection des membres.</li>
                <li><strong>Obligations légales</strong> — réponse aux réquisitions judiciaires ou administratives légalement fondées.</li>
            </ul>
        </section>

        <section id="destinataires">
            <h2>4. Destinataires</h2>
            <p>Les données sont accessibles :</p>
            <ul>
                <li>Au personnel et aux bénévoles de Bassila Emergence habilités, liés par une obligation de confidentialité.</li>
                <li>Aux sous-traitants techniques strictement nécessaires à l'exploitation du service : hébergeur de la plateforme et service d'envoi d'emails transactionnels.</li>
            </ul>
            <p>Les données ne font l'objet d'aucune revente ni d'aucun transfert à des tiers à des fins commerciales.</p>
        </section>

        <section id="transferts">
            <h2>5. Transferts hors UE / hors Bénin</h2>
            <p>Dans le cas où l'hébergement ou l'envoi d'emails transactionnels serait assuré par un prestataire établi hors de l'Union européenne ou hors du Bénin, Bassila Emergence s'assure que ce transfert est encadré par des garanties appropriées, notamment les clauses contractuelles types adoptées par la Commission européenne ou des mécanismes d'équivalence reconnus, conformément au RGPD et à la réglementation béninoise applicable.</p>
        </section>

        <section id="conservation">
            <h2>6. Durées de conservation</h2>
            <ul>
                <li><strong>Compte actif</strong> : les données sont conservées pendant toute la durée d'activité du compte.</li>
                <li><strong>Compte inactif depuis plus de 3 ans</strong> : un email d'alerte est envoyé au membre ; en l'absence de réponse dans un délai raisonnable, le compte est anonymisé.</li>
                <li><strong>Journaux d'audit (logs)</strong> : conservés pendant une durée maximale d'un (1) an à des fins de sécurité.</li>
                <li><strong>Newsletter</strong> : les données d'abonnement sont conservées jusqu'à la désinscription effective du membre.</li>
            </ul>
        </section>

        <section id="droits">
            <h2>7. Droits</h2>
            <p>Conformément au RGPD et à la réglementation béninoise, vous disposez des droits suivants sur vos données personnelles :</p>
            <ul>
                <li><strong>Droit d'accès</strong> : obtenir une copie des données vous concernant.</li>
                <li><strong>Droit de rectification</strong> : corriger des données inexactes ou incomplètes.</li>
                <li><strong>Droit à l'effacement</strong> (« droit à l'oubli ») : demander la suppression de vos données dans les conditions prévues par la loi.</li>
                <li><strong>Droit à la limitation du traitement</strong> : demander le gel temporaire du traitement de vos données.</li>
                <li><strong>Droit d'opposition</strong> : vous opposer à un traitement fondé sur l'intérêt légitime.</li>
                <li><strong>Droit à la portabilité</strong> : recevoir vos données dans un format structuré et lisible par machine.</li>
                <li><strong>Retrait du consentement</strong> : retirer à tout moment un consentement précédemment donné, sans que cela ne remette en cause la licéité des traitements effectués avant ce retrait.</li>
                <li><strong>Directives post-mortem</strong> : définir des instructions relatives à la conservation et à la communication de vos données après votre décès.</li>
            </ul>
            <p>Pour exercer ces droits, vous pouvez agir directement depuis votre espace profil ou envoyer un email à <a href="mailto:contact@bassila-emergence.org">contact@bassila-emergence.org</a>. Une réponse vous sera apportée dans un délai de trente (30) jours.</p>
        </section>

        <section id="suppression">
            <h2>8. Suppression de compte</h2>
            <p>Tout membre peut demander la suppression de son compte depuis la page de modification de son profil. La procédure est la suivante :</p>
            <ol>
                <li>Clic sur «&nbsp;Supprimer mon compte&nbsp;» dans la section «&nbsp;Zone dangereuse&nbsp;» du profil.</li>
                <li>Saisie du mot de passe et confirmation dans la fenêtre modale.</li>
                <li>Envoi d'un email de confirmation, valable 24 heures.</li>
                <li>Clic sur le lien de confirmation&nbsp;: le compte est désactivé et la purge est planifiée à trente (30) jours.</li>
                <li>Pendant ce délai, toute reconnexion propose l'annulation de la demande.</li>
                <li>À l'échéance, la purge est exécutée&nbsp;: le profil est supprimé, les articles et commentaires anonymisés, les données de compte (email, mot de passe) effacées. Les journaux d'audit sont conservés un (1) an pour des raisons de sécurité.</li>
            </ol>
        </section>

        <section id="cookies">
            <h2>9. Cookies et traceurs</h2>
            <p>Dans un souci de transparence, nous précisons que la plateforme Bassila Emergence est développée avec le framework open source <strong>Laravel</strong> (PHP), l'interface interactive utilise <strong>Livewire</strong>, les feuilles de style sont générées via <strong>Tailwind CSS</strong>, et les données sont stockées dans une base de données relationnelle <strong>PostgreSQL</strong>. Les noms de certains cookies reflètent directement ces choix techniques (par exemple <code>laravel_session</code>) ; cette information est partagée ici pour vous permettre de comprendre ce qui est déposé dans votre navigateur.</p>
            <p>La plateforme utilise les cookies et traceurs suivants :</p>
            <ul>
                <li><strong><code>laravel_session</code></strong> : cookie de session Laravel, strictement nécessaire au fonctionnement du service.</li>
                <li><strong><code>XSRF-TOKEN</code></strong> : jeton de protection contre les attaques CSRF, strictement nécessaire à la sécurité des formulaires.</li>
                <li><strong><code>remember_web_*</code></strong> : cookie de connexion persistante (« se souvenir de moi »), actif uniquement si le membre a coché l'option correspondante lors de la connexion.</li>
                <li><strong>Pixel d'ouverture newsletter</strong> : traceur d'ouverture inclus dans les emails de la newsletter. Son utilisation est soumise au consentement donné lors de l'inscription à la newsletter.</li>
            </ul>
            <p>Tous les cookies utilisés sont soit strictement nécessaires au service, soit conditionnés à un consentement opt-in explicite. Aucun bandeau de consentement cookies n'est affiché pour les cookies strictement nécessaires.</p>
        </section>

        <section id="securite">
            <h2>10. Sécurité</h2>
            <p>Bassila Emergence met en œuvre les mesures techniques et organisationnelles suivantes pour protéger vos données :</p>
            <ul>
                <li>Chiffrement des communications via HTTPS (TLS).</li>
                <li>Hachage des mots de passe selon les algorithmes bcrypt ou Argon2id.</li>
                <li>Accès à l'interface d'administration restreint aux rôles habilités.</li>
                <li>Journaux d'audit permettant la détection d'accès non autorisés.</li>
            </ul>
        </section>

        <section id="reclamations">
            <h2>11. Réclamations</h2>
            <p>Si vous estimez que le traitement de vos données ne respecte pas la réglementation applicable, vous avez le droit d'introduire une réclamation auprès de l'autorité de contrôle compétente :</p>
            <ul>
                <li><strong>APDP</strong> (Autorité de Protection des Données Personnelles du Bénin) — autorité compétente pour les résidents béninois.</li>
                <li><strong>CNIL</strong> (Commission Nationale de l'Informatique et des Libertés, France) — autorité compétente pour les résidents de l'Union européenne.</li>
                <li>Les dispositions de l'<strong>acte additionnel A/SA.1/01/10</strong> de la CEDEAO relatif à la protection des données personnelles dans l'espace communautaire s'appliquent également.</li>
            </ul>
        </section>

        <section id="modifications">
            <h2>12. Modifications de la politique</h2>
            <p>Bassila Emergence se réserve le droit de modifier la présente politique de confidentialité. En cas de changement substantiel, les membres seront informés par email au moins trente (30) jours avant l'entrée en vigueur des nouvelles dispositions. La poursuite de l'utilisation du service après cette date vaut acceptation des modifications.</p>
        </section>

        <section id="contact">
            <h2>13. Contact</h2>
            <p>Pour toute question relative à la présente politique ou à l'exercice de vos droits : <a href="mailto:contact@bassila-emergence.org">contact@bassila-emergence.org</a>.</p>
        </section>

    </div>
</div>
@endsection
