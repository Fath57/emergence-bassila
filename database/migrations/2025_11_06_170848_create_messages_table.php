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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');

            // Contenu du message
            $table->string('subject')->nullable();
            $table->text('body');

            // Statut
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            // Réponse à un message
            $table->foreignId('parent_message_id')->nullable()->constrained('messages')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            // Index pour optimiser les recherches
            $table->index(['sender_id', 'recipient_id']);
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
