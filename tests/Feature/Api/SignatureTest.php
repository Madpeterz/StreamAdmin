<?php

namespace Tests\Feature\Api;

use App\Api\ApiClass;
use App\Api\Signature;
use App\Models\Avatar;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Support\SystemApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\CreatesStreamAdminData;
use Tests\Support\SignsApiRequests;
use Tests\TestCase;

/** Who may call /api/v3: signing, replay, key classes and who the caller is. */
class SignatureTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;
    use SignsApiRequests;

    public function test_a_signed_call_works_and_records_the_object(): void
    {
        $object = $this->callingObject('Nova\'s Vendor');

        $this->api('api/v3/objects/ping', body: ['object' => $object])
            ->assertOk()
            ->assertExactJson(['ok' => true, 'message' => 'pong', 'data' => []]);

        $stored = SlObject::where('object_uuid', $object['key'])->sole();
        $this->assertSame("Nova's Vendor", $stored->object_name, 'escaped text is stored as plain text');
        $this->assertSame('Test Region', Region::sole()->name);
    }

    public function test_unsigned_and_badly_signed_calls_are_refused(): void
    {
        $this->useApiKeys();
        $this->call('POST', '/api/v3/objects/ping', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{}')
            ->assertStatus(401)->assertJson(['ok' => false, 'code' => 'unauthorized']);

        $this->api('api/v3/objects/ping', secret: 'not-the-vendor-secret')->assertStatus(401)->assertJson(['message' => 'Invalid signature']);
        $this->api('api/v3/objects/ping', headers: ['X-SA-Class' => 'nonsense'])->assertStatus(401);
        $this->api('api/v3/objects/ping', headers: ['X-SA-Nonce' => 'short'])->assertStatus(401);
        // the body is part of the signature
        $this->api('api/v3/objects/ping', raw: '{"object":{}}', secret: 'x')->assertStatus(401);
    }

    public function test_a_stale_timestamp_is_refused(): void
    {
        $config = $this->useApiKeys();
        $old = (string) (time() - 400);
        $signature = Signature::sign($config->sl_link_code, $old, 'nonce0123456789abcdef', '/api/v3/objects/ping', '{}');

        $this->api('api/v3/objects/ping', headers: ['X-SA-Timestamp' => $old, 'X-SA-Nonce' => 'nonce0123456789abcdef', 'X-SA-Signature' => $signature], raw: '{}')
            ->assertStatus(401)->assertJson(['message' => 'The timestamp is outside the allowed window']);
    }

    public function test_a_nonce_works_once(): void
    {
        $config = $this->useApiKeys();
        $body = json_encode(['object' => $this->callingObject(), 'owner' => $this->reseller(), 'data' => new \stdClass]);
        $now = (string) time();
        $nonce = Str::random(24);
        $headers = [
            'X-SA-Class' => 'vendor', 'X-SA-Timestamp' => $now, 'X-SA-Nonce' => $nonce,
            'X-SA-Signature' => Signature::sign($config->sl_link_code, $now, $nonce, '/api/v3/objects/ping', $body),
        ];

        $this->api('api/v3/objects/ping', headers: $headers, raw: $body)->assertOk();
        $this->api('api/v3/objects/ping', headers: $headers, raw: $body)->assertStatus(409)->assertJson(['code' => 'replay']);
        // the same payload with a new nonce is a new call
        $this->api('api/v3/objects/ping', raw: $body)->assertOk();
    }

    public function test_secrets_shorter_than_16_characters_never_sign(): void
    {
        $this->useApiKeys();
        SlConfig::whereKey(1)->update(['sl_link_code' => 'install']);

        $this->api('api/v3/objects/ping', secret: 'install')->assertStatus(401);
    }

    public function test_key_classes_only_reach_their_endpoints(): void
    {
        $this->api('api/v3/owner/mail/next', class: ApiClass::Vendor)->assertStatus(403)->assertJson(['code' => 'forbidden']);
        $this->api('api/v3/hud/config', class: ApiClass::Vendor)->assertStatus(403);
        $this->api('api/v3/objects/ping', class: ApiClass::Hud)->assertStatus(403);
        $this->api('api/v3/objects/ping', class: ApiClass::Bot)->assertStatus(403);
        $this->api('api/v3/owner/mail/next', class: ApiClass::Owner)->assertOk();
        $this->api('api/v3/objects/ping', class: ApiClass::Owner)->assertOk();
    }

    public function test_the_owner_key_is_the_owner_and_vendors_cannot_name_the_owner(): void
    {
        $owner = Avatar::findOrFail(SlConfig::findOrFail(1)->owner_avatar_id);

        // a reseller naming the owner avatar is refused, whatever it signs with
        $this->api('api/v3/objects/ping', body: ['owner' => $this->identityOf($owner)])
            ->assertStatus(403)->assertJson(['message' => 'The system owner must sign with the owner key']);

        // the owner key needs no owner claim and becomes the owner's reseller entry
        $this->api('api/v3/objects/ping', class: ApiClass::Owner)->assertOk();
        $this->assertTrue(Reseller::where('avatar_id', $owner->id)->sole()->allowed);
        $this->assertSame($owner->id, SlObject::sole()->avatar_id);
    }

    public function test_new_resellers_follow_the_system_settings(): void
    {
        $stranger = ['key' => (string) Str::uuid(), 'name' => rawurlencode('Stranger Resident')];
        SlConfig::whereKey(1)->update(['new_resellers' => false]);

        $this->api('api/v3/objects/ping', body: ['owner' => $stranger])->assertStatus(403)->assertJson(['code' => 'forbidden']);
        $this->assertSame(0, SlObject::count(), 'a refused call leaves nothing behind');
        $this->assertNull(Avatar::where('avatar_uuid', $stranger['key'])->first());

        SlConfig::whereKey(1)->update(['new_resellers' => true, 'new_resellers_rate' => 15]);
        $this->api('api/v3/objects/ping', body: ['owner' => $stranger])->assertOk();
        $this->assertSame(15, Reseller::whereHas('avatar', fn ($q) => $q->where('avatar_uuid', $stranger['key']))->sole()->rate);
    }

    public function test_the_caller_must_say_who_and_where(): void
    {
        $this->api('api/v3/objects/ping', body: ['owner' => ['key' => 'not-a-uuid', 'name' => 'x']])
            ->assertStatus(422)->assertJson(['ok' => false, 'code' => 'validation'])->assertJsonStructure(['errors' => ['owner.key']]);
        $this->api('api/v3/objects/ping', body: ['object' => ['key' => (string) Str::uuid()]])->assertStatus(422);
        $this->api('api/v3/objects/ping', headers: ['X-SecondLife-Shard' => 'Beta'])->assertStatus(422)->assertJson(['message' => 'Wrong grid connected: Beta']);
    }

    public function test_a_body_that_is_not_a_json_object_is_refused_after_the_signature(): void
    {
        $config = $this->useApiKeys();
        $now = (string) time();
        $nonce = Str::random(24);
        $signature = Signature::sign($config->sl_link_code, $now, $nonce, '/api/v3/objects/ping', 'nope');

        $this->api('api/v3/objects/ping', headers: ['X-SA-Timestamp' => $now, 'X-SA-Nonce' => $nonce, 'X-SA-Signature' => $signature], raw: 'nope')
            ->assertStatus(400)->assertJson(['code' => 'invalid_request']);
    }

    public function test_unknown_endpoints_answer_json(): void
    {
        $this->api('api/v3/nothing/here')->assertStatus(404)->assertExactJson(['ok' => false, 'code' => 'not_found', 'message' => 'Unknown endpoint']);
    }

    public function test_the_bot_may_sign_with_a_short_lived_key_for_one_path(): void
    {
        $secret = $this->useApiKeys()->http_inbound_secret;
        $path = '/api/v3/bot/notecards/next';
        $key = SystemApiKey::current($secret, $path);

        $this->api($path, class: ApiClass::Bot, secret: $key)->assertOk()->assertJson(['message' => 'nowork']);
        $this->api($path, class: ApiClass::Bot, secret: $secret)->assertOk();
        $this->api($path, class: ApiClass::Bot, secret: SystemApiKey::current($secret, '/api/v3/bot/other'))->assertStatus(401);
        $this->api($path, class: ApiClass::Bot, secret: SystemApiKey::forWindow($secret, $path, intdiv(time(), SystemApiKey::WINDOW) - 2))->assertStatus(401);
    }
}
