<?php

return [

    'scheduler' => [
        /*
         * Master switch for the scheduled tasks. Off: the container starts the web server only, and
         * a stray `schedule:run` does nothing. Each task can also be paused from the Health page.
         */
        'enabled' => (bool) env('SCHEDULER_ENABLED', true),
    ],

    'export' => [
        // where the scheduled spreadsheet export is written, and how many files are kept
        'path' => env('EXPORT_PATH', storage_path('app/private/exports')),
        'keep' => (int) env('EXPORT_MAX_BACKUPS', 14),
    ],

];
