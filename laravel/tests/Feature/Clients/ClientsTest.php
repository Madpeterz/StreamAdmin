<?php

namespace Tests\Feature\Clients;

use App\Livewire\Clients\Show;
use App\Models\AuditLog;
use App\Models\DetailRequest;
use App\Models\Rental;
use App\Models\RentalNoticeOptOut;
use App\Models\Stream;
use App\Services\NoticeLevels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class ClientsTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    public function test_list_and_detail_pages_render(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Listed Client'));

        $this->get('/clients')->assertOk()->assertSee('Listed Client');
        $this->get('/clients/'.$rental->rental_uid)->assertOk()->assertSee('Listed Client')->assertSee($rental->rental_uid);
    }

    public function test_list_filters_by_status_from_the_query_string(): void
    {
        $this->rental(hoursLeft: 200, avatar: $this->avatar('Fine Client'));
        $this->rental(hoursLeft: -5, avatar: $this->avatar('Lapsed Client'));

        $this->get('/clients?status=expired')->assertOk()->assertSee('Lapsed Client')->assertDontSee('Fine Client');
    }

    public function test_adding_time_updates_expiry_note_and_audit_log(): void
    {
        $rental = $this->rental(hoursLeft: 10);
        $before = $rental->expire_unixtime->getTimestamp();

        Livewire::test(Show::class, ['rental' => $rental])
            ->set('adjustDays', 2)->set('adjustHours', 3)->set('adjustDirection', 'add')
            ->call('adjustTime')
            ->assertHasNoErrors();

        $rental->refresh();
        $this->assertSame($before + 51 * 3600, $rental->expire_unixtime->getTimestamp());
        $this->assertStringContainsString('Added 2 days', $rental->message);
        $this->assertSame((string) $before, AuditLog::where('value_name', 'expire_unixtime')->value('old_value'));
    }

    public function test_adjustment_needs_some_time(): void
    {
        Livewire::test(Show::class, ['rental' => $this->rental()])->call('adjustTime')->assertHasErrors('adjustDays');
    }

    public function test_transfer_to_another_avatar(): void
    {
        $rental = $this->rental();
        $to = $this->avatar('New Owner');

        Livewire::test(Show::class, ['rental' => $rental])->set('transferUid', 'nope1234')->call('transfer')->assertHasErrors('transferUid');
        Livewire::test(Show::class, ['rental' => $rental])->set('transferUid', $to->avatar_uid)->call('transfer')->assertHasNoErrors();

        $this->assertSame($to->id, $rental->refresh()->avatar_id);
        $this->assertStringContainsString('Transfer to: New Owner', $rental->message);
    }

    public function test_revoke_releases_the_stream(): void
    {
        $rental = $this->rental();
        $streamId = $rental->stream_id;

        Livewire::test(Show::class, ['rental' => $rental])->call('revoke')->assertRedirect(route('clients.index'));

        $this->assertNull(Rental::find($rental->id));
        $stream = Stream::find($streamId);
        $this->assertNull($stream->rental_id);
        $this->assertTrue($stream->need_work);
    }

    public function test_revoke_is_blocked_by_a_pending_details_request(): void
    {
        $rental = $this->rental();
        DetailRequest::create(['rental_id' => $rental->id]);

        Livewire::test(Show::class, ['rental' => $rental])->call('revoke')->assertNoRedirect();

        $this->assertNotNull(Rental::find($rental->id));
    }

    public function test_notice_opt_outs_are_added_and_removed(): void
    {
        $rental = $this->rental();
        RentalNoticeOptOut::create(['rental_id' => $rental->id, 'notice_id' => 1]);

        Livewire::test(Show::class, ['rental' => $rental])
            ->assertSet('optOuts', ['1'])
            ->set('optOuts', ['3', '6', (string) NoticeLevels::ACTIVE])
            ->call('saveOptOuts');

        $this->assertSame([3, 6], $rental->noticeOptOuts()->orderBy('notice_id')->pluck('notice_id')->all(), 'Active cannot be skipped');
        $this->assertTrue(AuditLog::where('value_name', 'updated notice opt-outs')->where('source_id', $rental->rental_uid)->exists());

        Livewire::test(Show::class, ['rental' => $rental])->set('optOuts', [])->call('saveOptOuts');
        $this->assertSame(0, $rental->noticeOptOuts()->count());
    }
}
