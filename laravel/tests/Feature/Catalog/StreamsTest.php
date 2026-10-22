<?php

namespace Tests\Feature\Catalog;

use App\Livewire\Streams\Edit;
use App\Livewire\Streams\NeedsWork;
use App\Models\Rental;
use App\Models\Stream;
use App\Models\Transaction;
use App\Services\RentalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class StreamsTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    public function test_list_filters_by_status_from_the_query_string(): void
    {
        $rented = $this->rental()->stream;
        $free = $rented->replicate(['rental_id'])->fill(['port' => 7001, 'stream_uid' => 'freeOne1', 'rental_id' => null]);
        $free->save();

        $this->get('/streams?status=ready')->assertOk()->assertSee('freeOne1')->assertDontSee($rented->stream_uid);
    }

    public function test_create_streams_one_port_after_another(): void
    {
        $rental = $this->rental();
        $server = $rental->stream->server;

        Livewire::test(Edit::class)
            ->set('server_id', $server->id)->set('package_id', $rental->package_id)->set('port', 9100)
            ->set('admin_username', 'admin')->set('admin_password', 'adminpw')->set('dj_password', 'djpass')
            ->call('saveAndNext')->assertHasNoErrors()
            ->assertSet('port', 9101)
            ->set('admin_password', 'adminpw2')->set('dj_password', 'djpass2')
            ->call('save')->assertHasNoErrors();

        $this->assertSame(2, Stream::where('server_id', $server->id)->whereIn('port', [9100, 9101])->count());
    }

    public function test_port_must_be_free_on_that_server(): void
    {
        $stream = $this->rental()->stream;

        Livewire::test(Edit::class)
            ->set('server_id', $stream->server_id)->set('package_id', $stream->package_id)->set('port', $stream->port)
            ->set('admin_username', 'admin')->set('admin_password', 'adminpw')->set('dj_password', 'djpass')
            ->call('save')->assertHasErrors('port');
    }

    public function test_moving_a_rented_stream_to_another_package_moves_the_rental(): void
    {
        $rental = $this->rental();
        $other = $rental->package->replicate()->fill(['package_uid' => 'pkgOther', 'name' => 'Other package']);
        $other->save();

        Livewire::test(Edit::class, ['stream' => $rental->stream])->set('package_id', $other->id)->call('save')->assertHasNoErrors();

        $this->assertSame($other->id, $rental->refresh()->package_id);
    }

    public function test_rented_stream_cannot_be_removed(): void
    {
        $stream = $this->rental()->stream;

        Livewire::test(Edit::class, ['stream' => $stream])->call('delete')->assertNoRedirect();
        $this->assertNotNull(Stream::find($stream->id));
    }

    public function test_restore_gives_the_stream_back_to_the_newest_buyer(): void
    {
        $rental = $this->rental();
        $stream = $rental->stream;
        $first = $this->avatar('First Buyer');
        $latest = $rental->avatar;
        foreach ([[$first, time() - 9000], [$latest, time() - 100]] as [$avatar, $when]) {
            Transaction::create([
                'avatar_id' => $avatar->id, 'stream_id' => $stream->id, 'package_id' => $stream->package_id,
                'amount' => 700, 'unixtime' => $when, 'transaction_uid' => Str::random(8),
            ]);
        }
        app(RentalService::class)->revoke($rental);

        Livewire::test(Edit::class, ['stream' => $stream->refresh()])->call('restore');

        $restored = Rental::where('stream_id', $stream->id)->firstOrFail();
        $this->assertSame($latest->id, $restored->avatar_id);
        $this->assertFalse($stream->refresh()->need_work);
    }

    public function test_needs_work_streams_are_marked_ready_in_bulk(): void
    {
        $rental = $this->rental();
        $stream = $rental->stream;
        app(RentalService::class)->revoke($rental);
        $unchanged = $stream->replicate()->fill(['port' => 7002, 'stream_uid' => 'workTwo2', 'need_work' => true, 'rental_id' => null]);
        $unchanged->save();

        Livewire::test(NeedsWork::class)
            ->set('passwords', [
                $stream->id => ['admin' => 'newadmin', 'dj' => 'newdj'],
                $unchanged->id => ['admin' => 'N/A', 'dj' => $unchanged->dj_password],
            ])
            ->call('markReady');

        $stream->refresh();
        $this->assertFalse($stream->need_work);
        $this->assertSame('newdj', $stream->dj_password);
        $this->assertTrue($unchanged->refresh()->need_work, 'unchanged DJ password is refused');
    }
}
