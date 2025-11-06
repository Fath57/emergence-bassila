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
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Informations sur le poste
            $table->string('job_title'); // Titre du poste
            $table->string('company_name'); // Nom de l'entreprise
            $table->string('company_location')->nullable(); // Localisation
            $table->enum('employment_type', ['CDI', 'CDD', 'Freelance', 'Stage', 'Bénévolat'])->nullable();

            // Période
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null si poste actuel
            $table->boolean('is_current')->default(false);

            // Description
            $table->text('description')->nullable();
            $table->text('achievements')->nullable(); // Réalisations

            $table->timestamps();

            // Index pour les recherches
            $table->index('user_id');
            $table->index('is_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
