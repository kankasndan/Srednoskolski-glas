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
        Schema::create('thread_attachments', function (Blueprint $table) {
            $table->id();
            $table->string("url");
            $table->enum("slug", ["video", "image", "file", "link"]); // 10mb za file, 25mb za video i slika
            $table->foreignId("thread_id")->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thread_attachments');
    }
};
