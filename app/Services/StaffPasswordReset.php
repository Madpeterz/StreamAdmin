<?php

namespace App\Services;

use App\Models\Avatar;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Staff password reset by Second Life name (port of the 2.0.x Login/Reset and Login/Resetnow
 * controls): the reset link is sent to the staff member's avatar as an in-world IM and works for
 * an hour. The code lives in staff.email_reset_code (8 characters, as in 2.0.x).
 */
class StaffPasswordReset
{
    public const LIFETIME = 3600;

    public function __construct(private readonly BotService $bot, private readonly AuditLogger $audit) {}

    /** "Madpeter" means "Madpeter Resident", as in Second Life. */
    public static function avatarName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return str_contains($name, ' ') ? $name : $name.' Resident';
    }

    public function findStaff(string $slName): ?Staff
    {
        $avatar = Avatar::where('avatar_name', self::avatarName($slName))->first();

        return $avatar ? Staff::with('avatar')->where('avatar_id', $avatar->id)->first() : null;
    }

    /**
     * Send a reset link if the name belongs to a staff member. Says nothing either way, so the
     * form cannot be used to find out who is staff.
     */
    public function request(string $slName): void
    {
        $staff = $this->findStaff($slName);
        if ($staff === null) {
            return;
        }
        $code = Str::random(8);
        DB::transaction(function () use ($staff, $code) {
            $staff->forceFill(['email_reset_code' => $code, 'email_reset_expires' => time() + self::LIFETIME])->save();
            $this->bot->sendMessage($staff->avatar, 'Web panel reset link: '.route('password.reset', $code).' expires in 1 hour');
            $this->audit->log('Staff', (string) $staff->id, 'reset code requested', 'av:'.$staff->avatar->avatar_name, 'User:'.$staff->username);
        });
    }

    /** Set a new password if the name and code match an unexpired reset. */
    public function reset(string $slName, string $code, string $password): bool
    {
        $staff = $this->findStaff($slName);
        if ($staff === null || $staff->email_reset_code === null
            || ! hash_equals($staff->email_reset_code, $code)
            || $staff->email_reset_expires === null || $staff->email_reset_expires->getTimestamp() <= time()) {
            return false;
        }
        DB::transaction(function () use ($staff, $password) {
            $staff->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
                'email_reset_code' => null,
                'email_reset_expires' => time() - 1,
            ])->save();
            $this->audit->log('Staff', (string) $staff->id, 'new password applied', 'av:'.$staff->avatar->avatar_name, 'User:'.$staff->username);
        });

        return true;
    }
}
