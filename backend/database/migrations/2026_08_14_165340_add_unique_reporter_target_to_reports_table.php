<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $keepIds = DB::table('reports')
            ->whereNull('deleted_at')
            ->whereNotNull('reporter_id')
            ->groupBy('reporter_id', 'reportable_type', 'reportable_id')
            ->selectRaw('MIN(id) as id')
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            DB::table('reports')
                ->whereNull('deleted_at')
                ->whereNotNull('reporter_id')
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        Schema::table('reports', function (Blueprint $table) {
            $table->unique(
                ['reporter_id', 'reportable_type', 'reportable_id'],
                'reports_reporter_target_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropUnique('reports_reporter_target_unique');
        });
    }
};
