<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 50)->default('imagekit');
            $table->string('file_id');
            $table->string('path')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('directory')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'file_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_uploads');
    }
};
