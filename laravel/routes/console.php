<?php

use App\Services\Scheduler;
use Illuminate\Support\Facades\Schedule;

/*
 * Replaces the 2.0.x cron container. `schedule:work` runs inside the app container when
 * SCHEDULER_ENABLED is on; each task can be paused from the Health page. Locks live in the shared
 * cache (Valkey), so several app containers never run the same task twice.
 */
$task = fn (string $key) => fn () => app(Scheduler::class)->enabled($key);

Schedule::command('streamadmin:task botcommandq')->everyFiveSeconds()
    ->when($task('botcommandq'))->withoutOverlapping(1)->onOneServer()->runInBackground();

Schedule::command('streamadmin:task details-server')->everyTenSeconds()
    ->when($task('details-server'))->withoutOverlapping(1)->onOneServer()->runInBackground();

Schedule::command('streamadmin:task dynamic-notecards')->everyTenSeconds()
    ->when($task('dynamic-notecards'))->withoutOverlapping(1)->onOneServer()->runInBackground();

Schedule::command('streamadmin:export')->dailyAt('03:00')
    ->when($task('export'))->withoutOverlapping()->onOneServer();
