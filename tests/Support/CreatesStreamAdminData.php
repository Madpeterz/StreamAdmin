<?php

namespace Tests\Support;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Staff;
use App\Models\Stream;
use Illuminate\Support\Str;

/**
 * Builds rentals on top of the default data from the 2.0.x installer (System avatar 1,
 * notices, templates, server types).
 */
trait CreatesStreamAdminData
{
    protected function staff(bool $owner = true): Staff
    {
        return Staff::create([
            'username' => 'staff'.Str::random(4),
            'password' => 'secret-password',
            'avatar_id' => $this->avatar('Staff '.Str::random(6))->id, // staff.avatar_id is unique
            'owner_level' => $owner,
            'phash' => null,
            'psalt' => null,
            'lhash' => null,
            'email_reset_expires' => 0,
        ]);
    }

    protected function avatar(string $name = 'Test Client'): Avatar
    {
        return Avatar::create([
            'avatar_uuid' => (string) Str::uuid(),
            'avatar_name' => $name,
            'avatar_uid' => Str::random(8),
            'credits' => 0,
        ]);
    }

    protected function rental(int $hoursLeft = 168, ?Avatar $avatar = null): Rental
    {
        $server = Server::firstOrCreate(['domain' => 'stream.test'], ['control_panel_url' => 'https://cp.stream.test']);
        $package = Package::firstOrCreate(['package_uid' => 'pkgTest1'], [
            'name' => 'Test package', 'cost' => 700, 'days' => 7, 'listeners' => 10, 'bitrate' => 128,
            'template_id' => 1, 'server_type_id' => 1,
            'texture_soldout' => Str::uuid(), 'texture_instock_small' => Str::uuid(), 'texture_instock_selected' => Str::uuid(),
        ]);
        $stream = Stream::create([
            'server_id' => $server->id, 'package_id' => $package->id, 'port' => random_int(8000, 9999),
            'admin_username' => 'admin', 'admin_password' => 'adminpass', 'dj_password' => 'djpass',
            'stream_uid' => Str::random(8), 'mountpoint' => '/live',
        ]);
        $rental = Rental::create([
            'avatar_id' => ($avatar ?? $this->avatar())->id,
            'stream_id' => $stream->id,
            'package_id' => $package->id,
            'notice_id' => 10,
            'start_unixtime' => time() - 3600,
            'expire_unixtime' => time() + $hoursLeft * 3600,
            'rental_uid' => Str::random(8),
        ]);
        $stream->update(['rental_id' => $rental->id]);

        return $rental;
    }
}
