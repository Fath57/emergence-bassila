<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('country_id')
                  ->nullable()
                  ->after('city')
                  ->constrained('countries')
                  ->nullOnDelete();

            $table->string('phone', 30)->nullable()->after('portfolio_url');
            $table->string('email_contact', 255)->nullable()->after('phone');
        });

        // Migrate existing free-text country to null (no mapping possible without data)
        // The old `country` column is kept nullable for backward compat during transition
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn(['phone', 'email_contact']);
            $table->string('country', 100)->nullable(false)->change();
        });
    }
};
