<?php

use App\Http\Controllers\DownloadExport;
use App\Livewire\AuditLog;
use App\Livewire\Avatars;
use App\Livewire\Bans;
use App\Livewire\Bot;
use App\Livewire\Clients;
use App\Livewire\Coupons;
use App\Livewire\Dashboard;
use App\Livewire\Health;
use App\Livewire\Notices;
use App\Livewire\Outbox;
use App\Livewire\Packages;
use App\Livewire\Reports;
use App\Livewire\Resellers;
use App\Livewire\Search;
use App\Livewire\Servers;
use App\Livewire\Staff;
use App\Livewire\Streams;
use App\Livewire\SystemSettings;
use App\Livewire\Templates;
use App\Livewire\TexturePacks;
use App\Livewire\Transactions;
use App\Livewire\TreeVendors;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', 'dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('clients', Clients\Index::class)->name('clients.index');
    Route::get('clients/create', Clients\Create::class)->name('clients.create');
    Route::get('clients/bulk-remove', Clients\BulkRemove::class)->name('clients.bulk-remove');
    Route::get('clients/{rental:rental_uid}', Clients\Show::class)->name('clients.show');
    Route::get('search', Search::class)->name('search');

    Route::get('streams', Streams\Index::class)->name('streams.index');
    Route::get('streams/create', Streams\Edit::class)->name('streams.create');
    Route::get('streams/needs-work', Streams\NeedsWork::class)->name('streams.needs-work');
    Route::get('streams/{stream:stream_uid}', Streams\Edit::class)->name('streams.edit');

    Route::get('packages', Packages\Index::class)->name('packages.index');
    Route::get('packages/create', Packages\Edit::class)->name('packages.create');
    Route::get('packages/{package:package_uid}', Packages\Edit::class)->name('packages.edit');

    Route::get('servers', Servers\Index::class)->name('servers.index');

    Route::get('transactions', Transactions\Index::class)->name('transactions.index');
    Route::get('reports', Reports::class)->name('reports.index');
    Route::get('resellers', Resellers\Index::class)->name('resellers.index');
    Route::get('tree-vendors', TreeVendors\Index::class)->name('tree-vendors.index');
    Route::get('tree-vendors/{vendor}', TreeVendors\Edit::class)->name('tree-vendors.edit');
    Route::get('coupons', Coupons\Index::class)->name('coupons.index');

    Route::get('outbox', Outbox\Index::class)->name('outbox.index');
    Route::get('outbox/compose', Outbox\Compose::class)->name('outbox.compose');
    Route::get('notices', Notices\Index::class)->name('notices.index');
    Route::get('notices/create', Notices\Edit::class)->name('notices.create');
    Route::get('notices/{notice}', Notices\Edit::class)->name('notices.edit');
    Route::get('templates', Templates\Index::class)->name('templates.index');
    Route::get('templates/create', Templates\Edit::class)->name('templates.create');
    Route::get('templates/{template}', Templates\Edit::class)->name('templates.edit');

    Route::get('health', Health\Index::class)->name('health.index');
    Route::get('avatars', Avatars\Index::class)->name('avatars.index');
    Route::get('avatars/create', Avatars\Show::class)->name('avatars.create');
    Route::get('avatars/{avatar:avatar_uid}', Avatars\Show::class)->name('avatars.show');
    Route::get('texture-packs', TexturePacks\Index::class)->name('texture-packs.index');
    Route::get('texture-packs/create', TexturePacks\Edit::class)->name('texture-packs.create');
    Route::get('texture-packs/{pack}', TexturePacks\Edit::class)->name('texture-packs.edit');

    Route::middleware('can:owner')->group(function () {
        Route::get('bot', Bot\Settings::class)->name('bot.index');
        Route::get('bans', Bans\Index::class)->name('bans.index');
        Route::get('audit-log', AuditLog\Index::class)->name('audit-log.index');
        Route::get('staff', Staff\Index::class)->name('staff.index');
        Route::get('system-settings', SystemSettings::class)->name('system-settings.index');
        Route::get('export', DownloadExport::class)->name('export.download');
    });

    Route::redirect('settings', 'settings/password');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
