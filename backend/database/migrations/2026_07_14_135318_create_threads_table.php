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
        Schema::create('threads', function (Blueprint $table) {
            $table->softDeletes();
            $table->id();
            $table->string("title");
            $table->text("description");
            $table->unsignedInteger("upvotes");
            $table->unsignedInteger("views");
            $table->foreignId("user_id")->nullable()->constrained();
            $table->foreignId("forum_id")->constrained()->cascadeOnDelete();
            $table->boolean("is_anonymous")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
