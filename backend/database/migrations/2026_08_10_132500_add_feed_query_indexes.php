<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('threads', function (Blueprint $table): void {
            // Trending candidate window: WHERE created_at >= ? ORDER BY created_at DESC LIMIT n
            $table->index('created_at', 'threads_created_at_index');
        });

        Schema::table('votes', function (Blueprint $table): void {
            // recent_votes_count / engagement lookups filtered by votable + created_at
            $table->index(
                ['votable_type', 'votable_id', 'created_at'],
                'votes_votable_created_at_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table): void {
            $table->dropIndex('threads_created_at_index');
        });

        Schema::table('votes', function (Blueprint $table): void {
            $table->dropIndex('votes_votable_created_at_index');
        });
    }
};
