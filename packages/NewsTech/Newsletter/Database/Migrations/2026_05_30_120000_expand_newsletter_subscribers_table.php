<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->string('unsubscribe_token')->nullable()->unique()->after('email');
            $table->string('ip_address', 45)->nullable()->after('source');
            $table->text('user_agent')->nullable()->after('ip_address');
        });

        DB::table('newsletter_subscribers')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(100, function ($subscribers): void {
                foreach ($subscribers as $subscriber) {
                    DB::table('newsletter_subscribers')
                        ->where('id', $subscriber->id)
                        ->update(['unsubscribe_token' => Str::random(40)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table): void {
            $table->dropUnique(['unsubscribe_token']);
            $table->dropColumn([
                'unsubscribe_token',
                'ip_address',
                'user_agent',
            ]);
        });
    }
};
