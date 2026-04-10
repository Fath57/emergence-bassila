<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // GIN fulltext index on profiles for French-language search
        DB::statement("
            CREATE INDEX idx_profiles_fulltext ON profiles
            USING GIN(to_tsvector('french',
                coalesce(full_name,'') || ' ' ||
                coalesce(job_title,'') || ' ' ||
                coalesce(company,'')
            ))
        ");

        // Additional indexes on profiles
        Schema::table('profiles', function (Blueprint $table) {
            $table->index('sector_id', 'idx_profiles_sector_id');
            $table->index('country', 'idx_profiles_country');
            $table->index('is_verified', 'idx_profiles_is_verified');
        });

        // Composite index on blog_posts for status + published_at queries
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'idx_blog_posts_status_published_at');
        });

        // Index on contact_messages for inbox queries
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->index('to_user_id', 'idx_contact_messages_to_user_id');
        });

        // Composite index on blog_comments for moderation queries
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->index(['blog_post_id', 'moderated_at'], 'idx_blog_comments_post_moderated');
        });
    }

    public function down(): void
    {
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropIndex('idx_blog_comments_post_moderated');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex('idx_contact_messages_to_user_id');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex('idx_blog_posts_status_published_at');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('idx_profiles_is_verified');
            $table->dropIndex('idx_profiles_country');
            $table->dropIndex('idx_profiles_sector_id');
        });

        DB::statement('DROP INDEX IF EXISTS idx_profiles_fulltext');
    }
};
