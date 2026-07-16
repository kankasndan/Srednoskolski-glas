<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->softDeletes();
            $table->id();

            // Link to the thread
            $table->foreignId('thread_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // The self-referencing parent ID (nullable for top-level comments)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('comments') // Explicitly point to this same table
                  ->cascadeOnDelete();

            // Link to the user who wrote the comment
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->text('content');
            $table->unsignedTinyInteger('upvotes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};