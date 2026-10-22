<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\SlConfig;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    /** A staff row as 2.0.x stored it: phash = first 42 chars of sha256(password . id . psalt . ownerLevel). */
    private function legacyStaff(string $password, bool $owner, string $username = 'legacy'): Staff
    {
        $staff = Staff::create([
            'username' => $username, 'avatar_id' => $this->avatar('Legacy Staff')->id, 'owner_level' => $owner,
            'phash' => 'pending-'.$username, 'psalt' => 'oldsalt-'.$username, 'lhash' => 'legacy-'.$username, 'email_reset_expires' => 0,
        ]);
        $staff->forceFill([
            'phash' => substr(hash('sha256', $password.$staff->id.$staff->psalt.($owner ? '1' : '')), 0, 42),
        ])->save();

        return $staff;
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_legacy_password_logs_in_and_is_upgraded_to_bcrypt(): void
    {
        $staff = $this->legacyStaff('old-password', owner: true);

        Volt::test('auth.login')->set('username', 'legacy')->set('password', 'old-password')->call('login')
            ->assertHasNoErrors()->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($staff);
        $staff->refresh();
        $this->assertTrue(Hash::check('old-password', $staff->password));
        $this->assertNull($staff->phash);
    }

    public function test_the_first_login_replaces_install_keys_and_keeps_changed_ones(): void
    {
        $this->legacyStaff('old-password', owner: true);
        SlConfig::whereKey(1)->update(['hud_link_code' => 'my-own-hud-code', 'sl_link_code' => 'install', 'http_inbound_secret' => 'install']);

        Volt::test('auth.login')->set('username', 'legacy')->set('password', 'old-password')->call('login')->assertHasNoErrors();

        $config = SlConfig::find(1);
        $this->assertSame('my-own-hud-code', $config->hud_link_code);
        foreach (['sl_link_code', 'http_inbound_secret', 'public_link_code'] as $key) {
            $this->assertNotSame('install', $config->$key);
            $this->assertSame(16, strlen($config->$key));
        }
        $this->assertTrue(AuditLog::where('store', 'Slconfig')->where('value_name', 'install keys replaced')->exists());

        // later logins leave the new keys alone
        $keys = $config->only(['sl_link_code', 'http_inbound_secret']);
        auth()->logout();
        Volt::test('auth.login')->set('username', 'legacy')->set('password', 'old-password')->call('login')->assertHasNoErrors();
        $this->assertSame($keys, SlConfig::find(1)->only(['sl_link_code', 'http_inbound_secret']));
    }

    public function test_several_staff_can_upgrade_their_legacy_password(): void
    {
        // phash/psalt/lhash keep their 2.0.x unique indexes, so upgraded rows must clear them to NULL
        $first = $this->legacyStaff('first-password', owner: true);
        $second = $this->legacyStaff('second-password', owner: false, username: 'legacy2');

        foreach ([['legacy', 'first-password'], ['legacy2', 'second-password']] as [$username, $password]) {
            Volt::test('auth.login')->set('username', $username)->set('password', $password)->call('login')->assertHasNoErrors();
            auth()->logout();
        }

        $this->assertNotNull($first->refresh()->password);
        $this->assertNotNull($second->refresh()->password);
    }

    public function test_legacy_password_of_non_owner_staff(): void
    {
        $this->legacyStaff('staff-password', owner: false);

        Volt::test('auth.login')->set('username', 'legacy')->set('password', 'staff-password')->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticated();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->legacyStaff('old-password', owner: true);

        Volt::test('auth.login')->set('username', 'legacy')->set('password', 'nope')->call('login')
            ->assertHasErrors('username');

        $this->assertGuest();
    }

    public function test_bcrypt_password_logs_in(): void
    {
        $staff = $this->staff();

        Volt::test('auth.login')->set('username', $staff->username)->set('password', 'secret-password')->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($staff);
    }
}
