<?php

namespace Tests\Feature\System;

use App\Livewire\Health;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\Message;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Services\ObjectHealth;
use App\Services\Scheduler;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    public function test_a_queue_task_runs_and_counts_as_its_service_being_online(): void
    {
        BotCommand::create(['command' => 'IM', 'args' => json_encode(['a', 'b']), 'unixtime' => time()]);

        $this->artisan('streamadmin:task botcommandq')->expectsOutput('botcommandq: passed command to mail server')->assertSuccessful();

        $bot = Avatar::find(1);
        $this->assertSame(1, Message::where('avatar_id', $bot->id)->count());
        $this->assertSame(0, SlObject::count(), 'no virtual objects are created');
        $this->assertTrue(app(Scheduler::class)->lastRun('botcommandq')['status']);
        $this->assertNotNull(app(Scheduler::class)->lastPassed('botcommandqserver'));
        $this->assertNull(app(Scheduler::class)->lastPassed('detailsserver'));
    }

    public function test_a_task_that_stopped_running_shows_as_failed(): void
    {
        $scheduler = app(Scheduler::class);
        $this->travelTo(now()->subMinutes(9));
        $scheduler->record('details-server', true, 'nowork');
        $scheduler->record('export', true, 'Wrote a.xlsx');
        $this->travelBack();

        $queue = $scheduler->lastRun('details-server');
        $this->assertFalse($queue['status']);
        $this->assertTrue($queue['stale']);
        $this->assertStringContainsString('nowork', $queue['message']);
        $this->assertTrue($scheduler->lastRun('export')['status'], 'the daily export is fine after 9 minutes');

        $scheduler->record('details-server', true, 'nowork');
        $this->assertTrue($scheduler->lastRun('details-server')['status']);

        $this->actingAs($this->staff());
        $this->travelTo(now()->addMinutes(9));
        Livewire::test(Health\Index::class)->assertSee('Failed')->assertSee('No run since');
        SlConfig::whereKey(1)->update(['scheduler_details_server' => false]);
        Livewire::test(Health\Index::class)->assertSee('Paused')->assertDontSee('Failed');
        $this->travelBack();
    }

    public function test_details_and_notecard_tasks_run_their_service(): void
    {
        $this->artisan('streamadmin:task details-server')->expectsOutput('details-server: nowork')->assertSuccessful();
        $this->assertNotNull(app(Scheduler::class)->lastPassed('detailsserver'));

        // notecards are off on the bot, so the task fails the way the endpoint did
        $this->artisan('streamadmin:task dynamic-notecards')->assertFailed();
        $this->assertSame('Notecards are not enabled on the bot', app(Scheduler::class)->lastRun('dynamic-notecards')['message']);
        $this->artisan('streamadmin:task nonsense')->assertFailed();
    }

    public function test_a_failed_run_is_reported(): void
    {
        BotCommand::create(['command' => 'IM', 'args' => '[]', 'unixtime' => time()]);
        Message::create(['avatar_id' => 1, 'message' => 'already waiting']);

        $this->artisan('streamadmin:task botcommandq')->assertFailed();

        $this->assertSame('Too many pending mail commands to the bot', app(Scheduler::class)->lastRun('botcommandq')['message']);
        $this->assertSame(1, BotCommand::count(), 'a failed run rolls back');
    }

    public function test_the_health_page_counts_a_running_task_as_its_server_and_a_paused_one_as_not(): void
    {
        $this->artisan('streamadmin:task details-server')->assertSuccessful();
        $health = fn () => app(ObjectHealth::class)->serverObjects()->firstWhere('mode', 'detailsserver');

        $this->assertSame('Scheduled task', $health()['region']);
        $this->assertTrue(ObjectHealth::isOnline($health()['last_seen']));

        SlConfig::whereKey(1)->update(['scheduler_details_server' => false]);
        $this->assertNull($health()['last_seen']);
    }

    public function test_the_master_switch_and_pause_switches_gate_the_schedule(): void
    {
        config(['streamadmin.scheduler.enabled' => true]); // the container's SCHEDULER_ENABLED must not decide this test
        $due = fn (string $command) => collect(app(Schedule::class)->events())
            ->first(fn (Event $event) => str_contains((string) $event->command, $command))
            ->filtersPass($this->app);

        $this->assertTrue($due('streamadmin:task botcommandq'));
        $this->assertFalse($due('streamadmin:export'), 'export starts paused');

        SlConfig::whereKey(1)->update(['scheduler_botcommandq' => false]);
        $this->assertFalse($due('streamadmin:task botcommandq'));
        $this->assertTrue($due('streamadmin:task details-server'));

        config(['streamadmin.scheduler.enabled' => false]);
        $this->assertFalse($due('streamadmin:task details-server'));
    }

    public function test_owners_pause_tasks_from_the_health_page(): void
    {
        $this->actingAs($this->staff());
        Livewire::test(Health\Index::class)->call('toggleTask', 'details-server')->assertOk();
        $this->assertFalse(SlConfig::find(1)->scheduler_details_server);

        $this->actingAs($this->staff(owner: false));
        Livewire::test(Health\Index::class)->call('toggleTask', 'details-server')->assertForbidden();
    }

    public function test_the_export_keeps_the_newest_files(): void
    {
        $this->rental();
        $dir = sys_get_temp_dir().'/sa-export-test-'.uniqid();
        config(['streamadmin.export.path' => $dir, 'streamadmin.export.keep' => 2]);
        File::ensureDirectoryExists($dir);
        foreach (['20200101000000', '20200102000000', '20200103000000'] as $old) {
            touch("{$dir}/{$old}-streamadmin.xlsx");
        }

        $this->artisan('streamadmin:export')->assertSuccessful();

        $files = collect(File::glob("{$dir}/*.xlsx"))->map(fn (string $path) => basename($path))->sort()->values();
        $this->assertCount(2, $files);
        $this->assertSame('20200103000000-streamadmin.xlsx', $files[0]);

        $reader = new Reader;
        $reader->open("{$dir}/{$files[1]}");
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
        }
        $reader->close();
        File::deleteDirectory($dir);

        $this->assertSame('Customer Name', $rows[0][1]);
        $this->assertSame('Test Client', $rows[1][1]);
        $this->assertSame('djpass', $rows[1][3]);
    }

    public function test_only_owners_download_the_export(): void
    {
        $this->actingAs($this->staff(owner: false))->get(route('export.download'))->assertForbidden();
        $this->flushSession();
        $this->actingAs($this->staff())->get(route('export.download'))->assertOk()->assertDownload();
        $this->assertDatabaseHas('audit_logs', ['store' => 'Export']);
    }
}
