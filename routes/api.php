<?php

use App\Api\Http\Controllers\BotController;
use App\Api\Http\Controllers\CatalogController;
use App\Api\Http\Controllers\HudController;
use App\Api\Http\Controllers\ObjectController;
use App\Api\Http\Controllers\OwnerController;
use App\Api\Http\Controllers\RentalController;
use App\Http\Controllers\MarketplaceNotification;
use Illuminate\Support\Facades\Route;

/*
 * In-world API, docs/api-v3.md. Every call is a signed POST with a JSON body (VerifySignature):
 * api.signed:{key classes that may call it}. The caller is identified (IdentifyCaller) inside a
 * transaction, so a call that ends in an error leaves nothing behind.
 */
Route::prefix('api/v3')->group(function () {
    // vendors, renewal points and the owner's own vendors
    Route::middleware(['api.signed:vendor,owner', 'api.transaction', 'api.caller'])->group(function () {
        Route::post('objects/ping', [ObjectController::class, 'ping']);

        Route::post('packages/status', [CatalogController::class, 'packageStatus']);
        Route::post('vendors/config', [CatalogController::class, 'vendorConfig']);
        Route::post('texture-packs/show', [CatalogController::class, 'texturePack']);
        Route::post('stock/levels', [CatalogController::class, 'stockLevels']);
        Route::post('tree-vendors/packages', [CatalogController::class, 'treePackages']);

        Route::post('rentals/start', [RentalController::class, 'start']);
        Route::post('rentals/renew', [RentalController::class, 'renew']);
        Route::post('rentals/cost', [RentalController::class, 'cost']);
        Route::post('rentals/list', [RentalController::class, 'list']);
        Route::post('rentals/find', [RentalController::class, 'find']);
        Route::post('rentals/send-details', [RentalController::class, 'sendDetails']);
        Route::post('clients/has-rental', [RentalController::class, 'hasRental']);
    });

    // the owner's own server objects
    Route::middleware(['api.signed:owner', 'api.transaction', 'api.caller'])->prefix('owner')->group(function () {
        Route::post('mail/next', [OwnerController::class, 'nextMail']);
        Route::post('events/next', [OwnerController::class, 'nextEvent']);
        Route::post('details/process', [OwnerController::class, 'processDetails']);
        Route::post('bot-commands/next', [OwnerController::class, 'nextBotCommand']);
        Route::post('notices/next', [OwnerController::class, 'nextNotice']);
        Route::post('notecards/next', [OwnerController::class, 'nextNotecardMail']);
        Route::post('notecards/sync', [OwnerController::class, 'syncNotecards']);
        Route::post('bot/notecards/sync', [OwnerController::class, 'syncBotNotecards']);
        Route::post('support-code', [OwnerController::class, 'supportCode']);
    });

    // the renter HUD, worn by the client
    Route::middleware(['api.signed:hud', 'api.transaction', 'api.caller'])->prefix('hud')->group(function () {
        Route::post('config', [HudController::class, 'config']);
        Route::post('rentals', [HudController::class, 'rentals']);
        Route::post('rentals/cost', [HudController::class, 'cost']);
        Route::post('rentals/renew', [HudController::class, 'renew']);
        Route::post('rentals/send-details', [HudController::class, 'sendDetails']);
    });

    // the Second Life bot
    Route::middleware(['api.signed:bot', 'api.transaction'])->prefix('bot')->group(function () {
        Route::post('notecards/next', [BotController::class, 'nextNotecard']);
    });
});

// Linden Marketplace Automated Notification, Linden's own protocol
Route::get('ans.php', MarketplaceNotification::class);
