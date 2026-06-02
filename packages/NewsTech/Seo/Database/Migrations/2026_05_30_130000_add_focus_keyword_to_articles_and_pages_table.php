<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('focus_keyword')->nullable()->after('meta_description');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->string('focus_keyword')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('focus_keyword');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('focus_keyword');
        });
    }
};
