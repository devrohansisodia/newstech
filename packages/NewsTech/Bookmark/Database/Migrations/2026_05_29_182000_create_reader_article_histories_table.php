<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_article_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reader_id')->constrained('readers')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->timestamp('last_viewed_at');
            $table->unsignedInteger('view_count')->default(1);
            $table->timestamps();

            $table->unique(['reader_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_article_histories');
    }
};
