<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('education_level', 50)->nullable()->after('education_end_year');
            $table->index('education_level', 'idx_profiles_education_level');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('idx_profiles_education_level');
            $table->dropColumn('education_level');
        });
    }
};
