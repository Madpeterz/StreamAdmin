<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

/**
 * Staff login. `password` is bcrypt; `phash`/`psalt` hold the 2.0.x hash until the first login.
 */
class Staff extends Authenticatable
{
    protected $table = 'staff';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token', 'phash', 'psalt', 'lhash'];

    protected function casts(): array
    {
        return [
            'owner_level' => 'boolean',
            'email_reset_expires' => UnixTimestamp::class,
            'password' => 'hashed',
        ];
    }

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    public function initials(): string
    {
        return Str::of($this->username)->substr(0, 2)->upper()->toString();
    }
}
