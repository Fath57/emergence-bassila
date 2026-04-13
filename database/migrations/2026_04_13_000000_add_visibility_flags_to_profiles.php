<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('show_phone')->default(false)->after('phone');
            $table->boolean('show_email_contact')->default(false)->after('email_contact');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['show_phone', 'show_email_contact']);
        });
    }
};
