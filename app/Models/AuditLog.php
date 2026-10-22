<?php

namespace App\Models;

use App\Casts\UnixTimestamp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Change history written by staff actions.
 */
class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'unixtime' => UnixTimestamp::class,
        ];
    }

    /** @return BelongsTo<Avatar, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
