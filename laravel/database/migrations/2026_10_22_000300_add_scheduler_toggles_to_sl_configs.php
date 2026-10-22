<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-task pause switches for the scheduler (replaces the 2.0.x cron container). The in-world
 * queue tasks start on, as they always ran in 2.0.x; the export starts off.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sl_configs', function (Blueprint $table) {
            $table->boolean('scheduler_botcommandq')->default(true);
            $table->boolean('scheduler_details_server')->default(true);
            $table->boolean('scheduler_dynamic_notecards')->default(true);
            $table->boolean('scheduler_export')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('sl_configs', function (Blueprint $table) {
            $table->dropColumn(['scheduler_botcommandq', 'scheduler_details_server', 'scheduler_dynamic_notecards', 'scheduler_export']);
        });
    }
};
