<?php

namespace App\Console\Commands;

use App\Models\Staff;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Set a staff member's password from the server. Needed once on a fresh install: the 2.0.x
 * installer creates the admin account without a password.
 *
 *   docker compose exec app php artisan streamadmin:staff-password admin
 */
class StaffPassword extends Command
{
    protected $signature = 'streamadmin:staff-password {username : Staff username, e.g. admin}';

    protected $description = 'Set a staff member\'s password (first run, or when no one can sign in)';

    public function handle(AuditLogger $audit): int
    {
        $staff = Staff::where('username', $this->argument('username'))->first();
        if ($staff === null) {
            $this->error('No staff member has that username. Staff: '.Staff::orderBy('username')->pluck('username')->join(', '));

            return self::FAILURE;
        }

        $password = (string) $this->secret('New password for '.$staff->username);
        $validator = Validator::make(
            ['password' => $password, 'password_confirmation' => (string) $this->secret('Repeat it')],
            ['password' => ['required', 'confirmed', Password::defaults()]],
        );
        if ($validator->fails()) {
            $this->error($validator->errors()->first('password'));

            return self::FAILURE;
        }

        $staff->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();
        $audit->log('Staff', (string) $staff->id, 'Password changed', null, $staff->username.' (console)');
        $this->info('Password set for '.$staff->username.'.');

        return self::SUCCESS;
    }
}
