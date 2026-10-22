<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\SpreadsheetExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Owners download a fresh stream spreadsheet (2.0.x Export/Flow1). It holds every stream login,
 * so each download is written to the audit log.
 */
class DownloadExport extends Controller
{
    public function __invoke(SpreadsheetExport $export, AuditLogger $audit): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'sa-export');
        $export->write($path);
        $audit->log('Export', null, 'download', null, 'stream spreadsheet');

        return response()->download($path, $export->filename(), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }
}
