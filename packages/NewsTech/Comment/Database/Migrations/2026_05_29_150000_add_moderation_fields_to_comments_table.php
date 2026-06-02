<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->boolean('is_spam')->default(false)->after('status');
            $table->string('spam_reason')->nullable()->after('is_spam');
            $table->timestamp('moderated_at')->nullable()->after('approved_at');
            $table->foreignId('moderated_by')->nullable()->after('moderated_at')->constrained('admin_users')->nullOnDelete();
            $table->index(['status', 'is_spam']);
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('moderated_by');
            $table->dropIndex(['status', 'is_spam']);
            $table->dropColumn(['is_spam', 'spam_reason', 'moderated_at']);
        });
    }
};
