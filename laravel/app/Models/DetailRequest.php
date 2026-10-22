<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pending request to send stream details to a client.
 */
class DetailRequest extends Model
{
    protected $table = 'detail_requests';

    public $timestamps = false;

    protected $guarded = ['id'];

    /** @return BelongsTo<Rental, $this> */
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }
}
