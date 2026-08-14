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
        Schema::table('student_data', function (Blueprint $table) {
            $table->timestamp('school_changed_at')->nullable()->after('grade');
            $table->timestamp('grade_promoted_at')->nullable()->after('school_changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_data', function (Blueprint $table) {
            $table->dropColumn(['school_changed_at', 'grade_promoted_at']);
        });
    }
};
