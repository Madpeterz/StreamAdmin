<?php

namespace Tests\Feature\LegacyApi;

use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Support\SystemApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

/**
 * Smoke tests for the in-world entry points. The full contract (227 recorded steps) is checked
 * by contract-tests/ against a running stack.
 */
class GatewayTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    private function signedPost(string $path, string $ownerKey, string $ownerName, array $extra = [], ?string $linkCode = null): TestResponse
    {
        [$module, $area] = explode('/', $path);
        $fields = [
            'version' => '2.0.1.0', 'mode' => 'test', 'objectuuid' => (string) Str::uuid(), 'regionname' => 'Test Region',
            'ownerkey' => $ownerKey, 'ownername' => $ownerName, 'pos' => '128,128,25', 'objectname' => 'Vendor', 'objecttype' => 'vendor',
        ];
        $unixtime = time();
        $linkCode ??= SlConfig::find(1)->sl_link_code;
        $fields['unixtime'] = $unixtime;
        $fields['hash'] = sha1($unixtime.ucfirst($module).ucfirst($area).implode('', array_slice($fields, 0, 9)).$linkCode);

        return $this->post('/api.php/'.$path, $fields + $extra, ['X-SecondLife-Shard' => 'Production']);
    }

    private function owner(): array
    {
        $owner = SlConfig::find(1)->ownerAvatar;

        return [$owner->avatar_uuid, $owner->avatar_name];
    }

    public function test_owner_ping_is_recorded(): void
    {
        $this->signedPost('object/ping', ...$this->owner())
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertExactJson(['status' => '1', 'message' => 'pong', 'module' => 'Object', 'area' => 'Ping']);
        $this->assertSame(1, SlObject::where('object_mode', 'vendor')->count());
    }

    public function test_bad_hash_and_unknown_endpoint(): void
    {
        $this->signedPost('Object/Ping', ...[...$this->owner(), [], 'wrong-code'])
            ->assertExactJson(['status' => '0', 'message' => 'Unable to vaildate request to API endpoint']);
        $this->post('/api.php/Nope/Nothing')->assertStatus(501)->assertExactJson(['status' => false, 'message' => '[Nope | Nothing | ] Unsupported']);
        $this->post('/api.php')->assertStatus(501);
    }

    public function test_a_refused_request_leaves_nothing_behind(): void
    {
        SlConfig::whereKey(1)->update(['new_resellers' => false]);
        $stranger = (string) Str::uuid();

        $this->signedPost('Object/Ping', $stranger, 'Stranger Resident')
            ->assertExactJson(['status' => '0', 'message' => 'Unable to access this api - please contact owner']);

        $this->assertFalse(Reseller::whereHas('avatar', fn ($q) => $q->where('avatar_uuid', $stranger))->exists());
        $this->assertSame(0, SlObject::count());
    }

    public function test_sys_php_accepts_the_secret_or_a_scoped_key(): void
    {
        SlConfig::whereKey(1)->update(['http_inbound_secret' => 'sys-secret']);
        $sign = fn (string $code) => ['unixtime' => time(), 'method' => 'Notecard', 'action' => 'Next', 'token' => sha1(time().'NotecardNext'.$code)];

        $this->post('/sys.php/Notecard/Next', $sign('sys-secret'))->assertExactJson(['status' => true, 'message' => 'nowork', 'module' => 'Notecard', 'area' => 'Next']);
        $key = SystemApiKey::current('sys-secret', 'Notecard', 'Next');
        $this->post('/sys.php/Notecard/Next', $sign($key))->assertJson(['status' => true]);
        $this->post('/sys.php/Notecard/Next', $sign('nope'))->assertExactJson(['status' => false, 'message' => 'Invaild token']);
    }

    public function test_ans_is_off_until_coupons_are_enabled(): void
    {
        $this->get('/ans.php?Type=Purchase')->assertOk()->assertExactJson(['status' => false, 'message' => 'Failed ANS disabled', 'module' => 'Ans', 'area' => 'Event']);
    }
}
