<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookmarks', function (Blueprint $table): void {
            $table->foreignId('folder_id')->nullable()->after('article_id')->constrained('bookmark_folders')->nullOnDelete();
            $table->index('folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookmarks', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('folder_id');
        });
    }
};
