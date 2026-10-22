<?php

namespace App\Models;

use App\Casts\HtmlEncoded;
use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * In-world object (vendor, server object, HUD) that has called the API.
 */
class SlObject extends Model
{
    protected $table = 'sl_objects';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'object_name' => HtmlEncoded::class,
            'last_seen' => UnixTimestamp::class,
        ];
    }

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    /** @return BelongsTo<Region, $this> */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
