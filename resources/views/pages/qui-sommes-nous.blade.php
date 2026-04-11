@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Qui sommes-nous')
        ->withDescription('Bassila Émergence est la plateforme qui réunit les Bassilois du Bénin et de la diaspora. Découvrez notre mission, nos valeurs et notre contact.')
        ->withOgType('website');
@endphp
@extends('layouts.app')
@section('title', 'Qui sommes-nous')
@section('description', 'Bassila Émergence est la plateforme qui réunit les Bassilois du Bénin et de la diaspora. Découvrez notre mission, nos valeurs et notre contact.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs
        :items="[
            ['name' => 'Accueil', 'url' => route('home')],
            ['name' => 'Qui sommes-nous', 'url' => null],
        ]"
        :with-json-ld="true"
    />

    <header class="mb-12">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">À propos</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight" style="font-family: 'Lora', serif;">
            Qui sommes-nous — Bassila Émergence
        </h1>
    </header>

    <section class="prose prose-lg max-w-none mb-12">
        <h2>Notre mission</h2>
        <p>
            Bassila Émergence est la plateforme numérique qui réunit les natifs de Bassila, commune du département de la Donga au Bénin, et leur diaspora à travers le monde. Notre mission est de créer un lien vivant entre les générations, les horizons et les parcours professionnels de notre communauté.
        </p>
        <p>
            Chaque profil raconte une histoire de résilience et de réussite. Chaque connexion est un pont entre une personne qui cherche ses racines et une communauté qui l'accueille. En rendant visible la richesse des Bassilois du Bénin comme de la diaspora, nous contribuons à renforcer l'identité, l'entraide et les opportunités au sein du réseau.
        </p>
    </section>

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-[#111827] mb-6">Nos valeurs</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Entraide</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    La solidarité entre Bassilois — au pays et à l'étranger — est le ciment de notre réseau. Chaque membre est à la fois bénéficiaire et contributeur.
                </p>
            </div>
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Transparence</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Les profils sont modérés et vérifiés avant publication. Nous préférons un réseau authentique à un annuaire volumineux mais approximatif.
                </p>
            </div>
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Fierté communautaire</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Bassila a une histoire, une culture et une diaspora dont nous sommes fiers. La plateforme met cette fierté au service des parcours individuels et collectifs.
                </p>
            </div>
            <div class="border border-gray-200 p-6">
                <h3 class="font-bold text-[#111827] mb-2">Ouverture</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Nous accueillons tous les secteurs, toutes les générations, toutes les géographies. La diversité des parcours est une force.
                </p>
            </div>
        </div>
    </section>

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-[#111827] mb-4">Comment ça marche</h2>
        <p class="text-gray-600 leading-relaxed mb-4">
            Créez votre compte en quelques secondes avec votre adresse email. Après vérification, renseignez votre profil : parcours, métier, localisation. Un administrateur vérifie et valide votre profil, puis vous rejoignez officiellement l'annuaire des Bassilois.
        </p>
        <a href="{{ route('register') }}" class="inline-block bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
            Créer mon profil
        </a>
    </section>

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-[#111827] mb-4">Contact</h2>
        <p class="text-gray-600 leading-relaxed">
            Pour toute question concernant la plateforme, écrivez-nous à
            <a href="mailto:{{ setting('contact.email', 'contact@bassila-emergence.org') }}" class="text-[#0066CC] font-semibold hover:underline">
                {{ setting('contact.email', 'contact@bassila-emergence.org') }}
            </a>.
        </p>
    </section>

</div>
@endsection
