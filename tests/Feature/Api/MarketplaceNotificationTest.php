<?php

namespace Tests\Feature\Api;

use App\Models\Avatar;
use App\Models\BotConfig;
use App\Models\MarketplaceCoupon;
use App\Models\Message;
use App\Models\SlConfig;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

/** Linden Marketplace Automated Notification at /ans.php. */
class MarketplaceNotificationTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    private const SALT = 'ans-salt-for-tests';

    protected function setUp(): void
    {
        parent::setUp();
        SlConfig::whereKey(1)->update(['enable_coupons' => true, 'ans_salt' => self::SALT]);
        MarketplaceCoupon::create(['listing_id' => 4242, 'cost' => 100, 'credit' => 150, 'claims' => 0, 'last_claim' => 0]);
        BotConfig::whereKey(1)->update(['ims' => false]);
    }

    /** @param array<string, mixed> $override */
    private function purchase(array $override = [], ?string $salt = self::SALT)
    {
        $query = http_build_query($override + [
            'Type' => 'Purchase', 'PaymentGross' => 100, 'PayerKey' => (string) Str::uuid(), 'PayerName' => 'Pat Payer',
            'ReceiverKey' => (string) Str::uuid(), 'ReceiverName' => 'Rita Receiver', 'TransactionID' => '12345678', 'ItemID' => 4242,
        ]);

        return $this->get('/ans.php?'.$query, $salt === null ? [] : ['X-ANS-Verify-Hash' => sha1($query.$salt)]);
    }

    public function test_a_purchase_credits_the_receiver_and_thanks_the_payer(): void
    {
        $receiverKey = (string) Str::uuid();

        $this->purchase(['ReceiverKey' => $receiverKey, 'ReceiverName' => 'Rita'])->assertOk()->assertExactJson(['ok' => true, 'message' => 'Credit added']);

        $receiver = Avatar::where('avatar_uuid', $receiverKey)->sole();
        $this->assertSame('Rita Resident', $receiver->avatar_name, 'single word names get Resident');
        $this->assertSame(150, (int) $receiver->credits);
        $sale = Transaction::sole();
        $this->assertTrue((bool) $sale->via_marketplace);
        $this->assertSame($receiver->id, $sale->target_avatar_id);
        $this->assertSame('12345678', $sale->sl_transaction_uuid);
        $this->assertSame(1, (int) MarketplaceCoupon::sole()->claims);
        $this->assertSame(2, Message::count(), 'one message each for the receiver and the payer');
    }

    public function test_buying_for_yourself_sends_one_message(): void
    {
        $key = (string) Str::uuid();

        $this->purchase(['PayerKey' => $key, 'ReceiverKey' => $key, 'PayerName' => 'Self Buyer', 'ReceiverName' => 'Self Buyer'])->assertJson(['ok' => true]);

        $this->assertNull(Transaction::sole()->target_avatar_id);
        $this->assertSame(1, Message::count());
    }

    public function test_the_same_transaction_is_only_paid_once(): void
    {
        $receiver = ['ReceiverKey' => (string) Str::uuid()];
        $this->purchase($receiver)->assertJson(['ok' => true]);

        $this->purchase($receiver)->assertOk()->assertExactJson(['ok' => false, 'message' => 'This transaction was already processed']);

        $this->assertSame(150, (int) Avatar::where('avatar_uuid', $receiver['ReceiverKey'])->sole()->credits);
        $this->assertSame(1, Transaction::count());
        $this->assertSame(1, (int) MarketplaceCoupon::sole()->claims, 'the refused delivery is not counted either');
    }

    public function test_the_hash_must_match_the_query_and_the_salt(): void
    {
        $this->purchase(salt: null)->assertOk()->assertJson(['ok' => false, 'message' => 'Unable to validate']);
        $this->purchase(salt: 'wrong-salt')->assertOk()->assertJson(['ok' => false, 'message' => 'Unable to validate']);

        $this->assertSame(0, Transaction::count());
    }

    public function test_it_is_off_until_coupons_are_enabled(): void
    {
        SlConfig::whereKey(1)->update(['enable_coupons' => false]);

        $this->purchase()->assertOk()->assertExactJson(['ok' => false, 'message' => 'ANS is disabled']);
    }

    public function test_bad_deliveries_are_refused_without_a_credit(): void
    {
        $this->purchase(['Type' => 'Refund'])->assertJson(['ok' => false]);
        $this->purchase(['PayerKey' => 'not-a-uuid'])->assertJson(['ok' => false]);
        $this->purchase(['ItemID' => 999])->assertJson(['ok' => false, 'message' => 'Not a tracked listing']);
        $this->purchase(['PaymentGross' => 50])->assertJson(['ok' => false, 'message' => 'The amount paid does not match the listing']);

        $this->assertSame(0, Transaction::count());
        $this->assertSame(0, (int) MarketplaceCoupon::sole()->claims);
    }
}
