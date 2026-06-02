<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')
                ->constrained('articles')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['article_id', 'category_id']);
        });

        $timestamp = now();

        DB::table('articles')
            ->select(['id', 'category_id'])
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunkById(200, function ($articles) use ($timestamp): void {
                $rows = $articles->map(fn ($article): array => [
                    'article_id' => $article->id,
                    'category_id' => $article->category_id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();

                if ($rows !== []) {
                    DB::table('article_category')->insertOrIgnore($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_category');
    }
};
