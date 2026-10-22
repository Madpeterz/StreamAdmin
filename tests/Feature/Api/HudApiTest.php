<?php

namespace Tests\Feature\Api;

use App\Api\ApiClass;
use App\Models\Avatar;
use App\Models\DetailRequest;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\CreatesStreamAdminData;
use Tests\Support\SignsApiRequests;
use Tests\TestCase;

/** The renter HUD: its wearer sees and pays only their own rentals. */
class HudApiTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;
    use SignsApiRequests;

    private function hud(string $path, array $data, Avatar $wearer)
    {
        return $this->api($path, $data, ApiClass::Hud, ['owner' => $this->identityOf($wearer), 'object' => ['region' => rawurlencode('Hud Region')]]);
    }

    private function enable(bool $details = true, bool $renewals = true): void
    {
        SlConfig::whereKey(1)->update(['hud_allow_details' => $details, 'hud_allow_renewal' => $renewals, 'hud_allow_discord' => true, 'hud_discord_link' => 'https://discord.gg/x']);
    }

    public function test_the_config_says_what_the_hud_offers(): void
    {
        $wearer = $this->avatar('Hud Wearer');
        $this->hud('api/v3/hud/config', [], $wearer)->assertOk()->assertJsonPath('data.allowDetails', false)->assertJsonPath('data.allowRenewal', false);

        $this->enable();
        $this->hud('api/v3/hud/config', [], $wearer)->assertOk()->assertJsonPath('data', [
            'allowDiscord' => true, 'allowGroup' => false, 'allowDetails' => true, 'allowRenewal' => true,
            'groupLink' => SlConfig::find(1)->hud_group_link, 'discordLink' => 'https://discord.gg/x',
        ]);

        // renewals would bypass the rental time limit
        SlConfig::whereKey(1)->update(['limit_time' => true]);
        $this->hud('api/v3/hud/config', [], $wearer)->assertJsonPath('data.allowRenewal', false);
    }

    public function test_the_hud_is_not_tracked_as_an_object_or_reseller(): void
    {
        $wearer = $this->avatar('Plain Wearer');
        $this->enable();

        $this->hud('api/v3/hud/config', [], $wearer)->assertOk();

        $this->assertSame(0, SlObject::count());
        $this->assertSame(0, Reseller::where('avatar_id', $wearer->id)->count());
    }

    public function test_the_wearer_sees_their_own_rentals_when_details_are_on(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Hud Wearer'));
        $this->rental(avatar: $this->avatar('Someone Else'));

        $this->hud('api/v3/hud/rentals', [], $rental->avatar)->assertStatus(403)->assertJson(['code' => 'disabled', 'message' => 'Hud details are currently disabled']);
        $this->enable(renewals: false);
        $response = $this->hud('api/v3/hud/rentals', [], $rental->avatar)->assertOk();
        $this->assertSame([$rental->rental_uid], array_column($response->json('data.rentals'), 'rentalUid'));
    }

    public function test_costs_and_details_are_only_for_the_wearers_own_rentals(): void
    {
        $mine = $this->rental(avatar: $this->avatar('Hud Wearer'));
        $theirs = $this->rental(avatar: $this->avatar('Someone Else'));

        $this->enable(renewals: false);
        $this->hud('api/v3/hud/rentals/cost', ['rentalUid' => $mine->rental_uid], $mine->avatar)->assertStatus(403)->assertJson(['message' => 'Hud renewals are currently disabled']);
        $this->enable();
        $this->hud('api/v3/hud/rentals/cost', ['rentalUid' => $mine->rental_uid], $mine->avatar)->assertOk()->assertJsonPath('data.cost', 700);
        $this->hud('api/v3/hud/rentals/cost', ['rentalUid' => $theirs->rental_uid], $mine->avatar)->assertStatus(403);

        $this->hud('api/v3/hud/rentals/send-details', ['rentalUid' => $theirs->rental_uid], $mine->avatar)->assertStatus(403);
        $this->hud('api/v3/hud/rentals/send-details', ['rentalUid' => $mine->rental_uid], $mine->avatar)->assertOk();
        $this->assertSame([$mine->id], DetailRequest::pluck('rental_id')->all());
    }

    public function test_a_hud_renewal_is_paid_to_the_owner_and_tied_to_the_sl_transaction(): void
    {
        $rental = $this->rental(hoursLeft: 24, avatar: $this->avatar('Hud Wearer'));
        $before = $rental->expire_unixtime->getTimestamp();
        $key = (string) Str::uuid();
        $this->enable();

        $this->hud('api/v3/hud/rentals/renew', ['rentalUid' => $rental->rental_uid, 'amountPaid' => 1400, 'transactionKey' => $key], $rental->avatar)
            ->assertOk()->assertJsonPath('data.ownerPayment.required', false)->assertJsonPath('data.inArrears', false);

        $this->assertSame($before + 14 * 86400, $rental->refresh()->expire_unixtime->getTimestamp());
        $sale = Transaction::sole();
        $this->assertTrue((bool) $sale->via_hud);
        $this->assertSame($key, $sale->sl_transaction_uuid);
        $this->assertSame(SlConfig::find(1)->owner_avatar_id, $sale->reseller->avatar_id);
        $this->assertSame('Hud Region', $sale->region->name);
    }

    public function test_hud_renewals_refuse_other_peoples_rentals_the_time_limit_and_bad_input(): void
    {
        $mine = $this->rental(avatar: $this->avatar('Hud Wearer'));
        $theirs = $this->rental(avatar: $this->avatar('Someone Else'));
        $this->enable();
        $renew = fn ($rental, array $extra = []) => $this->hud('api/v3/hud/rentals/renew', $extra + ['rentalUid' => $rental->rental_uid, 'amountPaid' => 700, 'transactionKey' => (string) Str::uuid()], $mine->avatar);

        $renew($theirs)->assertStatus(403)->assertJson(['message' => 'You can not renew other peoples rentals via the hud']);
        $renew($mine, ['transactionKey' => 'nope'])->assertStatus(422)->assertJson(['code' => 'validation']);
        $renew($mine, ['amountPaid' => 5])->assertStatus(422);

        SlConfig::whereKey(1)->update(['limit_time' => true]);
        $renew($mine)->assertStatus(403)->assertJson(['message' => 'Hud renewals are disabled while the rental time limit is enforced']);
        $this->assertSame(0, Transaction::count());
    }

    public function test_the_hud_key_reaches_nothing_else(): void
    {
        $wearer = $this->avatar('Hud Wearer');

        $this->api('api/v3/rentals/cost', ['rentalUid' => 'x'], ApiClass::Hud, ['owner' => $this->identityOf($wearer)])->assertStatus(403);
        $this->hud('api/v3/hud/config', [], $wearer)->assertOk();
        $this->api('api/v3/hud/config', [], ApiClass::Vendor)->assertStatus(403);
    }
}
