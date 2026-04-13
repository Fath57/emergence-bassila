@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('Mentions légales')
        ->withDescription('Mentions légales de la plateforme Bassila Emergence.')
        ->withOgType('website');

    $version = '1.0';
    $updatedAt = '14 avril 2026';
@endphp
@extends('layouts.app')
@section('title', 'Mentions légales')
@section('description', 'Mentions légales de Bassila Emergence.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs :items="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'Mentions légales', 'url' => null],
    ]" :with-json-ld="true" />

    <header class="mb-10">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Informations légales</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight" style="font-family: 'Lora', serif;">
            Mentions légales
        </h1>
        <p class="text-sm text-gray-500 mt-3">Version {{ $version }} — Dernière mise à jour : {{ $updatedAt }}</p>
    </header>

    <nav aria-label="Sommaire" class="mb-10 p-6 bg-gray-50 rounded-sm">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-3">Sommaire</p>
        <ol class="list-decimal list-inside text-sm space-y-1">
            <li><a href="#editeur" class="hover:underline">Éditeur du site</a></li>
            <li><a href="#publication" class="hover:underline">Directeur de la publication</a></li>
            <li><a href="#hebergeur" class="hover:underline">Hébergeur</a></li>
            <li><a href="#pi" class="hover:underline">Propriété intellectuelle</a></li>
            <li><a href="#signalement" class="hover:underline">Signalement de contenu illicite</a></li>
        </ol>
    </nav>

    <div class="prose prose-lg max-w-none">

        <section id="editeur">
            <h2>1. Éditeur du site</h2>
            <p>Le présent site est édité par :</p>
            <p>
                <strong>Bassila Emergence</strong><br>
                Collectif communautaire (sans entité juridique formelle)<br>
                Bassila, Département de la Donga, République du Bénin<br>
                Email : <a href="mailto:contact@bassila-emergence.org">contact@bassila-emergence.org</a>
            </p>
            <p>Représentant : à renseigner au déploiement.</p>
        </section>

        <section id="publication">
            <h2>2. Directeur de la publication</h2>
            <p>À renseigner au déploiement.</p>
        </section>

        <section id="hebergeur">
            <h2>3. Hébergeur</h2>
            <p>Nom et adresse à renseigner au déploiement.</p>
        </section>

        <section id="pi">
            <h2>4. Propriété intellectuelle</h2>
            <p>Le logo, le design, le code source et l'ensemble des éléments graphiques originaux de la plateforme sont la propriété exclusive de Bassila Emergence. Toute reproduction, représentation, modification, publication, adaptation ou exploitation de tout ou partie de ces éléments, quel qu'en soit le moyen ou le procédé, est interdite sans l'autorisation écrite préalable de Bassila Emergence.</p>
        </section>

        <section id="signalement">
            <h2>5. Signalement de contenu illicite</h2>
            <p>Pour signaler tout contenu illicite publié sur la plateforme, veuillez écrire à l'adresse dédiée : <a href="mailto:contact@bassila-emergence.org">contact@bassila-emergence.org</a>. Toute demande fera l'objet d'un examen dans les meilleurs délais.</p>
        </section>

    </div>
</div>
@endsection
