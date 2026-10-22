<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dev stack only: the 2.0.x installer leaves the admin staff row without a password (its setup
 * screen used to set one), so give it the documented local test login admin / localtest.
 */
class DevLoginSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }
        $admin = Staff::where('username', 'admin')->first();
        if ($admin === null || $admin->password !== null) {
            return;
        }
        $admin->forceFill([
            'password' => Hash::make('localtest'),
            'phash' => null,
            'psalt' => null,
            'lhash' => null,
        ])->save();
    }
}
