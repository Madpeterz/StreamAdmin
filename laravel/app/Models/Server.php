<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Streaming server hosting streams.
 */
class Server extends Model
{
    protected $table = 'servers';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** Bandwidth in kbps. "gpbs" is a typo stored by 2.0.x forms and means gbps. */
    public function bandwidthKbps(): int
    {
        return match ($this->bandwidth_type) {
            'kbps' => $this->bandwidth,
            'gbps', 'gpbs' => $this->bandwidth * 1_000_000,
            default => $this->bandwidth * 1000,
        };
    }

    /** Storage in MB. */
    public function storageMb(): int
    {
        return match ($this->total_storage_type) {
            'mb' => $this->total_storage,
            'tb' => $this->total_storage * 1_000_000,
            default => $this->total_storage * 1000,
        };
    }

    /** @return HasMany<Stream, $this> */
    public function streams(): HasMany
    {
        return $this->hasMany(Stream::class, 'server_id');
    }
}
