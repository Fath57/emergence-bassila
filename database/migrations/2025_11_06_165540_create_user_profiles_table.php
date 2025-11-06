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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('quartier_origine')->nullable(); // Quartier d'origine à Bassila
            $table->string('ville_actuelle')->nullable();
            $table->string('pays_actuel')->nullable();
            $table->text('bio')->nullable();
            $table->string('profession')->nullable();
            $table->text('competences')->nullable(); // JSON ou texte des compétences
            $table->string('formation')->nullable();
            $table->text('experience')->nullable();
            $table->string('domaine_expertise')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('photo')->nullable(); // Chemin vers la photo de profil
            $table->boolean('visible_annuaire')->default(true);
            $table->boolean('disponible_opportunites')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
