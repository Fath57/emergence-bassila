@php
    use App\Support\Seo\SeoData;

    $seo = SeoData::default()
        ->withTitle('À propos de Bassila — Commune du Donga, Bénin')
        ->withDescription('Découvrez Bassila, commune du département de la Donga au Bénin : géographie, histoire, culture et diaspora. La plateforme du réseau des Bassilois.')
        ->withOgType('website');
@endphp
@extends('layouts.app')
@section('title', 'À propos de Bassila — Commune du Donga, Bénin')
@section('description', 'Découvrez Bassila, commune du département de la Donga au Bénin : géographie, histoire, culture et diaspora. La plateforme du réseau des Bassilois.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <x-breadcrumbs
        :items="[
            ['name' => 'Accueil', 'url' => route('home')],
            ['name' => 'À propos de Bassila', 'url' => null],
        ]"
        :with-json-ld="true"
    />

    <header class="mb-12 anim-reveal">
        <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Territoire</p>
        <h1 class="text-4xl font-bold text-[#111827] leading-tight mb-4" style="font-family: 'Lora', serif;">
            Bassila — Commune du nord-ouest du Bénin
        </h1>
        <p class="text-gray-600 text-lg leading-relaxed">
            Située dans le département de la Donga, Bassila est une commune au cœur du Bénin dont la population et la diaspora tissent un lien vivant entre territoire et monde.
        </p>
    </header>

    <article class="prose prose-lg max-w-none anim-reveal">

        <h2>Géographie</h2>
        <p>
            Bassila est une commune du département de la Donga, dans le nord-ouest du Bénin. Elle couvre un vaste territoire caractérisé par des paysages de savane arborée, une pluviométrie marquée par la saison sèche et la saison des pluies, et une mosaïque de villages qui structurent la vie communautaire. La commune est traversée par des axes routiers qui la relient aux grandes villes du Bénin et aux pays voisins.
        </p>
        <p>
            {{-- TODO éditorial — préciser la superficie, la population, les principales localités, la distance à Cotonou/Parakou --}}
        </p>

        <h2>Histoire</h2>
        <p>
            L'histoire de Bassila se lit à travers celle de ses habitants, de ses langues et de ses traditions. La commune est le fruit de plusieurs vagues de peuplement qui ont façonné une identité singulière dans le paysage béninois.
        </p>
        <p>
            {{-- TODO éditorial — origines, dates clés, figures historiques, rapport au royaume et à l'administration coloniale puis à l'indépendance --}}
        </p>

        <h2>Culture & traditions</h2>
        <p>
            La richesse culturelle de Bassila s'exprime à travers plusieurs langues parlées dans la commune, des fêtes traditionnelles qui rythment l'année, un artisanat local et une cuisine qui reflètent l'histoire des échanges avec les communautés voisines.
        </p>
        <p>
            {{-- TODO éditorial — lister les langues parlées (Anii, Nagot, etc.), les fêtes principales, les artisanats emblématiques --}}
        </p>

        <h2>Économie locale</h2>
        <p>
            L'économie de Bassila est portée par l'agriculture, le petit commerce, l'artisanat et des secteurs en développement. Les Bassilois présents dans le réseau couvrent un large éventail de métiers, du secteur médical à l'ingénierie en passant par l'éducation et l'entrepreneuriat.
        </p>
        <p>
            <a href="{{ route('directory.index') }}" class="text-[#0066CC] font-semibold hover:underline hover-arrow">
                Explorer les secteurs représentés dans l'annuaire &rarr;
            </a>
        </p>

        <h2>La diaspora Bassiloise</h2>
        <p>
            Des centaines de Bassilois vivent aujourd'hui en Afrique, en Europe et à travers le monde. Ils sont médecins, ingénieurs, enseignants, entrepreneurs, artistes — et ils partagent un même attachement à leur commune d'origine. Bassila Émergence est leur maison numérique commune : un lieu où les générations se retrouvent, où les compétences se partagent, et où les ponts entre le pays et le monde se construisent.
        </p>

    </article>

    <section class="mt-16 bg-[#0066CC] text-white p-10 anim-reveal">
        <h2 class="text-2xl font-bold mb-3" style="font-family: 'Lora', serif;">
            Faites partie du réseau
        </h2>
        <p class="text-white/85 mb-6 max-w-2xl">
            Si vous êtes Bassilois(e) — au Bénin ou à l'étranger — rejoignez la plateforme, créez votre profil et connectez-vous avec la communauté.
        </p>
        <a href="{{ route('register') }}" class="inline-block bg-white text-[#0066CC] font-semibold px-7 py-3 text-sm hover:bg-gray-100 transition">
            Créer mon profil
        </a>
    </section>

</div>
@endsection
