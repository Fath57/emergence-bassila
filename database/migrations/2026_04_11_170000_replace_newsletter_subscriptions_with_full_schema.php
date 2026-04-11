<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name', 100)->nullable();
            $table->string('confirmation_token', 64)->nullable();
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('source', 50)->default('public_form');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->index('confirmed_at',    'idx_subs_confirmed');
            $table->index('unsubscribed_at', 'idx_subs_unsubscribed');
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->longText('content');
            $table->string('preview_text', 150)->nullable();
            // Postgres does not support enum() directly in Laravel; use a
            // CHECK constraint via raw string column.
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('batch_id')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('opens_count')->default(0);
            $table->unsignedInteger('unsubscribes_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_campaigns_status');
        });

        // Postgres CHECK constraint on status
        \DB::statement(
            "ALTER TABLE newsletter_campaigns
             ADD CONSTRAINT chk_campaign_status
             CHECK (status IN ('draft', 'sending', 'sent', 'failed'))"
        );

        Schema::create('newsletter_campaign_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                  ->constrained('newsletter_campaigns')
                  ->cascadeOnDelete();
            $table->foreignId('subscriber_id')
                  ->constrained('newsletter_subscribers')
                  ->cascadeOnDelete();
            $table->string('open_token', 32)->unique();
            $table->timestamp('sent_at');
            $table->timestamp('opened_at')->nullable();
            $table->text('error')->nullable();

            $table->unique(['campaign_id', 'subscriber_id'], 'uq_campaign_subscriber');
            $table->index('opened_at', 'idx_sends_opened');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaign_sends');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_subscribers');

        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('created_at')->useCurrent();
        });
    }
};
