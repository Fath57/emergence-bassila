<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('name', 100);
            $table->string('code', 2)->unique();
            $table->string('flag', 10)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(99);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
