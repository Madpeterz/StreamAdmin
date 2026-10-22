<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline: the 2.0.1.x schema and its default data.
 *
 * Existing installs already have it (and are renamed by the next migration), so this only runs
 * the original installer SQL on an empty database.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('slconfig') || Schema::hasTable('sl_configs')) {
            return;
        }
        foreach (['2.0.0.0-installer.sql', '2.0.1.1-upgrade.sql'] as $file) {
            DB::unprepared(file_get_contents(database_path('legacy/sql/'.$file)));
        }
    }

    public function down(): void
    {
        // the legacy schema is never dropped automatically
    }
};
