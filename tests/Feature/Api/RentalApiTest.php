<?php

namespace Tests\Feature\Api;

use App\Api\ApiClass;
use App\Models\Avatar;
use App\Models\Ban;
use App\Models\DetailRequest;
use App\Models\QueuedEvent;
use App\Models\Rental;
use App\Models\SlConfig;
use App\Models\Stream;
use App\Models\Transaction;
use App\Services\NoticeLevels;
use App\Support\Swaps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\CreatesStreamAdminData;
use Tests\Support\SignsApiRequests;
use Tests\TestCase;

/** Vendors and renewal points: buying, renewing and looking up rentals. */
class RentalApiTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;
    use SignsApiRequests;

    /** A stream of package pkgTest1 (L$700 for 7 days) that nobody rents. */
    private function freeStream(): Stream
    {
        $rental = $this->rental();
        $stream = $rental->stream;
        $stream->update(['rental_id' => null]);
        $rental->delete();

        return $stream->refresh();
    }

    /** @return array<string, mixed> */
    private function buyer(string $name = 'Happy Client'): array
    {
        return ['key' => (string) Str::uuid(), 'name' => rawurlencode($name)];
    }

    private function start(array $buyer, int $paid = 700, ApiClass $class = ApiClass::Vendor, string $package = 'pkgTest1')
    {
        return $this->api('api/v3/rentals/start', ['packageUid' => $package, 'amountPaid' => $paid, 'avatar' => $buyer], $class);
    }

    public function test_a_payment_starts_a_rental_and_tells_the_vendor_what_to_pay_the_owner(): void
    {
        $stream = $this->freeStream();
        $buyer = $this->buyer();
        $systemOwner = Avatar::findOrFail(SlConfig::findOrFail(1)->owner_avatar_id);

        $response = $this->start($buyer, 700)->assertOk()->assertJson(['ok' => true, 'message' => 'Details should be with you shortly']);

        // the reseller keeps 10% (70), 630 goes to the owner
        $response->assertJsonPath('data.ownerPayment', ['required' => true, 'amount' => 630, 'key' => $systemOwner->avatar_uuid])
            ->assertJsonPath('data.credit', ['returned' => 0, 'remaining' => 0]);
        $rental = Rental::where('rental_uid', $response->json('data.rentalUid'))->sole();
        $this->assertSame($stream->id, $rental->stream_id);
        $this->assertSame($rental->id, $stream->refresh()->rental_id);
        $this->assertSame('Happy Client', $rental->avatar->avatar_name);
        $this->assertEqualsWithDelta(time() + 7 * 86400, $rental->expire_unixtime->getTimestamp(), 5);
        $this->assertSame(1, DetailRequest::where('rental_id', $rental->id)->count());
        $sale = Transaction::sole();
        $this->assertSame(700, (int) $sale->amount);
        $this->assertFalse((bool) $sale->renew);
        $this->assertSame('Test Region', $sale->region->name);
    }

    public function test_the_owners_own_vendor_passes_nothing_on_and_gets_credit_refunds(): void
    {
        $this->freeStream();
        $buyer = $this->buyer('Credit Client');
        $avatar = Avatar::create(['avatar_uuid' => $buyer['key'], 'avatar_name' => 'Credit Client', 'avatar_uid' => 'cred0001', 'credits' => 1000]);

        $this->start($buyer, 700, ApiClass::Owner)->assertOk()
            ->assertJsonPath('data.ownerPayment.required', false)
            ->assertJsonPath('data.credit', ['returned' => 700, 'remaining' => 300]);

        $this->assertSame(300, (int) $avatar->refresh()->credits);
        $this->assertSame([700, -700], Transaction::orderBy('id')->pluck('amount')->map(fn ($amount) => (int) $amount)->all());
    }

    public function test_the_owners_share_follows_the_resellers_rate(): void
    {
        // rate %, paid, what the owner gets: a cut under L$1 is rounded in the reseller's favour
        foreach ([[10, 700, 630], [10, 2, 1], [0, 700, 700], [100, 700, 0], [50, 700, 350]] as [$rate, $paid, $owner]) {
            $this->freeStream()->package->update(['cost' => $paid]);
            $reseller = $this->reseller($rate, Avatar::create(['avatar_uuid' => (string) Str::uuid(), 'avatar_name' => 'Rate '.$rate.'-'.$paid, 'avatar_uid' => Str::random(8), 'credits' => 0]));

            $this->api('api/v3/rentals/start', ['packageUid' => 'pkgTest1', 'amountPaid' => $paid, 'avatar' => $this->buyer()], body: ['owner' => $reseller])
                ->assertOk()->assertJsonPath('data.ownerPayment.amount', $owner);
        }
    }

    public function test_payments_that_do_not_fit_are_refused_and_leave_nothing_behind(): void
    {
        $stream = $this->freeStream();
        $buyer = $this->buyer();

        $this->start($buyer, 701)->assertStatus(422)->assertJson(['ok' => false, 'code' => 'rejected', 'message' => 'Payment amount not accepted']);
        $this->start($buyer, 5 * 700)->assertStatus(422);
        $this->start($buyer, 700, package: 'nopackage')->assertStatus(404)->assertJson(['code' => 'not_found']);
        $this->start($buyer, 0)->assertStatus(422)->assertJson(['code' => 'validation']);

        $this->assertNull($stream->refresh()->rental_id);
        $this->assertSame(0, Transaction::count());
        $this->assertNull(Avatar::where('avatar_uuid', $buyer['key'])->first(), 'the buyer is not kept either');

        $this->start($buyer, 2 * 700)->assertOk();
        $this->assertEqualsWithDelta(time() + 14 * 86400, Rental::sole()->expire_unixtime->getTimestamp(), 5);
    }

    public function test_nothing_in_stock_banned_buyers_and_stream_limits(): void
    {
        $buyer = $this->buyer();
        $this->start($buyer)->assertStatus(404);

        $stream = $this->freeStream();
        $stream->update(['need_work' => true]);
        $this->start($buyer)->assertStatus(422)->assertJson(['message' => 'Nothing in stock for that package']);
        $stream->update(['need_work' => false]);

        $avatar = Avatar::create(['avatar_uuid' => $buyer['key'], 'avatar_name' => 'Happy Client', 'avatar_uid' => 'happy001', 'credits' => 0]);
        $ban = Ban::create(['avatar_id' => $avatar->id]);
        $this->start($buyer)->assertStatus(422)->assertJson(['message' => 'This avatar is banned']);
        $ban->delete();

        SlConfig::whereKey(1)->update(['limit_streams' => true, 'max_total_streams' => 1]);
        $this->start($buyer)->assertOk();
        $this->freeStreamOf(2);
        $this->start($buyer)->assertStatus(422)->assertJson(['message' => 'This client has reached the stream limit']);
    }

    private function freeStreamOf(int $count): void
    {
        $stream = Stream::first();
        for ($i = 0; $i < $count; $i++) {
            Stream::create([
                'server_id' => $stream->server_id, 'package_id' => $stream->package_id, 'port' => random_int(10000, 19999),
                'admin_username' => 'admin', 'admin_password' => 'pw', 'dj_password' => 'dj', 'stream_uid' => Str::random(8), 'mountpoint' => '/live',
            ]);
        }
    }

    public function test_renewals_add_periods_and_record_the_payment(): void
    {
        $rental = $this->rental(hoursLeft: 24);
        $before = $rental->expire_unixtime->getTimestamp();
        $payer = $this->identityOf($rental->avatar);

        $response = $this->api('api/v3/rentals/renew', ['rentalUid' => $rental->rental_uid, 'amountPaid' => 1400, 'payer' => $payer])
            ->assertOk()->assertJson(['message' => 'Payment accepted'])
            ->assertJsonPath('data.inArrears', false)
            ->assertJsonPath('data.ownerPayment.required', true);

        $rental->refresh();
        $this->assertSame($before + 14 * 86400, $rental->expire_unixtime->getTimestamp());
        $this->assertSame(2, (int) $rental->renewals);
        $response->assertJsonPath('data.expiresAt', $rental->expire_unixtime->getTimestamp());
        $sale = Transaction::sole();
        $this->assertTrue((bool) $sale->renew);
        $this->assertSame(1400, (int) $sale->amount);
        $this->assertNull($sale->target_avatar_id);
    }

    public function test_a_renewal_paid_by_someone_else_names_the_account_and_a_lapsed_rental_is_flagged(): void
    {
        SlConfig::whereKey(1)->update(['events_api' => true]);
        $rental = $this->rental(hoursLeft: -24 * 30);
        $rental->update(['notice_id' => NoticeLevels::EXPIRED]);
        $friend = $this->buyer('Kind Friend');

        $this->api('api/v3/rentals/renew', ['rentalUid' => $rental->rental_uid, 'amountPaid' => 700, 'payer' => $friend])
            ->assertOk()->assertJson(['message' => 'Payment on account but account is still in arrears'])->assertJsonPath('data.inArrears', true);

        $sale = Transaction::sole();
        $this->assertSame($rental->avatar_id, $sale->target_avatar_id);
        $this->assertSame('Kind Friend', $sale->avatar->avatar_name);
        $this->assertSame('RentalRenewAny', QueuedEvent::sole()->event_name, 'still lapsed, so not a comeback');
    }

    public function test_renewal_amounts_and_the_time_limit_are_enforced(): void
    {
        $rental = $this->rental(hoursLeft: 24);
        $payer = $this->identityOf($rental->avatar);
        $renew = fn (int $paid) => $this->api('api/v3/rentals/renew', ['rentalUid' => $rental->rental_uid, 'amountPaid' => $paid, 'payer' => $payer]);

        $renew(699)->assertStatus(422)->assertJson(['message' => 'Payment not accepted (invalid amount)']);
        $this->api('api/v3/rentals/renew', ['rentalUid' => 'nope', 'amountPaid' => 700, 'payer' => $payer])->assertStatus(404);

        SlConfig::whereKey(1)->update(['limit_time' => true, 'max_stream_time_days' => 20]);
        $renew(700 * 3)->assertStatus(422)->assertJson(['message' => 'Payment not accepted: it would go over the time limit for this rental']);
        $renew(700 * 2)->assertOk();
        $rental->update(['expire_unixtime' => time() + 25 * 86400]);
        $renew(700)->assertStatus(422)->assertJson(['message' => 'Payment not accepted: there is already too much time on this rental'])
            ->assertJsonMissingPath('data');
    }

    public function test_banned_accounts_cannot_be_renewed(): void
    {
        $rental = $this->rental();
        Ban::create(['avatar_id' => $rental->avatar_id]);

        $this->api('api/v3/rentals/renew', ['rentalUid' => $rental->rental_uid, 'amountPaid' => 700, 'payer' => $this->buyer()])
            ->assertStatus(422)->assertJson(['message' => 'This avatar is banned']);
    }

    public function test_what_a_renewal_costs(): void
    {
        $rental = $this->rental(hoursLeft: 50);
        $owner = Avatar::findOrFail(SlConfig::findOrFail(1)->owner_avatar_id);

        $this->api('api/v3/rentals/cost', ['rentalUid' => $rental->rental_uid])->assertOk()
            ->assertJsonPath('data.systemOwnerKey', $owner->avatar_uuid)
            ->assertJsonPath('data.cost', 700)->assertJsonPath('data.word', 'Week')->assertJsonPath('data.stackSize', 1)
            ->assertJsonPath('data.timeLeft', Swaps::timeLeft($rental->expire_unixtime));
        $rental->package->update(['days' => 30]);
        $this->api('api/v3/rentals/cost', ['rentalUid' => $rental->rental_uid])->assertJsonPath('data.word', 'Month')->assertJsonPath('data.stackSize', 1);
        $rental->package->update(['days' => 3]);
        $this->api('api/v3/rentals/cost', ['rentalUid' => $rental->rental_uid])->assertJsonPath('data.word', 'Day')->assertJsonPath('data.stackSize', 3);
        $this->api('api/v3/rentals/cost', ['rentalUid' => 'nope'])->assertStatus(404);
    }

    public function test_an_avatars_rentals_by_key_and_by_name(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Rent Payer'));
        $other = $this->rental(avatar: $rental->avatar);
        $expected = [
            ['rentalUid' => $other->rental_uid, 'port' => (int) $other->stream->port, 'timeLeft' => '6 days, 23 hours'],
            ['rentalUid' => $rental->rental_uid, 'port' => (int) $rental->stream->port, 'timeLeft' => '6 days, 23 hours'],
        ];
        $this->assertStringContainsString('days', $expected[0]['timeLeft']);

        $byKey = $this->api('api/v3/rentals/list', ['avatarKey' => $rental->avatar->avatar_uuid])->assertOk()
            ->assertJson(['message' => 'Client account: Rent Payer'])->assertJsonPath('data.client', 'Rent Payer');
        $this->assertSame([$other->rental_uid, $rental->rental_uid], array_column($byKey->json('data.rentals'), 'rentalUid'), 'newest first');

        $this->api('api/v3/rentals/find', ['target' => rawurlencode('Rent Payer')])->assertOk()->assertJsonCount(2, 'data.rentals');
        $this->api('api/v3/rentals/find', ['target' => $rental->avatar->avatar_uuid])->assertOk()->assertJsonCount(2, 'data.rentals');
        $this->api('api/v3/rentals/find', ['target' => rawurlencode('Nobody Here')])->assertStatus(404);
        $this->api('api/v3/rentals/list', ['avatarKey' => (string) Str::uuid()])->assertStatus(404);

        $lonely = $this->avatar('No Rentals');
        $this->api('api/v3/rentals/list', ['avatarKey' => $lonely->avatar_uuid])->assertOk()->assertJson(['message' => 'No active rentals'])->assertJsonPath('data.rentals', []);
        Ban::create(['avatar_id' => $rental->avatar_id]);
        $this->api('api/v3/rentals/list', ['avatarKey' => $rental->avatar->avatar_uuid])->assertStatus(404);
    }

    public function test_details_requests_are_not_queued_twice(): void
    {
        $rental = $this->rental();

        $this->api('api/v3/rentals/send-details', ['rentalUid' => $rental->rental_uid])->assertOk()->assertJson(['message' => 'Details request accepted, it should be with you shortly']);
        $this->api('api/v3/rentals/send-details', ['rentalUid' => $rental->rental_uid])->assertOk()->assertJson(['message' => 'You already have a pending details request - please wait for it or contact support']);
        $this->assertSame(1, DetailRequest::count());
        $this->api('api/v3/rentals/send-details', ['rentalUid' => 'nope'])->assertStatus(404);
    }

    public function test_whether_an_avatar_rents_anything(): void
    {
        $rental = $this->rental();

        $this->api('api/v3/clients/has-rental', ['avatarKey' => $rental->avatar->avatar_uuid])->assertOk()->assertExactJson(['ok' => true, 'message' => 'some', 'data' => ['hasRental' => true]]);
        $this->api('api/v3/clients/has-rental', ['avatarKey' => $this->avatar('Free Loader')->avatar_uuid])->assertJsonPath('data.hasRental', false);
        $this->api('api/v3/clients/has-rental', ['avatarKey' => (string) Str::uuid()])->assertJsonPath('data.hasRental', false);
        $this->api('api/v3/clients/has-rental', ['avatarKey' => 'bad'])->assertStatus(422)->assertJson(['code' => 'validation']);
    }
}
