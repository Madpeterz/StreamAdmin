<?php

namespace Tests\Feature\Auth;

use App\Models\AuditLog;
use App\Models\SlConfig;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_the_password_logs_in(): void
    {
        $staff = $this->staff();

        Volt::test('auth.login')->set('username', $staff->username)->set('password', 'secret-password')->call('login')
            ->assertHasNoErrors()->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($staff);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $staff = $this->staff();

        Volt::test('auth.login')->set('username', $staff->username)->set('password', 'nope')->call('login')
            ->assertHasErrors('username');

        $this->assertGuest();
    }

    public function test_staff_without_a_password_cannot_sign_in(): void
    {
        // the 2.0.x installer's admin row, or staff who never signed in before 3.0 dropped the old hashes
        $staff = Staff::create(['username' => 'nopassword', 'avatar_id' => $this->avatar('No Password')->id, 'owner_level' => true, 'email_reset_expires' => 0]);

        Volt::test('auth.login')->set('username', 'nopassword')->set('password', '')->call('login')->assertHasErrors('password');
        Volt::test('auth.login')->set('username', 'nopassword')->set('password', 'anything')->call('login')->assertHasErrors('username');

        $this->assertGuest();
        $this->assertNull($staff->refresh()->password);
    }

    public function test_the_first_login_replaces_weak_keys_and_keeps_strong_ones(): void
    {
        $staff = $this->staff();
        $strong = 'my-own-hud-code-0123456789';
        SlConfig::whereKey(1)->update(['hud_link_code' => $strong, 'sl_link_code' => 'install', 'http_inbound_secret' => 'short8ch']);

        Volt::test('auth.login')->set('username', $staff->username)->set('password', 'secret-password')->call('login')->assertHasNoErrors();

        $config = SlConfig::find(1);
        $this->assertSame($strong, $config->hud_link_code);
        foreach (['sl_link_code', 'http_inbound_secret', 'public_link_code', 'owner_link_code'] as $key) {
            $this->assertNotSame('install', $config->$key);
            $this->assertSame(16, strlen($config->$key), $key);
        }
        $this->assertTrue(AuditLog::where('store', 'Slconfig')->where('value_name', 'weak keys replaced')->exists());

        // later logins leave the new keys alone
        $keys = $config->only(['sl_link_code', 'http_inbound_secret']);
        auth()->logout();
        Volt::test('auth.login')->set('username', $staff->username)->set('password', 'secret-password')->call('login')->assertHasNoErrors();
        $this->assertSame($keys, SlConfig::find(1)->only(['sl_link_code', 'http_inbound_secret']));
    }
}
