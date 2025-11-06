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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Qui a posté
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            // Informations sur l'opportunité
            $table->string('title'); // Titre de l'opportunité
            $table->enum('type', ['Emploi', 'Stage', 'Freelance', 'Bénévolat', 'Collaboration', 'Autre'])->default('Emploi');
            $table->text('description'); // Description détaillée
            $table->text('requirements')->nullable(); // Exigences/Compétences requises

            // Localisation
            $table->string('location')->nullable(); // Lieu
            $table->boolean('remote_possible')->default(false); // Télétravail possible ?

            // Entreprise
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();

            // Détails du poste
            $table->string('contract_type')->nullable(); // CDI, CDD, etc.
            $table->string('salary_range')->nullable(); // Fourchette de salaire
            $table->string('experience_required')->nullable(); // Années d'expérience

            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('application_url')->nullable(); // Lien pour postuler

            // Dates
            $table->date('deadline')->nullable(); // Date limite de candidature
            $table->date('start_date')->nullable(); // Date de début souhaitée

            // Statut
            $table->enum('status', ['active', 'closed', 'draft'])->default('active');
            $table->boolean('is_featured')->default(false); // Opportunité mise en avant
            $table->integer('views_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['status', 'type']);
            $table->index('deadline');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
