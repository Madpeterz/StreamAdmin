<?php

namespace Tests\Feature\Clients;

use App\Livewire\Clients\BulkRemove;
use App\Livewire\Clients\Create;
use App\Livewire\Clients\Show;
use App\Models\AuditLog;
use App\Models\DetailRequest;
use App\Models\QueuedEvent;
use App\Models\Region;
use App\Models\Rental;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Models\Stream;
use App\Models\Transaction;
use App\Services\NoticeLevels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

/**
 * Client pages that came over from the 2.0.x Client/Create, Bulkremove, Getnotecard and Resend
 * controls, and the search page.
 */
class ClientActionsTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    private function freeStream(): Stream
    {
        $rental = $this->rental();
        $stream = $rental->stream;
        $stream->update(['rental_id' => null, 'need_work' => true]);
        $rental->delete();

        return $stream->refresh();
    }

    public function test_adding_a_client_by_hand_creates_rental_and_free_transaction(): void
    {
        $staff = $this->actingAs($this->staff())->app['auth']->user();
        $stream = $this->freeStream();
        $avatar = $this->avatar('Gift Receiver');

        Livewire::test(Create::class)
            ->set('avatarUid', $avatar->avatar_uid)->set('streamUid', $stream->stream_uid)->set('days', 14)
            ->call('save')->assertHasNoErrors();

        $rental = Rental::where('avatar_id', $avatar->id)->sole();
        $this->assertSame($rental->id, $stream->refresh()->rental_id);
        $this->assertFalse($stream->need_work);
        $this->assertEqualsWithDelta(time() + 14 * 86400, $rental->expire_unixtime->getTimestamp(), 5);
        $this->assertNotSame(NoticeLevels::EXPIRED, $rental->notice_id);

        $transaction = Transaction::where('avatar_id', $avatar->id)->sole();
        $this->assertSame(0, $transaction->amount);
        $this->assertFalse($transaction->renew);
        $this->assertSame('website', Region::find($transaction->region_id)->name);
        $this->assertSame($staff->avatar_id, $transaction->reseller->avatar_id);
        $this->assertTrue(AuditLog::where('value_name', '+++')->where('source_id', $rental->rental_uid)->exists());
    }

    public function test_a_rented_stream_cannot_be_given_out_again(): void
    {
        $this->actingAs($this->staff());
        $rented = $this->rental();

        Livewire::test(Create::class)
            ->set('avatarUid', $this->avatar()->avatar_uid)->set('streamUid', $rented->stream->stream_uid)->set('days', 7)
            ->call('save')->assertHasErrors('streamUid');
    }

    public function test_bulk_remove_only_takes_expired_clients_with_nothing_waiting(): void
    {
        $this->actingAs($this->staff());
        SlConfig::whereKey(1)->update(['events_api' => true]);
        $gone = $this->rental(hoursLeft: -48, avatar: $this->avatar('Long Gone'));
        $gone->update(['notice_id' => NoticeLevels::EXPIRED]);
        $gone->noticeOptOuts()->create(['notice_id' => 2]);
        $waiting = $this->rental(hoursLeft: -48, avatar: $this->avatar('Waiting For Details'));
        $waiting->update(['notice_id' => NoticeLevels::EXPIRED]);
        DetailRequest::create(['rental_id' => $waiting->id]);
        $active = $this->rental(hoursLeft: 100, avatar: $this->avatar('Still Paid Up'));

        Livewire::test(BulkRemove::class)
            ->assertSee('Long Gone')->assertDontSee('Waiting For Details')->assertDontSee('Still Paid Up')
            ->set('selected', [$gone->rental_uid, $waiting->rental_uid, $active->rental_uid])
            ->call('remove');

        $this->assertNull(Rental::find($gone->id));
        $this->assertNotNull(Rental::find($waiting->id));
        $this->assertNotNull(Rental::find($active->id));
        $freed = $gone->stream->refresh();
        $this->assertNull($freed->rental_id);
        $this->assertTrue($freed->need_work);
        $this->assertDatabaseMissing('rental_notice_opt_outs', ['rental_id' => $gone->id]);
        $this->assertSame('RentalEnd', QueuedEvent::sole()->event_name);
    }

    public function test_details_can_be_previewed_and_sent_again_once(): void
    {
        $this->actingAs($this->staff());
        $rental = $this->rental();

        $component = Livewire::test(Show::class, ['rental' => $rental])
            ->assertSee('Stream details')->assertSee('Send to client again')
            ->call('resendDetails');

        $this->assertSame(1, DetailRequest::where('rental_id', $rental->id)->count());
        $this->assertTrue(AuditLog::where('value_name', 'resend details')->where('source_id', $rental->rental_uid)->exists());

        $component->call('resendDetails');
        $this->assertSame(1, DetailRequest::where('rental_id', $rental->id)->count(), 'a second request is not queued');
    }

    public function test_transfer_picker_finds_avatars_by_name(): void
    {
        $this->actingAs($this->staff());
        $rental = $this->rental();
        $target = $this->avatar('Picker Target');

        Livewire::test(Show::class, ['rental' => $rental])
            ->set('avatarSearch', 'Picker Tar')->assertSee('Picker Target')
            ->call('pickAvatar', $target->avatar_uid)
            ->assertSet('transferUid', $target->avatar_uid)
            ->call('transfer');

        $this->assertSame($target->id, $rental->refresh()->avatar_id);
    }

    public function test_search_finds_clients_streams_and_avatars(): void
    {
        $this->actingAs($this->staff());
        $rental = $this->rental(avatar: $this->avatar('Needle Person'));
        $rental->update(['message' => 'paid by cheque']);
        $this->rental(avatar: $this->avatar('Someone Else'));

        $this->get('/search?q=Needle')->assertOk()->assertSee('Needle Person')->assertDontSee('Someone Else');
        $this->get('/search?q=cheque')->assertOk()->assertSee('Needle Person');
        $this->get('/search?q='.$rental->stream->port)->assertOk()->assertSee('Needle Person');
        $this->get('/search?q=ab')->assertOk()->assertSee('3 or more letters');
        $this->get('/search?q=zzzzzzzz')->assertOk()->assertSee('Nothing found');
    }

    public function test_region_and_object_names_read_back_decoded_but_stay_stored_as_posted(): void
    {
        $region = Region::create(['name' => 'Nova&#039;s Isle']);
        $object = SlObject::create([
            'avatar_id' => 1, 'region_id' => $region->id, 'object_uuid' => (string) Str::uuid(), 'object_name' => 'Caf&eacute; vendor',
            'object_mode' => 'vendor', 'object_xyz' => '128,128,25', 'last_seen' => time(),
        ]);

        $this->assertSame("Nova's Isle", $region->refresh()->name);
        $this->assertSame('Nova&#039;s Isle', \DB::table('regions')->where('id', $region->id)->value('name'));
        $this->assertSame('Café vendor', $object->refresh()->object_name);
        $this->assertSame('Caf&eacute; vendor', \DB::table('sl_objects')->where('id', $object->id)->value('object_name'));
        $this->assertSame($region->id, Region::firstOrCreate(['name' => 'Nova&#039;s Isle'])->id, 'the in-world lookup by posted value still finds it');
    }
}
