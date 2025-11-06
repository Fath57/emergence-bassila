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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Auteur
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            // Contenu
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable(); // Résumé
            $table->longText('content'); // Contenu complet
            $table->string('featured_image')->nullable();

            // Métadonnées
            $table->enum('type', ['Actualité', 'Événement', 'Annonce', 'Culture'])->default('Actualité');
            $table->date('event_date')->nullable(); // Si c'est un événement
            $table->string('event_location')->nullable();

            // Publication
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();

            // Statistiques
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);

            // Tags et catégories
            $table->json('tags')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['status', 'published_at']);
            $table->index('slug');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
