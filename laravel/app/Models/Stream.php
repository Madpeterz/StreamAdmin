<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stream (port/mount) on a server, sold through a package.
 */
class Stream extends Model
{
    protected $table = 'streams';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'need_work' => 'boolean',
        ];
    }

    /** @return BelongsTo<Server, $this> */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /** @return BelongsTo<Rental, $this> */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }
}
