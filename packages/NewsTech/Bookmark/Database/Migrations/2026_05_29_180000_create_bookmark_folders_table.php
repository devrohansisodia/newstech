<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmark_folders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reader_id')->constrained('readers')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['reader_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmark_folders');
    }
};
