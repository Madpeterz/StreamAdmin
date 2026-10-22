<?php

namespace Tests\Feature\System;

use App\Livewire\Avatars;
use App\Livewire\Bans;
use App\Livewire\Health;
use App\Livewire\Staff as StaffPages;
use App\Livewire\SystemSettings;
use App\Livewire\TexturePacks;
use App\Models\AuditLog;
use App\Models\Avatar;
use App\Models\Ban;
use App\Models\Region;
use App\Models\Reseller;
use App\Models\SlConfig;
use App\Models\SlObject;
use App\Models\Staff;
use App\Models\TexturePack;
use App\Models\Transaction;
use App\Services\ObjectHealth;
use App\Support\PaymentKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class SystemTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    private Staff $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = $this->staff();
        $this->actingAs($this->owner);
    }

    public function test_pages_render(): void
    {
        $avatar = $this->avatar('Shown Avatar');
        foreach (['/health', '/avatars', '/avatars/create', '/avatars/'.$avatar->avatar_uid, '/bans', '/texture-packs', '/texture-packs/create', '/audit-log', '/staff', '/system-settings'] as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_avatars_are_added_and_edited(): void
    {
        Livewire::test(Avatars\Show::class)
            ->set('avatar_name', 'New Person')->set('avatar_uuid', '9B8A2C4E-0F1D-4C3B-8A7E-6D5C4B3A2F10')
            ->call('save')->assertHasNoErrors();
        $avatar = Avatar::where('avatar_name', 'New Person')->firstOrFail();
        $this->assertSame('9b8a2c4e-0f1d-4c3b-8a7e-6d5c4b3a2f10', $avatar->avatar_uuid);
        $this->assertSame(8, strlen($avatar->avatar_uid));

        Livewire::test(Avatars\Show::class, ['avatar' => $avatar])->set('credits', 250)->call('save')->assertHasNoErrors();
        $this->assertSame(250, $avatar->refresh()->credits);

        $this->actingAs($this->staff(owner: false));
        Livewire::test(Avatars\Show::class, ['avatar' => $avatar])->set('credits', 9999)->set('avatar_name', 'Renamed Person')->call('save');
        $avatar->refresh();
        $this->assertSame(250, $avatar->credits, 'only owners change credits');
        $this->assertSame('Renamed Person', $avatar->avatar_name);
    }

    public function test_removing_an_avatar_moves_their_payments_to_the_owner(): void
    {
        $avatar = $this->avatar('Leaving Avatar');
        $payment = Transaction::create(['avatar_id' => $avatar->id, 'amount' => 100, 'unixtime' => time(), 'transaction_uid' => Str::random(8)]);
        Ban::create(['avatar_id' => $avatar->id]);

        Livewire::test(Avatars\Show::class, ['avatar' => $avatar])->call('delete')->assertRedirect(route('avatars.index'));

        $this->assertModelMissing($avatar);
        $this->assertSame(SlConfig::find(1)->owner_avatar_id, $payment->refresh()->avatar_id);
    }

    public function test_avatars_in_use_are_kept(): void
    {
        $rental = $this->rental();
        Livewire::test(Avatars\Show::class, ['avatar' => $rental->avatar])->call('delete');
        $this->assertModelExists($rental->avatar);

        $reseller = Reseller::create(['avatar_id' => $this->avatar('Seller')->id, 'allowed' => true, 'rate' => 10]);
        Livewire::test(Avatars\Show::class, ['avatar' => $reseller->avatar])->call('delete');
        $this->assertModelExists($reseller->avatar);
    }

    public function test_bans(): void
    {
        $avatar = $this->avatar('Trouble Maker');
        Livewire::test(Bans\Index::class)->set('find', $avatar->avatar_uid)->call('ban')->assertHasNoErrors();
        $ban = Ban::where('avatar_id', $avatar->id)->firstOrFail();

        Livewire::test(Bans\Index::class)->call('ban', $avatar->id)->assertHasErrors('find');
        Livewire::test(Bans\Index::class)->set('find', 'Nobody Here')->call('ban')->assertHasErrors('find');

        Livewire::test(Bans\Index::class)->call('unban', $ban->id);
        $this->assertModelMissing($ban);
    }

    public function test_staff_can_be_added_and_sign_in(): void
    {
        $avatar = $this->avatar('New Staffer');
        Livewire::test(StaffPages\Index::class)
            ->set('username', 'newstaff')->set('avatar_uid', $avatar->avatar_uid)->set('password', 'a-good-password')
            ->call('save')->assertHasNoErrors();
        $staff = Staff::where('username', 'newstaff')->firstOrFail();
        $this->assertFalse($staff->owner_level);

        Livewire::test(StaffPages\Index::class)
            ->set('username', 'another')->set('avatar_uid', $avatar->avatar_uid)->set('password', 'a-good-password')
            ->call('save')->assertHasErrors('avatar_uid');

        auth()->logout();
        Volt::test('auth.login')->set('username', 'newstaff')->set('password', 'a-good-password')->call('login')->assertHasNoErrors();
        $this->assertAuthenticatedAs($staff);
    }

    public function test_owners_and_yourself_are_not_removed(): void
    {
        $otherOwner = $this->staff();
        $staff = $this->staff(owner: false);

        Livewire::test(StaffPages\Index::class)->call('delete', $otherOwner->id);
        Livewire::test(StaffPages\Index::class)->call('delete', $this->owner->id);
        $this->assertModelExists($otherOwner);
        $this->assertModelExists($this->owner);

        Livewire::test(StaffPages\Index::class)->call('delete', $staff->id);
        $this->assertModelMissing($staff);
    }

    public function test_texture_packs(): void
    {
        $component = Livewire::test(TexturePacks\Edit::class)->set('name', 'Blue pack');
        foreach (array_keys(TexturePacks\Edit::TEXTURES) as $column) {
            $component->set("textures.{$column}", (string) Str::uuid());
        }
        $component->set('textures.offline', 'not-a-uuid')->call('save')->assertHasErrors('textures.offline');
        $component->set('textures.offline', (string) Str::uuid())->call('save')->assertHasNoErrors();

        $this->assertTrue(TexturePack::where('name', 'Blue pack')->exists());
    }

    public function test_settings_follow_the_hud_rules(): void
    {
        $owner = SlConfig::find(1)->ownerAvatar;
        Livewire::test(SystemSettings::class)
            ->set('owner_avatar_uid', $owner->avatar_uid)
            ->set('hud_allow_details', true)->set('hud_allow_renewal', true)->set('limit_time', true)
            ->set('hud_allow_discord', true)->set('hud_discord_link', 'https://discord.gg/abc')
            ->set('new_resellers', true)->set('new_resellers_rate', 25)
            ->call('save')->assertHasNoErrors();

        $config = SlConfig::find(1);
        $this->assertTrue($config->hud_allow_details);
        $this->assertFalse($config->hud_allow_renewal, 'HUD renewals would bypass the rental time limit');
        $this->assertSame('https://discord.gg/abc', $config->hud_discord_link);
        $this->assertSame(25, $config->new_resellers_rate);
        $this->assertTrue(AuditLog::where('store', 'Slconfig')->where('value_name', 'new_resellers_rate')->exists());
    }

    public function test_reissuing_keys(): void
    {
        $keys = ['sl_link_code', 'owner_link_code', 'hud_link_code', 'public_link_code', 'http_inbound_secret'];
        $before = SlConfig::find(1)->only($keys);
        Livewire::test(SystemSettings::class)->call('reissue');
        $after = SlConfig::find(1)->only($keys);

        $this->assertCount(5, array_unique($after));
        foreach ($after as $key => $value) {
            $this->assertNotSame($before[$key], $value);
            $this->assertSame(16, strlen($value));
        }
    }

    public function test_payment_keys_are_tied_to_the_site(): void
    {
        $expires = (string) (time() + 86400 * 30);
        $code = str_repeat('A', 23 - strlen($expires) - 5);
        $key = $code.':'.$expires.'*'.substr(sha1($code.'https://sa.test/'.$expires.'web'), 0, 3);

        $this->assertTrue(PaymentKey::check($key, 'https://sa.test/')['ok']);
        $this->assertSame('Issued for a different site', PaymentKey::check($key, 'https://other.test/')['status']);
        $this->assertSame('No key', PaymentKey::check('install')['status']);
    }

    public function test_display_timezone_is_used(): void
    {
        SlConfig::whereKey(1)->update(['display_timezone_id' => 1]); // America/New_York
        $this->get('/dashboard')->assertOk();
        $this->assertSame('America/New_York', date_default_timezone_get());
        date_default_timezone_set('UTC');
    }

    public function test_display_timezone_is_cached_until_the_settings_change(): void
    {
        $this->get('/dashboard')->assertOk();
        $this->assertTrue(Cache::has(SlConfig::TIMEZONE_CACHE_KEY));

        SlConfig::findOrFail(1)->update(['display_timezone_id' => 1]); // America/New_York
        $this->assertFalse(Cache::has(SlConfig::TIMEZONE_CACHE_KEY));

        $this->get('/dashboard')->assertOk();
        $this->assertSame('America/New_York', date_default_timezone_get());
        date_default_timezone_set('UTC');
    }

    public function test_health_by_region_and_clearing_objects(): void
    {
        $reseller = Reseller::create(['avatar_id' => $this->avatar('Vendor Owner')->id, 'allowed' => true, 'rate' => 10]);
        $region = Region::create(['name' => 'Vendor Land']);
        foreach ([time(), time(), time() - 3600] as $i => $seen) {
            SlObject::create([
                'avatar_id' => $reseller->avatar_id, 'region_id' => $region->id, 'object_uuid' => (string) Str::uuid(),
                'object_name' => "Vendor {$i} S:2.0", 'object_mode' => 'vendor', 'object_xyz' => '<128.4, 30.1, 22>', 'last_seen' => $seen,
            ]);
        }

        $regions = app(ObjectHealth::class)->regions();
        $this->assertSame(['up' => 2, 'down' => 1, 'percent' => 66.7], array_intersect_key($regions->first(), array_flip(['up', 'down', 'percent'])));
        $this->assertSame('https://maps.secondlife.com/secondlife/Vendor%20Land/128/30/22', ObjectHealth::mapUrl(SlObject::with('region')->first()));

        Livewire::test(Health\Index::class)->assertSee('Vendor Land')->call('clearObjects');
        $this->assertSame(0, SlObject::count());
    }

    public function test_changing_your_own_password_keeps_you_signed_in(): void
    {
        $this->owner->forceFill(['password' => 'secret-password'])->save();
        $this->get('/dashboard')->assertOk();
        Volt::test('settings.password')
            ->set('current_password', 'secret-password')->set('password', 'a-brand-new-password')->set('password_confirmation', 'a-brand-new-password')
            ->call('updatePassword')->assertHasNoErrors();
        $this->get('/dashboard')->assertOk();
    }
}
