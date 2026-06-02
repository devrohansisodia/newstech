<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('type', 20);
            $table->string('status', 20)->default('inactive');
            $table->string('slot_key', 100);
            $table->string('title')->nullable();
            $table->string('image_path')->nullable();
            $table->string('target_url', 2048)->nullable();
            $table->longText('html_content')->nullable();
            $table->boolean('open_in_new_tab')->default(true);
            $table->boolean('nofollow')->default(false);
            $table->boolean('sponsored')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('priority')->default(0);
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['slot_key', 'status', 'priority']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
