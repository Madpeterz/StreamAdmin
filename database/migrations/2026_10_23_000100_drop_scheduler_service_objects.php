<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The scheduled tasks used to check in as virtual service objects in a region named "cron". The
 * Health page now reads the tasks' own last run, so those rows and the region go.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sl_objects')->whereIn('object_uuid', array_map(
            fn (int $number) => $number.'0000000-0000-0000-0000-000000000000',
            [1, 2, 3],
        ))->delete();

        $cron = DB::table('regions')->where('name', 'cron')->value('id');
        if ($cron !== null && ! DB::table('sl_objects')->where('region_id', $cron)->exists()) {
            DB::table('regions')->where('id', $cron)->delete();
        }
    }

    public function down(): void
    {
        // the rows were virtual; the next scheduler run no longer recreates them
    }
};
