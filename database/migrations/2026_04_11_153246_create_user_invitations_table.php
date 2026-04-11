<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('role', 50);
            $table->string('token', 64)->unique();
            $table->text('message')->nullable();
            $table->foreignId('invited_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index('expires_at', 'idx_invitations_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
