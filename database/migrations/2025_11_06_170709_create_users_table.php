<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Informations d'authentification
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            // Informations personnelles
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'Autre'])->nullable();

            // Photo de profil
            $table->string('profile_photo')->nullable();
            $table->string('cover_photo')->nullable();

            // Origine à Bassila
            $table->string('village_origin')->nullable(); // Village d'origine
            $table->string('quartier')->nullable(); // Quartier à Bassila

            // Localisation actuelle
            $table->string('current_city')->nullable(); // Ville actuelle
            $table->string('current_country')->nullable(); // Pays actuel
            $table->text('current_address')->nullable();

            // Informations professionnelles
            $table->string('current_profession')->nullable();
            $table->string('current_company')->nullable();
            $table->string('professional_status')->nullable(); // Employé, Entrepreneur, Étudiant, etc.

            // Biographie et présentation
            $table->text('bio')->nullable();
            $table->text('skills_summary')->nullable(); // Résumé des compétences

            // Réseaux sociaux
            $table->string('linkedin_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();

            // Disponibilité pour opportunités
            $table->boolean('open_to_opportunities')->default(true);
            $table->text('interests')->nullable(); // Centres d'intérêt

            // Statut du compte
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            // Rôle
            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');

            // Visibilité du profil
            $table->boolean('profile_visible')->default(true);

            $table->timestamps();
            $table->softDeletes(); // Pour permettre la suppression douce

            // Index
            $table->index(['status', 'profile_visible']);
            $table->index('current_city');
            $table->index('current_country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
