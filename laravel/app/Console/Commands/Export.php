<?php

namespace App\Console\Commands;

use App\Services\Scheduler;
use App\Services\SpreadsheetExport;
use Illuminate\Console\Command;

/**
 * The stream spreadsheet export (2.0.x Export/Flow1). Without --path it writes into the export folder
 * and keeps the newest EXPORT_MAX_BACKUPS files, as the scheduled export does.
 */
class Export extends Command
{
    protected $signature = 'streamadmin:export {--path= : write to this file instead of the export folder}';

    protected $description = 'Export every stream with its client and logins to a spreadsheet';

    public function handle(SpreadsheetExport $export, Scheduler $scheduler): int
    {
        $path = $this->option('path');
        if (is_string($path) && $path !== '') {
            $export->write($path);
        } else {
            $path = $export->rotate(config('streamadmin.export.path'), config('streamadmin.export.keep'));
        }
        $scheduler->record('export', true, 'Wrote '.basename($path));
        $this->line('Export written to '.$path);

        return self::SUCCESS;
    }
}
