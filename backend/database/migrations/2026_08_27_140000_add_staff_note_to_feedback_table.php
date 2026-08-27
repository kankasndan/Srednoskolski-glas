<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->text('staff_note')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn('staff_note');
        });
    }
};
