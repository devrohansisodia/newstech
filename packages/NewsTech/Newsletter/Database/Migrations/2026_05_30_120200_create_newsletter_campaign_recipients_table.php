<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->nullable()->constrained('newsletter_subscribers')->nullOnDelete();
            $table->string('email');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_campaign_recipients');
    }
};
