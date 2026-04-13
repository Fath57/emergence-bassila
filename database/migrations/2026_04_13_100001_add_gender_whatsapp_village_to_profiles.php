<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->char('gender', 1)->nullable()->after('last_name');
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->foreignId('village_id')
                  ->nullable()
                  ->after('city')
                  ->constrained('villages')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('village_id');
            $table->dropColumn(['gender', 'whatsapp']);
        });
    }
};
