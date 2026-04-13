<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('user_invitations', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
        });
        Schema::table('user_invitations', function (Blueprint $table) {
            $table->foreignId('invited_by')->nullable()->change();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('blog_comments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('account_deletion_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('user_invitations', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
        });
        Schema::table('user_invitations', function (Blueprint $table) {
            $table->foreignId('invited_by')->nullable(false)->change();
            $table->foreign('invited_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
