<?php

namespace Tests\Feature\Auth;

use App\Models\Message;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    private function staffNamed(string $avatarName): Staff
    {
        $staff = $this->staff(owner: false);
        $staff->avatar->update(['avatar_name' => $avatarName]);

        return $staff->refresh();
    }

    public function test_pages_render_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('Forgot password?');
        $this->get('/forgot-password')->assertOk();
        $this->get('/reset-password/abcd1234')->assertOk();
    }

    public function test_reset_link_is_sent_to_the_avatar_and_works_once(): void
    {
        $staff = $this->staffNamed('Helper Resident');

        Volt::test('auth.forgot-password')->set('sl_name', 'helper')->call('sendResetLink')->assertSet('sent', true);

        $staff->refresh();
        $code = $staff->email_reset_code;
        $this->assertSame(8, strlen($code));
        $message = Message::where('avatar_id', $staff->avatar_id)->firstOrFail();
        $this->assertStringContainsString(route('password.reset', $code), $message->message);

        Volt::test('auth.reset-password', ['token' => $code])
            ->set('sl_name', 'Helper')->set('password', 'my-new-password')->set('password_confirmation', 'my-new-password')
            ->call('resetPassword')->assertHasNoErrors()->assertRedirect(route('login'));

        $staff->refresh();
        $this->assertTrue(Hash::check('my-new-password', $staff->password));
        $this->assertNull($staff->email_reset_code);

        Volt::test('auth.reset-password', ['token' => $code])
            ->set('sl_name', 'Helper')->set('password', 'another-password')->set('password_confirmation', 'another-password')
            ->call('resetPassword')->assertHasErrors('sl_name');
    }

    public function test_unknown_names_get_the_same_answer_and_no_message(): void
    {
        Volt::test('auth.forgot-password')->set('sl_name', 'Nobody Here')->call('sendResetLink')->assertSet('sent', true);
        $this->assertSame(0, Message::count());
    }

    public function test_wrong_name_or_expired_code_is_refused(): void
    {
        $staff = $this->staffNamed('Helper Resident');
        $staff->forceFill(['email_reset_code' => 'Code1234', 'email_reset_expires' => time() + 600])->save();
        $other = $this->staffNamed('Other Person');

        Volt::test('auth.reset-password', ['token' => 'Code1234'])
            ->set('sl_name', 'Other Person')->set('password', 'my-new-password')->set('password_confirmation', 'my-new-password')
            ->call('resetPassword')->assertHasErrors('sl_name');

        $staff->forceFill(['email_reset_expires' => time() - 1])->save();
        Volt::test('auth.reset-password', ['token' => 'Code1234'])
            ->set('sl_name', 'Helper')->set('password', 'my-new-password')->set('password_confirmation', 'my-new-password')
            ->call('resetPassword')->assertHasErrors('sl_name');

        $this->assertTrue(Hash::check('secret-password', $staff->refresh()->password));
        $this->assertTrue(Hash::check('secret-password', $other->refresh()->password));
    }

    public function test_console_command_sets_a_password(): void
    {
        $staff = $this->staff();
        $this->artisan('streamadmin:staff-password', ['username' => $staff->username])
            ->expectsQuestion('New password for '.$staff->username, 'console-password')
            ->expectsQuestion('Repeat it', 'console-password')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('console-password', $staff->refresh()->password));
    }
}
