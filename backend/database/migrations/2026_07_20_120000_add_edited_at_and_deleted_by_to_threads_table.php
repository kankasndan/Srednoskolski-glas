<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            // Set when the author edits the thread -> powers the "(edited)" label.
            $table->timestamp('edited_at')->nullable()->after('updated_at');

            // Who soft-deleted the thread (author vs. moderator) -> tombstone text.
            $table->foreignId('deleted_by')->nullable()->after('edited_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropColumn('edited_at');
        });
    }
};
