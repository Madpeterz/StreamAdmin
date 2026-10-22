<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 2.0.1.x -> 3.0.0: rename tables and columns to Laravel conventions (database/legacy/schema-map.php).
 *
 * Each table is handled on its own and skipped when already renamed, so a run that stopped
 * part way can be resumed. InnoDB keeps foreign keys pointing at renamed tables and columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->map() as $oldTable => [$newTable, $columns]) {
            $this->renameTable($oldTable, $newTable, $columns);
        }
        DB::table('sl_configs')->where('id', 1)->update(['db_version' => '3.0.0']);
    }

    public function down(): void
    {
        DB::table('sl_configs')->where('id', 1)->update(['db_version' => '2.0.1.2']);
        foreach ($this->map() as $oldTable => [$newTable, $columns]) {
            $this->renameTable($newTable, $oldTable, array_flip($columns));
        }
    }

    /** @param array<string, string> $columns */
    private function renameTable(string $from, string $to, array $columns): void
    {
        if ($from !== $to && Schema::hasTable($from)) {
            DB::statement('RENAME TABLE `'.$from.'` TO `'.$to.'`');
        }
        $existing = Schema::getColumnListing($to);
        $renames = [];
        foreach ($columns as $old => $new) {
            if (in_array($old, $existing, true)) {
                $renames[] = 'RENAME COLUMN `'.$old.'` TO `'.$new.'`';
            }
        }
        if ($renames !== []) {
            DB::statement('ALTER TABLE `'.$to.'` '.implode(', ', $renames));
        }
    }

    /** @return array<string, array{0: string, 1: array<string, string>}> */
    private function map(): array
    {
        return require database_path('legacy/schema-map.php');
    }
};
