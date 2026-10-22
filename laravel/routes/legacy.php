<?php

use App\LegacyApi\Gateway;
use Illuminate\Support\Facades\Route;

/*
 * In-world entry points, unchanged from 2.0.x so vendors, HUDs, the bot and Marketplace keep
 * working (docs/lsl-contract.md). No web middleware: no sessions, cookies or CSRF.
 */
foreach (['api', 'hud', 'sys', 'ans'] as $entry) {
    Route::match(['GET', 'POST'], $entry.'.php/{path?}', [Gateway::class, $entry])->where('path', '.*');
}
