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
        Schema::table('thread_attachments', function (Blueprint $table) {
            $table->string('provider')->default('imagekit')->after('slug');
            $table->string('file_id')->nullable()->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thread_attachments', function (Blueprint $table) {
            $table->dropColumn(['provider', 'file_id']);
        });
    }
};
