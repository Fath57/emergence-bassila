<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add nullable first_name / last_name columns for backfill
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('id');
            $table->string('last_name', 100)->nullable()->after('first_name');
        });

        // 2. Backfill from existing "name" — first word → first_name, rest → last_name
        foreach (DB::table('users')->select('id', 'name')->get() as $user) {
            $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [''];
            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? '',
                'last_name'  => $parts[1] ?? '',
            ]);
        }

        // 3. Make them NOT NULL
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable(false)->change();
            $table->string('last_name', 100)->nullable(false)->change();
        });

        // 4. Drop the original "name" column …
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // 5. … and recreate it as a Postgres STORED generated column so
        //    $user->name (Auth notifications, Filament columns, views, mails)
        //    keeps working without any code changes downstream.
        DB::statement("
            ALTER TABLE users
            ADD COLUMN name VARCHAR(255)
            GENERATED ALWAYS AS (TRIM(COALESCE(first_name, '') || ' ' || COALESCE(last_name, ''))) STORED
        ");
    }

    public function down(): void
    {
        // Drop generated column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // Recreate a regular "name" column and backfill from first/last_name
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::statement("UPDATE users SET name = TRIM(COALESCE(first_name, '') || ' ' || COALESCE(last_name, ''))");

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
