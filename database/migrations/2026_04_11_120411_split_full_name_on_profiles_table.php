<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the fulltext GIN index (it references full_name)
        DB::statement('DROP INDEX IF EXISTS idx_profiles_fulltext');

        // 2. Add nullable first_name / last_name columns
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('user_id');
            $table->string('last_name', 100)->nullable()->after('first_name');
        });

        // 3. Backfill from existing full_name
        foreach (DB::table('profiles')->select('id', 'full_name')->get() as $profile) {
            $parts = preg_split('/\s+/', trim((string) $profile->full_name), 2) ?: [''];
            DB::table('profiles')->where('id', $profile->id)->update([
                'first_name' => $parts[0] ?? '',
                'last_name'  => $parts[1] ?? '',
            ]);
        }

        // 4. Make NOT NULL
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable(false)->change();
            $table->string('last_name', 100)->nullable(false)->change();
        });

        // 5. Drop the old full_name column
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });

        // 6. Recreate full_name as a Postgres STORED generated column
        DB::statement("
            ALTER TABLE profiles
            ADD COLUMN full_name VARCHAR(255)
            GENERATED ALWAYS AS (TRIM(COALESCE(first_name, '') || ' ' || COALESCE(last_name, ''))) STORED
        ");

        // 7. Recreate the French-language fulltext GIN index
        DB::statement("
            CREATE INDEX idx_profiles_fulltext ON profiles
            USING GIN(to_tsvector('french',
                coalesce(full_name,'') || ' ' ||
                coalesce(job_title,'') || ' ' ||
                coalesce(company,'')
            ))
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_profiles_fulltext');

        // Drop generated column
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });

        // Recreate as regular nullable column …
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('full_name', 255)->nullable()->after('user_id');
        });

        // … backfill
        DB::statement("UPDATE profiles SET full_name = TRIM(COALESCE(first_name, '') || ' ' || COALESCE(last_name, ''))");

        Schema::table('profiles', function (Blueprint $table) {
            $table->string('full_name', 255)->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name']);
        });

        // Recreate original fulltext index
        DB::statement("
            CREATE INDEX idx_profiles_fulltext ON profiles
            USING GIN(to_tsvector('french',
                coalesce(full_name,'') || ' ' ||
                coalesce(job_title,'') || ' ' ||
                coalesce(company,'')
            ))
        ");
    }
};
